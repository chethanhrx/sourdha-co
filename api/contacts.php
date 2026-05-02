<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        requireAuth();
        $stmt = $db->query("SELECT id, name, email, phone, message, status, created_at FROM contacts ORDER BY created_at DESC");
        jsonResponse(['success' => true, 'contacts' => $stmt->fetchAll()]);
        break;
        
    case 'POST':
        $action = $_POST['action'] ?? '';
        
        if ($action === 'submit') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($name) || empty($email) || empty($message)) {
                jsonResponse(['success' => false, 'error' => 'Name, email and message required']);
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['success' => false, 'error' => 'Invalid email']);
            }
            
            $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $message]);
            
            jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        }
        elseif ($action === 'delete') {
            requireAuth();
            $id = intval($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(['success' => true, 'message' => 'Message deleted']);
        }
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
