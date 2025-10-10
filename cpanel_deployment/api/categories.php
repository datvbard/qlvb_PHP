<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$pathParts = explode('/', trim($path, '/'));

try {
    $user = getCurrentUser();
    $db = getDb();
    
    if ($method === 'GET' && $path === '/') {
        // GET ALL CATEGORIES
        $stmt = $db->query("SELECT * FROM categories ORDER BY `order` ASC");
        $categories = $stmt->fetchAll();
        sendJSON($categories);
        
    } elseif ($method === 'GET' && isset($pathParts[0]) && $pathParts[0] === 'type' && isset($pathParts[1])) {
        // GET CATEGORIES BY TYPE
        $type = $pathParts[1];
        $stmt = $db->prepare("SELECT * FROM categories WHERE type = ? ORDER BY `order` ASC");
        $stmt->execute([$type]);
        $categories = $stmt->fetchAll();
        sendJSON($categories);
        
    } elseif ($method === 'POST' && $path === '/') {
        // CREATE CATEGORY (Admin only)
        requireAdmin();
        
        $input = getJSONInput();
        if (!isset($input['name'], $input['type'])) {
            sendError('Missing required fields', 400);
        }
        
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? AND type = ?");
        $stmt->execute([$input['name'], $input['type']]);
        if ($stmt->fetch()) {
            sendError('Danh mục này đã tồn tại', 400);
        }
        
        $id = generateUUID();
        $stmt = $db->prepare("
            INSERT INTO categories (id, name, type, icon, `order`) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $input['name'],
            $input['type'],
            $input['icon'] ?? '📄',
            $input['order'] ?? 0
        ]);
        
        $category = [
            'id' => $id,
            'name' => $input['name'],
            'type' => $input['type'],
            'icon' => $input['icon'] ?? '📄',
            'order' => $input['order'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        sendJSON($category, 201);
        
    } elseif ($method === 'PUT' && isset($pathParts[0])) {
        // UPDATE CATEGORY (Admin only)
        requireAdmin();
        
        $id = $pathParts[0];
        $input = getJSONInput();
        
        $updates = [];
        $params = [];
        
        if (isset($input['name'])) {
            $updates[] = "name = ?";
            $params[] = $input['name'];
        }
        if (isset($input['icon'])) {
            $updates[] = "icon = ?";
            $params[] = $input['icon'];
        }
        if (isset($input['order'])) {
            $updates[] = "`order` = ?";
            $params[] = $input['order'];
        }
        
        if (empty($updates)) {
            sendError('No fields to update', 400);
        }
        
        $params[] = $id;
        $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        
        if (!$category) {
            sendError('Category not found', 404);
        }
        
        sendJSON($category);
        
    } elseif ($method === 'DELETE' && isset($pathParts[0])) {
        // DELETE CATEGORY (Admin only)
        requireAdmin();
        
        $id = $pathParts[0];
        
        // Check if any documents use this category
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM documents WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            sendError("Không thể xóa danh mục đang được sử dụng bởi $count văn bản", 400);
        }
        
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Category not found', 404);
        }
        
        sendJSON(['message' => 'Category deleted successfully']);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Categories Error: ' . $e->getMessage());
    sendError('Internal server error', 500);
}

?>