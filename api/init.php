        <?php
// Database initialization - run once to setup database
require_once 'config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);

// Create tables    
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS interest_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rate_key VARCHAR(20) NOT NULL UNIQUE,
        rate_value DECIMAL(5,2) NOT NULL DEFAULT 0,
        label VARCHAR(100) NOT NULL,
        category ENUM('fd','rd') NOT NULL DEFAULT 'fd',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('success','alert','info','danger') NOT NULL DEFAULT 'info',
        icon VARCHAR(50) DEFAULT 'bell',
        badge VARCHAR(50) DEFAULT 'New',
        active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fiscal_year VARCHAR(10) NOT NULL,
        name VARCHAR(255) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery_albums (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gallery_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        album_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (album_id) REFERENCES gallery_albums(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS businesses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(50) DEFAULT '🏢',
        image_path VARCHAR(500),
        features TEXT,
        active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        message TEXT NOT NULL,
        status ENUM('new','read','replied') DEFAULT 'new',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $stmt->execute([ADMIN_DEFAULT_USER]);
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('divya2012', PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")
            ->execute([ADMIN_DEFAULT_USER, $hash]);
    }

    // Insert default rates if not exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM interest_rates");
    if ($stmt->fetchColumn() == 0) {
        $rates = [
            ['fd_3g', 10.00, '3+ Years (General)', 'fd'],
            ['fd_3s', 10.50, '3+ Years (Senior)', 'fd'],
            ['fd_2g', 9.00, '2+ Years (General)', 'fd'],
            ['fd_2s', 9.50, '2+ Years (Senior)', 'fd'],
            ['fd_1g', 8.00, '1+ Year (General)', 'fd'],
            ['fd_1s', 8.50, '1+ Year (Senior)', 'fd'],
            ['rd_36', 10.00, '36 Months RD', 'rd'],
            ['rd_24', 9.00, '24 Months RD', 'rd'],
            ['rd_12', 8.00, '12 Months RD', 'rd'],
        ];
        $stmt = $pdo->prepare("INSERT INTO interest_rates (rate_key, rate_value, label, category) VALUES (?, ?, ?, ?)");
        foreach ($rates as $r) {
            $stmt->execute($r);
        }
    }

    // Insert default notifications
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications");
    if ($stmt->fetchColumn() == 0) {
        $notifs = [
            ['Interest Rate Update', 'FD 3 Year+: 10.5% | Senior Citizens: 11% — Anniversary Special!', 'success', 'check', 'Now', 1, 0],
            ['Annual Meeting', '14-09-2025, 10:30 AM — Suvarna Sahakara Bhavan, Thirthahalli', 'alert', 'calendar', 'Important', 1, 1],
        ];
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type, icon, badge, active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($notifs as $n) {
            $stmt->execute($n);
        }
    }

    // Insert default settings
    $settings = [
        ['member_count', '1644'],
        ['total_deposits', '7.24 Cr'],
        ['total_loans', '7.37 Cr'],
        ['net_profit', '12.76 L'],
        ['anniversary_year', '13'],
        ['fiscal_year', '2024-25'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $s) {
        $stmt->execute($s);
    }

    jsonResponse(['success' => true, 'message' => 'Database initialized successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()]);
}
