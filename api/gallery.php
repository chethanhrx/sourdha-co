<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // Get all albums with their images
        $stmt = $db->query("SELECT id, title, created_at FROM gallery_albums ORDER BY created_at DESC");
        $albums = [];
        while ($row = $stmt->fetch()) {
            $albums[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'created_at' => $row['created_at'],
                'images' => []
            ];
        }
        
        // Get images for each album
        foreach ($albums as &$album) {
            $imgStmt = $db->prepare("SELECT id, filename, file_path, sort_order FROM gallery_images WHERE album_id = ? ORDER BY sort_order, id");
            $imgStmt->execute([$album['id']]);
            while ($img = $imgStmt->fetch()) {
                $album['images'][] = [
                    'id' => (int)$img['id'],
                    'filename' => $img['filename'],
                    'file_path' => $img['file_path'],
                    'sort_order' => (int)$img['sort_order']
                ];
            }
        }
        unset($album);
        
        jsonResponse(['success' => true, 'albums' => $albums]);
        break;
        
    case 'POST':
        requireAuth();
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_album') {
            $title = trim($_POST['title'] ?? '');
            if (empty($title)) {
                jsonResponse(['success' => false, 'error' => 'Album title required']);
            }
            
            $stmt = $db->prepare("INSERT INTO gallery_albums (title) VALUES (?)");
            $stmt->execute([$title]);
            $albumId = $db->lastInsertId();
            
            // Handle image uploads
            $uploadedImages = [];
            $errors = [];
            if (!empty($_FILES['images'])) {
                global $ALLOWED_IMG_TYPES;
                $files = reArrayFiles($_FILES['images']);
                
                foreach ($files as $file) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errors[] = "Upload error for " . $file['name'] . ": code " . $file['error'];
                        continue;
                    }
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($mime, $ALLOWED_IMG_TYPES)) {
                        $errors[] = "Invalid type for " . $file['name'];
                        continue;
                    }
                    if ($file['size'] > MAX_FILE_SIZE) {
                        $errors[] = $file['name'] . " is too large";
                        continue;
                    }
                    
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $safeName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    
                    $galleryDir = rtrim(UPLOAD_DIR, '/') . '/gallery';
                    $uploadPath = $galleryDir . '/' . $safeName;
                    
                    if (!is_dir($galleryDir)) {
                        if (!mkdir($galleryDir, 0777, true)) {
                            $errors[] = "Failed to create gallery directory: $galleryDir";
                            break;
                        }
                        chmod($galleryDir, 0777);
                    }
                    
                    // Debug writability
                    $testFile = $galleryDir . '/test_write.txt';
                    if (file_put_contents($testFile, 'test') === false) {
                        $errors[] = "CRITICAL: Cannot write to gallery directory $galleryDir";
                    } else {
                        unlink($testFile);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $imgStmt = $db->prepare("INSERT INTO gallery_images (album_id, filename, file_path) VALUES (?, ?, ?)");
                        $imgStmt->execute([$albumId, $file['name'], 'uploads/gallery/' . $safeName]);
                        $uploadedImages[] = $db->lastInsertId();
                    } else {
                        $targetDir = dirname($uploadPath);
                        $isWritable = is_writable($targetDir) ? "Yes" : "No";
                        $exists = file_exists($targetDir) ? "Yes" : "No";
                        $errors[] = "Failed to move " . $file['name'] . ". Target: $uploadPath. Dir exists: $exists, Writable: $isWritable. PHP Error: " . error_get_last()['message'];
                    }
                }
            }
            
            if (count($uploadedImages) === 0 && !empty($_FILES['images'])) {
                jsonResponse(['success' => false, 'error' => 'No images were uploaded. ' . implode(', ', $errors)]);
            }

            jsonResponse(['success' => true, 'message' => 'Album created with ' . count($uploadedImages) . ' images', 'album_id' => $albumId, 'images' => count($uploadedImages), 'errors' => $errors]);
        }
        elseif ($action === 'update_album') {
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            
            if (!empty($title)) {
                $stmt = $db->prepare("UPDATE gallery_albums SET title = ? WHERE id = ?");
                $stmt->execute([$title, $id]);
            }
            
            // Handle new image uploads for existing album
            $uploadedImages = [];
            if (!empty($_FILES['images'])) {
                global $ALLOWED_IMG_TYPES;
                $files = reArrayFiles($_FILES['images']);
                $galleryDir = rtrim(UPLOAD_DIR, '/') . '/gallery';

                foreach ($files as $file) {
                    if ($file['error'] !== UPLOAD_ERR_OK) continue;
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    if (!in_array($mime, $ALLOWED_IMG_TYPES)) continue;
                    
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $safeName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $uploadPath = $galleryDir . '/' . $safeName;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $imgStmt = $db->prepare("INSERT INTO gallery_images (album_id, filename, file_path) VALUES (?, ?, ?)");
                        $imgStmt->execute([$id, $file['name'], 'uploads/gallery/' . $safeName]);
                        $uploadedImages[] = $db->lastInsertId();
                    }
                }
            }
            
            jsonResponse(['success' => true, 'message' => 'Album updated', 'images_added' => count($uploadedImages)]);
        }
        elseif ($action === 'delete_album') {
            $id = intval($_POST['id'] ?? 0);
            
            // Delete images first (cascade will handle DB, but we need to delete files)
            $imgStmt = $db->prepare("SELECT file_path FROM gallery_images WHERE album_id = ?");
            $imgStmt->execute([$id]);
            while ($img = $imgStmt->fetch()) {
                $fullPath = dirname(__DIR__) . '/' . $img['file_path'];
                if (file_exists($fullPath)) unlink($fullPath);
            }
            
            $stmt = $db->prepare("DELETE FROM gallery_albums WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(['success' => true, 'message' => 'Album deleted']);
        }
        elseif ($action === 'delete_image') {
            $id = intval($_POST['id'] ?? 0);
            
            $imgStmt = $db->prepare("SELECT file_path FROM gallery_images WHERE id = ?");
            $imgStmt->execute([$id]);
            $img = $imgStmt->fetch();
            
            if ($img) {
                $fullPath = dirname(__DIR__) . '/' . $img['file_path'];
                if (file_exists($fullPath)) unlink($fullPath);
                
                $stmt = $db->prepare("DELETE FROM gallery_images WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            jsonResponse(['success' => true, 'message' => 'Image deleted']);
        }
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
