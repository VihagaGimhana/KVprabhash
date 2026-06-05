<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

// GET: Fetching reserved tables
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
    $time_slot = filter_input(INPUT_GET, 'time', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$date || !$time_slot) {
        echo json_encode(['status' => 'error', 'message' => 'Missing date or time parameter.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT table_id FROM reservations WHERE date = :date AND time_slot = :time_slot");
        $stmt->execute([':date' => $date, ':time_slot' => $time_slot]);
        $rows = $stmt->fetchAll();

        $occupied_tables = [];
        foreach ($rows as $row) {
            $occupied_tables[] = $row['table_id'];
        }

        echo json_encode([
            'status' => 'success',
            'date' => $date,
            'time' => $time_slot,
            'occupied' => $occupied_tables
        ]);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching database status.']);
    }
    exit;
}

// POST: Saving a new table booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
    $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
    $time_slot = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS));
    $table_id = trim(filter_input(INPUT_POST, 'table_id', FILTER_SANITIZE_SPECIAL_CHARS));
    $guests = filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT);

    if (empty($name) || !$email || empty($phone) || empty($date) || empty($time_slot) || empty($table_id) || !$guests) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please provide all details and select a table from the floor layout map.']);
        exit;
    }

    try {
        // Double-Booking Prevention Check
        $check_stmt = $conn->prepare("SELECT id FROM reservations WHERE date = :date AND time_slot = :time_slot AND table_id = :table_id");
        $check_stmt->execute([':date' => $date, ':time_slot' => $time_slot, ':table_id' => $table_id]);

        if ($check_stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Apologies, this table is already reserved for the selected date and time slot.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO reservations (res_id, name, email, phone, guests, date, time_slot, table_id, created_at) VALUES (:res_id, :name, :email, :phone, :guests, :date, :time_slot, :table_id, :created_at)");
        $res_id = uniqid('res_');
        $created_at = date('Y-m-d H:i:s');

        $stmt->execute([
            ':res_id' => $res_id,
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':guests' => $guests,
            ':date' => $date,
            ':time_slot' => $time_slot,
            ':table_id' => $table_id,
            ':created_at' => $created_at
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => "Excellent! Your table ($table_id) has been successfully reserved for $name on $date during the $time_slot slot."
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server error. Failed to save reservation parameters.']);
    }
    exit;
}
?>