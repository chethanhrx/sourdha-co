<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'POST':
        $action = $_POST['action'] ?? '';
        
        // LOGIN
        if ($action === 'login') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                jsonResponse(['success' => false, 'error' => 'Username and password required']);
            }
            
            $stmt = $db->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                jsonResponse(['success' => true, 'message' => 'Login successful', 'user' => $admin['username']]);
            } else {
                http_response_code(401);
                jsonResponse(['success' => false, 'error' => 'Invalid credentials']);
            }
        }
        
        // LOGOUT
        elseif ($action === 'logout') {
            session_destroy();
            jsonResponse(['success' => true, 'message' => 'Logged out']);
        }
        
        // CHANGE PASSWORD
        elseif ($action === 'change_password') {
            requireAuth();
            
            $newPass = $_POST['new_password'] ?? '';
            if (strlen($newPass) < 5) {
                jsonResponse(['success' => false, 'error' => 'Password must be at least 5 characters']);
            }
            
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);
            
            jsonResponse(['success' => true, 'message' => 'Password updated']);
        }
        
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    case 'GET':
        // CHECK SESSION
        if (!empty($_SESSION['admin_id'])) {
            jsonResponse(['success' => true, 'authenticated' => true, 'user' => $_SESSION['admin_user'] ?? 'admin']);
        } else {
            jsonResponse(['success' => true, 'authenticated' => false]);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
