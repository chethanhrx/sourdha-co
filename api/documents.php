<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // Get documents grouped by year
        $stmt = $db->query("SELECT id, fiscal_year, name, filename, file_path, file_size, file_type, created_at FROM documents ORDER BY fiscal_year DESC, created_at DESC");
        $docs = [];
        while ($row = $stmt->fetch()) {
            $year = $row['fiscal_year'];
            if (!isset($docs[$year])) $docs[$year] = [];
            $docs[$year][] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'filename' => $row['filename'],
                'file_size' => (int)$row['file_size'],
                'file_type' => $row['file_type'],
                'file_path' => $row['file_path'],
                'created_at' => $row['created_at']
            ];
        }
        jsonResponse(['success' => true, 'documents' => $docs]);
        break;
        
    case 'POST':
        requireAuth();
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'upload') {
            $year = trim($_POST['fiscal_year'] ?? '');
            $name = trim($_POST['name'] ?? '');
            
            if (empty($year) || empty($name) || empty($_FILES['files'])) {
                jsonResponse(['success' => false, 'error' => 'Year, name, and files required']);
            }
            
            $files = reArrayFiles($_FILES['files']);
            $uploadedCount = 0;
            $errors = [];
            
            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) continue;
                
                // Validate file type
                global $ALLOWED_DOC_TYPES;
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mime, $ALLOWED_DOC_TYPES)) {
                    $errors[] = "Invalid type for " . $file['name'];
                    continue;
                }
                
                if ($file['size'] > MAX_FILE_SIZE) {
                    $errors[] = $file['name'] . " is too large";
                    continue;
                }
                
                // Generate safe filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $ext;
                $uploadDir = UPLOAD_DIR . 'documents/';
                $uploadPath = $uploadDir . $safeName;
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $stmt = $db->prepare("INSERT INTO documents (fiscal_year, name, filename, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$year, $name, $file['name'], 'uploads/documents/' . $safeName, $file['size'], $mime]);
                    $uploadedCount++;
                } else {
                    $errors[] = "Failed to move " . $file['name'];
                }
            }
            
            if ($uploadedCount > 0) {
                jsonResponse(['success' => true, 'message' => "$uploadedCount documents uploaded", 'errors' => $errors]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Upload failed. ' . implode(', ', $errors)]);
            }
        }
        elseif ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $year = trim($_POST['fiscal_year'] ?? '');
            $name = trim($_POST['name'] ?? '');
            
            if (empty($year) || empty($name)) {
                jsonResponse(['success' => false, 'error' => 'Year and name required']);
            }
            
            $stmt = $db->prepare("UPDATE documents SET fiscal_year = ?, name = ? WHERE id = ?");
            $stmt->execute([$year, $name, $id]);
            
            jsonResponse(['success' => true, 'message' => 'Document updated']);
        }
        elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("SELECT file_path FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch();
            
            if ($doc) {
                $fullPath = dirname(__DIR__) . '/' . $doc['file_path'];
                if (file_exists($fullPath)) unlink($fullPath);
                
                $stmt = $db->prepare("DELETE FROM documents WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            jsonResponse(['success' => true, 'message' => 'Document deleted']);
        }
        else {
            jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
