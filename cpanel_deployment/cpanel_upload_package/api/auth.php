<?php
require_once 'config.php';

// ===================================
// AUTHENTICATION API
// ===================================

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

try {
    if ($method === 'POST' && $path === '/register') {
        // REGISTER
        $input = getJSONInput();
        
        if (!isset($input['username'], $input['password'], $input['name'], $input['email'])) {
            sendError('Missing required fields', 400);
        }
        
        $db = getDb();
        
        // Check if username exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        if ($stmt->fetch()) {
            sendError('Username already exists', 400);
        }
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            sendError('Email already exists', 400);
        }
        
        // Check if first user (make admin)
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        $role = ($count == 0) ? 'admin' : 'user';
        
        // Create user
        $id = generateUUID();
        $passwordHash = hashPassword($input['password']);
        
        $stmt = $db->prepare("
            INSERT INTO users (id, username, password_hash, name, email, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $input['username'],
            $passwordHash,
            $input['name'],
            $input['email'],
            $role
        ]);
        
        // Create token
        $token = createJWT(['sub' => $input['username']]);
        
        // Return user data
        $user = [
            'id' => $id,
            'username' => $input['username'],
            'name' => $input['name'],
            'email' => $input['email'],
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        sendJSON([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user
        ]);
        
    } elseif ($method === 'POST' && $path === '/login') {
        // LOGIN
        $input = getJSONInput();
        
        if (!isset($input['username'], $input['password'])) {
            sendError('Missing username or password', 400);
        }
        
        $db = getDb();
        $stmt = $db->prepare("
            SELECT id, username, password_hash, name, email, role, created_at 
            FROM users WHERE username = ?
        ");
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch();
        
        if (!$user || !verifyPassword($input['password'], $user['password_hash'])) {
            sendError('Invalid username or password', 401);
        }
        
        // Create token
        $token = createJWT(['sub' => $user['username']]);
        
        // Remove password hash from response
        unset($user['password_hash']);
        
        sendJSON([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user
        ]);
        
    } elseif ($method === 'GET' && $path === '/me') {
        // GET CURRENT USER
        $user = getCurrentUser();
        sendJSON($user);
        
    } else {
        sendError('Not found', 404);
    }
    
} catch (Exception $e) {
    logError('Auth Error: ' . $e->getMessage());
    sendError('Internal server error', 500);
}

?>