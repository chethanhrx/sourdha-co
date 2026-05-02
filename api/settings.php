<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        jsonResponse(['success' => true, 'settings' => $settings]);
        break;
        
    case 'POST':
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['settings']) || !is_array($data['settings'])) {
            jsonResponse(['success' => false, 'error' => 'Invalid settings data']);
        }
        
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($data['settings'] as $key => $value) {
            $stmt->execute([$key, $value, $value]);
        }
        
        jsonResponse(['success' => true, 'message' => 'Settings updated']);
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
