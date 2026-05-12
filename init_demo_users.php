<?php
/**
 * Demo Users Initialization
 * This creates the demo admin and candidate accounts
 */

require_once __DIR__ . '/config/database.php';

// Check if demo users already exist
$existing = $conn->query("SELECT COUNT(*) as count FROM users WHERE email IN ('admin@mail.com', 'candidate@mail.com')")->fetch_assoc();
if ($existing['count'] >= 2) {
    // Users already exist, just verify they're there
    echo "Demo users already exist.\n";
} else {
    // Create admin user
    $admin_password = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, jamb_number, password_hash, role, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, 'admin', 1)
                            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
    $first_name = 'System';
    $last_name = 'Administrator';
    $email = 'admin@mail.com';
    $phone = '';
    $jamb_number = 'ADMIN001';
    
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $jamb_number, $admin_password);
    $stmt->execute();
    $stmt->close();
    echo "✓ Admin user created: admin@mail.com\n";
    
    // Create candidate user
    $candidate_password = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, jamb_number, password_hash, role, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, 'candidate', 1)
                            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
    $first_name = 'Adebayo';
    $last_name = 'Oluwaseun';
    $email = 'candidate@mail.com';
    $phone = '08012345678';
    $jamb_number = '10234567890AB';
    
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $jamb_number, $candidate_password);
    $stmt->execute();
    $stmt->close();
    echo "✓ Candidate user created: candidate@mail.com\n";
}

echo "\nDemo Credentials:\n";
echo "═══════════════════════════════════════\n";
echo "Admin:\n";
echo "  Email:    admin@mail.com\n";
echo "  Password: admin123\n\n";
echo "Candidate:\n";
echo "  Email:    candidate@mail.com\n";
echo "  Password: password123\n";
echo "═══════════════════════════════════════\n";
?>
