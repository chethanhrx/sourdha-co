<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // Get all active notifications (public) or all (admin)
        $admin = !empty($_SESSION['admin_id']);
        if ($admin) {
            $stmt = $db->query("SELECT id, title, message, type, icon, badge, active, sort_order, created_at FROM notifications ORDER BY sort_order, created_at DESC");
        } else {
            $stmt = $db->query("SELECT title, message, type, icon, badge FROM notifications WHERE active = 1 ORDER BY sort_order, created_at DESC");
        }
        $notifs = $stmt->fetchAll();
        jsonResponse(['success' => true, 'notifications' => $notifs]);
        break;
        
    case 'POST':
        requireAuth();
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $type = $_POST['type'] ?? 'info';
            $icon = trim($_POST['icon'] ?? 'bell');
            $badge = trim($_POST['badge'] ?? 'New');
            
            if (empty($title) || empty($message)) {
                jsonResponse(['success' => false, 'error' => 'Title and message required']);
            }
            
            $stmt = $db->prepare("INSERT INTO notifications (title, message, type, icon, badge) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $message, $type, $icon, $badge]);
            
            jsonResponse(['success' => true, 'message' => 'Notification created', 'id' => $db->lastInsertId()]);
        }
        elseif ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $type = $_POST['type'] ?? '';
            $active = isset($_POST['active']) ? intval($_POST['active']) : null;
            $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : null;
            
            $updates = [];
            $params = [];
            
            if ($title !== '') { $updates[] = "title = ?"; $params[] = $title; }
            if ($message !== '') { $updates[] = "message = ?"; $params[] = $message; }
            if ($type !== '') { $updates[] = "type = ?"; $params[] = $type; }
            if ($active !== null) { $updates[] = "active = ?"; $params[] = $active; }
            if ($sort_order !== null) { $updates[] = "sort_order = ?"; $params[] = $sort_order; }
            
            if (empty($updates)) {
                jsonResponse(['success' => false, 'error' => 'No fields to update']);
            }
            
            $params[] = $id;
            $stmt = $db->prepare("UPDATE notifications SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($params);
            
            jsonResponse(['success' => true, 'message' => 'Notification updated']);
        }
        elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'message' => 'Notification deleted']);
        }
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
