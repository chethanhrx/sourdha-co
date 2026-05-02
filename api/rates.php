<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // Get all rates
        $stmt = $db->query("SELECT rate_key, rate_value, label, category FROM interest_rates ORDER BY category, id");
        $rates = [];
        while ($row = $stmt->fetch()) {
            $rates[$row['rate_key']] = [
                'value' => (float)$row['rate_value'],
                'label' => $row['label'],
                'category' => $row['category']
            ];
        }
        
        // Ensure all required keys exist to prevent JS errors
        $required_keys = ['fd_3g', 'fd_3s', 'fd_2g', 'fd_2s', 'fd_1g', 'fd_1s', 'rd_36', 'rd_24', 'rd_12'];
        foreach ($required_keys as $key) {
            if (!isset($rates[$key])) {
                $rates[$key] = ['value' => 0, 'label' => $key, 'category' => strpos($key, 'fd') === 0 ? 'fd' : 'rd'];
            }
        }
        
        jsonResponse(['success' => true, 'rates' => $rates]);
        break;
        
    case 'POST':
        requireAuth();
        
        // Update rates (batch)
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['rates']) || !is_array($data['rates'])) {
            jsonResponse(['success' => false, 'error' => 'Invalid rates data']);
        }
        
        $stmt = $db->prepare("UPDATE interest_rates SET rate_value = ? WHERE rate_key = ?");
        $updated = 0;
        foreach ($data['rates'] as $key => $value) {
            $stmt->execute([$value, $key]);
            $updated += $stmt->rowCount();
        }
        
        jsonResponse(['success' => true, 'message' => 'Rates updated', 'updated' => $updated]);
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed']);
}
