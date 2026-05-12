<?php
/**
 * Database Reset Script
 * WARNING: This will delete all data and recreate the database structure
 * Only use this if you want to start fresh or fix database issues
 */

require_once __DIR__ . '/config/database.php';

// Get all tables
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Drop all tables
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "✓ All tables deleted\n";

// Recreate tables by calling initializeTables
// We need to redefine it here since it's in the config
function initializeTables($conn) {
    // Subjects table
    $conn->query("CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        phone VARCHAR(20),
        jamb_number VARCHAR(20) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('candidate', 'admin') DEFAULT 'candidate',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tests table
    $conn->query("CREATE TABLE IF NOT EXISTS tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        duration_minutes INT NOT NULL DEFAULT 45,
        pass_mark INT NOT NULL DEFAULT 50,
        total_marks INT NOT NULL DEFAULT 100,
        available_from DATETIME NOT NULL,
        available_until DATETIME NOT NULL,
        status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_dates (available_from, available_until),
        FOREIGN KEY (created_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Questions table
    $conn->query("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_text TEXT NOT NULL,
        subject_id INT NOT NULL,
        difficulty VARCHAR(50) DEFAULT 'medium',
        marks INT NOT NULL DEFAULT 1,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_subject_id (subject_id),
        INDEX idx_difficulty (difficulty),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Question Options table
    $conn->query("CREATE TABLE IF NOT EXISTS options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        option_text VARCHAR(500) NOT NULL,
        is_correct BOOLEAN DEFAULT FALSE,
        position INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        INDEX idx_question (question_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Test Questions table (many-to-many)
    $conn->query("CREATE TABLE IF NOT EXISTS test_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        test_id INT NOT NULL,
        question_id INT NOT NULL,
        position INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_test_question (test_id, question_id),
        FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        INDEX idx_test (test_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Attempts table (student attempts to take test)
    $conn->query("CREATE TABLE IF NOT EXISTS attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        test_id INT NOT NULL,
        started_at DATETIME NOT NULL,
        submitted_at DATETIME,
        is_complete BOOLEAN DEFAULT FALSE,
        score_marks INT DEFAULT 0,
        score_percent INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_test (user_id, test_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_test (test_id),
        INDEX idx_complete (is_complete)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Responses table (student answers)
    $conn->query("CREATE TABLE IF NOT EXISTS responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attempt_id INT NOT NULL,
        question_id INT NOT NULL,
        selected_option_id INT,
        is_correct BOOLEAN DEFAULT FALSE,
        marks_obtained INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        FOREIGN KEY (selected_option_id) REFERENCES options(id) ON DELETE SET NULL,
        INDEX idx_attempt (attempt_id),
        INDEX idx_question (question_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Login attempts table (for security)
    $conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        INDEX idx_email_time (email, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Recreate all tables
initializeTables($conn);
echo "✓ All tables recreated\n";

// Create demo users
$admin_password = password_hash('admin123', PASSWORD_BCRYPT);
$candidate_password = password_hash('password123', PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, jamb_number, password_hash, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");

$first_name = 'System';
$last_name = 'Administrator';
$email = 'admin@mail.com';
$phone = '';
$jamb_number = 'ADMIN001';
$role = 'admin';
$stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $jamb_number, $admin_password, $role);
$stmt->execute();
$stmt->close();

$first_name = 'Adebayo';
$last_name = 'Oluwaseun';
$email = 'candidate@mail.com';
$phone = '08012345678';
$jamb_number = '10234567890AB';
$role = 'candidate';
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, jamb_number, password_hash, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $jamb_number, $candidate_password, $role);
$stmt->execute();
$stmt->close();

echo "✓ Demo users created\n";

// Create demo subjects
$subjects = [
    ['name' => 'Verbal Reasoning', 'description' => 'Tests vocabulary, reading comprehension, and language skills'],
    ['name' => 'Numerical Aptitude', 'description' => 'Tests mathematical and numerical problem-solving abilities'],
    ['name' => 'Logical Reasoning', 'description' => 'Tests logical deduction and analytical thinking']
];

foreach ($subjects as $subject) {
    $stmt = $conn->prepare("INSERT INTO subjects (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $subject['name'], $subject['description']);
    $stmt->execute();
    $stmt->close();
}

echo "✓ Demo subjects created\n";
echo "\n✅ Database reset complete!\n";
echo "\nDemo Credentials:\n";
echo "  Admin: admin@mail.com / admin123\n";
echo "  Candidate: candidate@mail.com / password123\n";
echo "\nYou can now:\n";
echo "  1. Go to http://localhost/aptitude-test/public/init_demo_data.php to create demo questions\n";
echo "  2. Or manually create subjects and questions through the admin panel\n";
?>
