<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$pathParts = explode('/', trim($path, '/'));

try {
    $user = getCurrentUser();
    $db = getDb();
    
    if ($method === 'GET' && $path === '/') {
        // GET ALL DOCUMENTS
        $category_id = $_GET['category_id'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $sql = "SELECT * FROM documents WHERE 1=1";
        $params = [];
        
        if ($category_id) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (code LIKE ? OR title LIKE ? OR assignee LIKE ? OR summary LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $documents = $stmt->fetchAll();
        
        sendJSON($documents);
        
    } elseif ($method === 'GET' && $path === '/stats') {
        // GET DOCUMENT STATS
        $stmt = $db->query("SELECT * FROM document_stats");
        $stats = $stmt->fetch();
        sendJSON($stats);
        
    } elseif ($method === 'GET' && $path === '/export') {
        // EXPORT TO EXCEL
        require_once 'vendor/autoload.php'; // PHPSpreadsheet
        
        $stmt = $db->query("
            SELECT d.*, c.name as category_name 
            FROM documents d 
            LEFT JOIN categories c ON d.category_id = c.id 
            ORDER BY d.created_at DESC
        ");
        $documents = $stmt->fetchAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'Mã Văn Bản');
        $sheet->setCellValue('B1', 'Tên Văn Bản');
        $sheet->setCellValue('C1', 'Danh Mục');
        $sheet->setCellValue('D1', 'Cán Bộ Cập Nhật');
        $sheet->setCellValue('E1', 'Ngày Hết Hạn');
        $sheet->setCellValue('F1', 'Trạng Thái');
        $sheet->setCellValue('G1', 'Tóm Tắt');
        
        // Data
        $row = 2;
        foreach ($documents as $doc) {
            $sheet->setCellValue('A' . $row, $doc['code']);
            $sheet->setCellValue('B' . $row, $doc['title']);
            $sheet->setCellValue('C' . $row, $doc['category_name']);
            $sheet->setCellValue('D' . $row, $doc['assignee']);
            $sheet->setCellValue('E' . $row, $doc['expiry_date']);
            $sheet->setCellValue('F' . $row, $doc['status']);
            $sheet->setCellValue('G' . $row, $doc['summary']);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="van-ban.xlsx"');
        $writer->save('php://output');
        exit();
        
    } elseif ($method === 'POST' && $path === '/') {
        // CREATE DOCUMENT
        $input = getJSONInput();
        
        if (!isset($input['code'], $input['title'], $input['category_id'], $input['assignee'], $input['expiry_date'], $input['summary'])) {
            sendError('Missing required fields', 400);
        }
        
        // Check if code exists
        $stmt = $db->prepare("SELECT id FROM documents WHERE code = ?");
        $stmt->execute([$input['code']]);
        if ($stmt->fetch()) {
            sendError('Mã văn bản đã tồn tại', 400);
        }
        
        $id = generateUUID();
        $status = calculateDocumentStatus($input['expiry_date']);
        
        $stmt = $db->prepare("
            INSERT INTO documents (id, code, title, category_id, assignee, expiry_date, summary, status, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $input['code'],
            $input['title'],
            $input['category_id'],
            $input['assignee'],
            $input['expiry_date'],
            $input['summary'],
            $status,
            $user['username']
        ]);
        
        $document = [
            'id' => $id,
            'code' => $input['code'],
            'title' => $input['title'],
            'category_id' => $input['category_id'],
            'assignee' => $input['assignee'],
            'expiry_date' => $input['expiry_date'],
            'summary' => $input['summary'],
            'status' => $status,
            'created_by' => $user['username'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        sendJSON($document, 201);
        
    } elseif ($method === 'PUT' && isset($pathParts[0])) {
        // UPDATE DOCUMENT
        $id = $pathParts[0];
        $input = getJSONInput();
        
        $updates = [];
        $params = [];
        
        if (isset($input['code'])) {
            $updates[] = "code = ?";
            $params[] = $input['code'];
        }
        if (isset($input['title'])) {
            $updates[] = "title = ?";
            $params[] = $input['title'];
        }
        if (isset($input['category_id'])) {
            $updates[] = "category_id = ?";
            $params[] = $input['category_id'];
        }
        if (isset($input['assignee'])) {
            $updates[] = "assignee = ?";
            $params[] = $input['assignee'];
        }
        if (isset($input['expiry_date'])) {
            $updates[] = "expiry_date = ?";
            $params[] = $input['expiry_date'];
            $updates[] = "status = ?";
            $params[] = calculateDocumentStatus($input['expiry_date']);
        }
        if (isset($input['summary'])) {
            $updates[] = "summary = ?";
            $params[] = $input['summary'];
        }
        
        if (empty($updates)) {
            sendError('No fields to update', 400);
        }
        
        $params[] = $id;
        $sql = "UPDATE documents SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $document = $stmt->fetch();
        
        if (!$document) {
            sendError('Document not found', 404);
        }
        
        sendJSON($document);
        
    } elseif ($method === 'DELETE' && isset($pathParts[0])) {
        // DELETE DOCUMENT
        $id = $pathParts[0];
        
        // Delete associated files first
        $stmt = $db->prepare("SELECT * FROM files WHERE document_id = ?");
        $stmt->execute([$id]);
        $files = $stmt->fetchAll();
        
        // TODO: Delete from Google Drive if configured
        
        $stmt = $db->prepare("DELETE FROM files WHERE document_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Document not found', 404);
        }
        
        sendJSON(['message' => 'Document deleted successfully']);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Documents Error: ' . $e->getMessage());
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

?>
