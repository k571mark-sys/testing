<?php
/**
 * Demo Data Initialization
 * Run this file once to populate the database with demo questions and tests
 */

require_once __DIR__ . '/config/database.php';

// Check if demo data already exists
$existing = $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc();
if ($existing['count'] > 0) {
    echo "Demo data already exists. Skipping initialization.";
    exit();
}

// Get admin user ID (created during auth)
$admin = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch_assoc();
$admin_id = $admin['id'] ?? 1;

// Create subjects first
$subjects_data = [
    ['name' => 'Verbal Reasoning', 'description' => 'Tests vocabulary, reading comprehension, and language skills'],
    ['name' => 'Numerical Aptitude', 'description' => 'Tests mathematical and numerical problem-solving abilities'],
    ['name' => 'Logical Reasoning', 'description' => 'Tests logical deduction and analytical thinking']
];

$subject_ids = [];
foreach ($subjects_data as $subject) {
    $stmt = $conn->prepare("INSERT INTO subjects (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $subject['name'], $subject['description']);
    $stmt->execute();
    $subject_ids[$subject['name']] = $conn->insert_id;
    $stmt->close();
}

// Sample questions by subject
$questions_data = [
    // Verbal Reasoning
    ['subject' => 'Verbal Reasoning', 'difficulty' => 'easy', 'text' => 'Which of the following words is closest in meaning to "diligent"?', 
     'options' => ['Lazy', 'Hardworking', 'Careless', 'Ignorant'], 'correct' => 1],
    ['subject' => 'Verbal Reasoning', 'difficulty' => 'easy', 'text' => 'What is the opposite of "benevolent"?',
     'options' => ['Kind', 'Generous', 'Malevolent', 'Helpful'], 'correct' => 2],
    ['subject' => 'Verbal Reasoning', 'difficulty' => 'medium', 'text' => 'Antonym of "laconic"',
     'options' => ['Brief', 'Verbose', 'Quiet', 'Slow'], 'correct' => 1],
    ['subject' => 'Verbal Reasoning', 'difficulty' => 'medium', 'text' => 'Which word best completes: "The politician\'s speech was so ___ that many people fell asleep."?',
     'options' => ['Engaging', 'Dynamic', 'Tedious', 'Interesting'], 'correct' => 2],
    ['subject' => 'Verbal Reasoning', 'difficulty' => 'hard', 'text' => 'What does "ephemeral" mean?',
     'options' => ['Permanent', 'Lasting', 'Temporary', 'Eternal'], 'correct' => 2],
    
    // Numerical Aptitude
    ['subject' => 'Numerical Aptitude', 'difficulty' => 'easy', 'text' => 'What is 15% of 200?',
     'options' => ['20', '30', '40', '50'], 'correct' => 1],
    ['subject' => 'Numerical Aptitude', 'difficulty' => 'easy', 'text' => 'If a = 5 and b = 3, what is a + b?',
     'options' => ['6', '8', '9', '15'], 'correct' => 1],
    ['subject' => 'Numerical Aptitude', 'difficulty' => 'medium', 'text' => 'Simplify: 3(x+4) – 2(x–1)',
     'options' => ['x + 12', 'x + 14', '5x + 10', '3x + 14'], 'correct' => 1],
    ['subject' => 'Numerical Aptitude', 'difficulty' => 'medium', 'text' => 'If x:y = 3:5 and y:z = 2:7, what is x:y:z?',
     'options' => ['6:10:35', '3:5:7', '6:10:25', '3:2:7'], 'correct' => 0],
    ['subject' => 'Numerical Aptitude', 'difficulty' => 'hard', 'text' => 'What is the sum of angles in a triangle?',
     'options' => ['90°', '180°', '270°', '360°'], 'correct' => 1],
    
    // Logical Reasoning
    ['subject' => 'Logical Reasoning', 'difficulty' => 'easy', 'text' => 'If all cats are animals, and all animals are living things, then all cats are living things. Is this statement?',
     'options' => ['False', 'True', 'Uncertain', 'Contradictory'], 'correct' => 1],
    ['subject' => 'Logical Reasoning', 'difficulty' => 'medium', 'text' => 'If all A are B and all B are C, which must be true?',
     'options' => ['All C are A', 'All A are C', 'Some C are not A', 'C and A are unrelated'], 'correct' => 1],
    ['subject' => 'Logical Reasoning', 'difficulty' => 'medium', 'text' => 'If all Blooms are Glows and some Glows are Sparks, which is definitely true?',
     'options' => ['Some Blooms are Sparks', 'No Bloom is a Spark', 'All Blooms are Sparks', 'Some Blooms may be Sparks'], 'correct' => 3],
    ['subject' => 'Logical Reasoning', 'difficulty' => 'hard', 'text' => 'What comes next in the sequence: 2, 6, 12, 20, 30, ?',
     'options' => ['40', '42', '45', '50'], 'correct' => 1],
    ['subject' => 'Logical Reasoning', 'difficulty' => 'hard', 'text' => 'If A > B, B > C, and C > D, then what is the relationship between A and D?',
     'options' => ['A < D', 'A > D', 'A = D', 'Cannot be determined'], 'correct' => 1],
];

// Insert questions
$question_ids = [];
foreach ($questions_data as $q) {
    $question_text = $q['text'];
    $subject_id = $subject_ids[$q['subject']];
    $difficulty = $q['difficulty'];
    $marks = 1;
    
    $stmt = $conn->prepare("INSERT INTO questions (question_text, subject_id, difficulty, marks, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiii", $question_text, $subject_id, $difficulty, $marks, $admin_id);
    $stmt->execute();
    $question_id = $conn->insert_id;
    $question_ids[] = $question_id;
    
    // Insert options
    foreach ($q['options'] as $idx => $option_text) {
        $is_correct = ($idx === $q['correct']) ? 1 : 0;
        $position = $idx + 1;
        
        $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct, position) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $question_id, $option_text, $is_correct, $position);
        $stmt->execute();
    }
    $stmt->close();
}

// Create a test
$test_title = "ND Aptitude Screening — 2025/2026";
$test_description = "Annual aptitude screening for incoming ND students.";
$duration_minutes = 45;
$pass_mark = 50;
$total_marks = count($question_ids);
$available_from = date('Y-m-d H:i:s', strtotime('+1 day'));
$available_until = date('Y-m-d H:i:s', strtotime('+30 days'));
$status = 'active';

$stmt = $conn->prepare("INSERT INTO tests (title, description, duration_minutes, pass_mark, total_marks, available_from, available_until, status, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiiissi", $test_title, $test_description, $duration_minutes, $pass_mark, $total_marks, $available_from, $available_until, $status, $admin_id);
$stmt->execute();
$test_id = $conn->insert_id;
$stmt->close();

// Add questions to test
foreach ($question_ids as $qid) {
    $stmt = $conn->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $test_id, $qid);
    $stmt->execute();
}
$stmt->close();

echo "✓ Demo data initialization complete!\n";
echo "- Created " . count($subject_ids) . " subjects\n";
echo "- Created " . count($question_ids) . " questions\n";
echo "- Created 1 test\n";
echo "- Test ID: $test_id\n";
?>
