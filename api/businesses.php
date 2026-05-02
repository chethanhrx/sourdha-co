<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        $stmt = $db->query("SELECT id, name, description, icon, image_path, features, active, created_at FROM businesses WHERE active = 1 ORDER BY created_at DESC");
        $biz = [];
        while ($row = $stmt->fetch()) {
            $biz[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'icon' => $row['icon'],
                'image_path' => $row['image_path'],
                'features' => $row['features'] ? explode(',', $row['features']) : [],
                'active' => (bool)$row['active']
            ];
        }
        jsonResponse(['success' => true, 'businesses' => $biz]);
        break;
        
    case 'POST':
        requireAuth();
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = trim($_POST['icon'] ?? '🏢');
            $features = trim($_POST['features'] ?? '');
            $imagePath = '';
            
            if (empty($name)) {
                jsonResponse(['success' => false, 'error' => 'Business name required']);
            }

            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeName = 'biz_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $uploadPath = UPLOAD_DIR . 'images/' . $safeName;
                
                if (!is_dir(UPLOAD_DIR . 'images/')) {
                    mkdir(UPLOAD_DIR . 'images/', 0777, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $imagePath = 'uploads/images/' . $safeName;
                }
            }
            
            $stmt = $db->prepare("INSERT INTO businesses (name, description, icon, image_path, features) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $icon, $imagePath, $features]);
            
            jsonResponse(['success' => true, 'message' => 'Business added', 'id' => $db->lastInsertId()]);
        }
        elseif ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $features = trim($_POST['features'] ?? '');
            $active = isset($_POST['active']) ? intval($_POST['active']) : 1;
            
            // Get current image path
            $stmt = $db->prepare("SELECT image_path FROM businesses WHERE id = ?");
            $stmt->execute([$id]);
            $currentBiz = $stmt->fetch();
            $imagePath = $currentBiz['image_path'] ?? '';

            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeName = 'biz_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $uploadPath = UPLOAD_DIR . 'images/' . $safeName;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Delete old image if exists
                    if (!empty($imagePath) && file_exists(dirname(__DIR__) . '/' . $imagePath)) {
                        unlink(dirname(__DIR__) . '/' . $imagePath);
                    }
                    $imagePath = 'uploads/images/' . $safeName;
                }
            }
            
            $stmt = $db->prepare("UPDATE businesses SET name = ?, description = ?, icon = ?, image_path = ?, features = ?, active = ? WHERE id = ?");
            $stmt->execute([$name, $description, $icon, $imagePath, $features, $active, $id]);
            
            jsonResponse(['success' => true, 'message' => 'Business updated']);
        }
        elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM businesses WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(['success' => true, 'message' => 'Business deleted']);
        }
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
