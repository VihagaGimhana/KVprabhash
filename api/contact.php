<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($name) || !$email || empty($subject) || empty($message)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Validation failed. Please ensure all fields are filled with valid data.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO messages (msg_id, name, email, subject, message, created_at) VALUES (:msg_id, :name, :email, :subject, :message, :created_at)");
        $msg_id = uniqid('msg_');
        $created_at = date('Y-m-d H:i:s');

        $stmt->execute([
            ':msg_id' => $msg_id,
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':created_at' => $created_at
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => "Thank you, $name! Your message has been sent successfully. Our concierge representative will contact you at $email shortly."
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error. Failed to save your message record.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
exit;
?>
?>