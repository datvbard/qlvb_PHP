<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$pathParts = explode('/', trim($path, '/'));

try {
    $user = getCurrentUser();
    $db = getDb();
    
    if ($method === 'GET' && $path === '/') {
        // GET ALL MENU ITEMS
        $stmt = $db->query("SELECT * FROM menu_items ORDER BY `order` ASC");
        $menuItems = $stmt->fetchAll();
        sendJSON($menuItems);
        
    } elseif ($method === 'POST' && $path === '/') {
        // CREATE MENU ITEM (Admin only)
        requireAdmin();
        
        $input = getJSONInput();
        if (!isset($input['title'], $input['path'])) {
            sendError('Missing required fields', 400);
        }
        
        $id = generateUUID();
        $stmt = $db->prepare("
            INSERT INTO menu_items (id, title, path, icon, `order`, parent_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $input['title'],
            $input['path'],
            $input['icon'] ?? '📋',
            $input['order'] ?? 0,
            $input['parent_id'] ?? null
        ]);
        
        $menuItem = [
            'id' => $id,
            'title' => $input['title'],
            'path' => $input['path'],
            'icon' => $input['icon'] ?? '📋',
            'order' => $input['order'] ?? 0,
            'parent_id' => $input['parent_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        sendJSON($menuItem, 201);
        
    } elseif ($method === 'PUT' && isset($pathParts[0])) {
        // UPDATE MENU ITEM (Admin only)
        requireAdmin();
        
        $id = $pathParts[0];
        $input = getJSONInput();
        
        $updates = [];
        $params = [];
        
        if (isset($input['title'])) {
            $updates[] = "title = ?";
            $params[] = $input['title'];
        }
        if (isset($input['path'])) {
            $updates[] = "path = ?";
            $params[] = $input['path'];
        }
        if (isset($input['icon'])) {
            $updates[] = "icon = ?";
            $params[] = $input['icon'];
        }
        if (isset($input['order'])) {
            $updates[] = "`order` = ?";
            $params[] = $input['order'];
        }
        if (isset($input['parent_id'])) {
            $updates[] = "parent_id = ?";
            $params[] = $input['parent_id'];
        }
        
        if (empty($updates)) {
            sendError('No fields to update', 400);
        }
        
        $params[] = $id;
        $sql = "UPDATE menu_items SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $menuItem = $stmt->fetch();
        
        if (!$menuItem) {
            sendError('Menu item not found', 404);
        }
        
        sendJSON($menuItem);
        
    } elseif ($method === 'DELETE' && isset($pathParts[0])) {
        // DELETE MENU ITEM (Admin only)
        requireAdmin();
        
        $id = $pathParts[0];
        
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Menu item not found', 404);
        }
        
        sendJSON(['message' => 'Menu item deleted successfully']);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Menu Error: ' . $e->getMessage());
    sendError('Internal server error', 500);
}

?>