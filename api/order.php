<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. JSON මඟින් එන අමු දත්ත (Raw Input) කියවීම
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);

    // සර්වර් එකට ආපු දත්ත JSON ද කියා තහවුරු කරගැනීම
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data format received by server.']);
        exit;
    }

    // 2. දත්ත කියවීම සහ පිරිසිදු කිරීම (Sanitize)
    $name = isset($inputData['name']) ? trim(filter_var($inputData['name'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
    $email = isset($inputData['email']) ? trim(filter_var($inputData['email'], FILTER_VALIDATE_EMAIL)) : '';
    $items = isset($inputData['items']) ? $inputData['items'] : [];
    $total = isset($inputData['total']) ? filter_var($inputData['total'], FILTER_VALIDATE_FLOAT) : 0;

    // 3. දත්ත හිස්දැයි පරීක්ෂා කිරීම (Strict Validation)
    if (empty($name) || !$email || empty($items) || $total <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid name, email address, and add items to your cart.']);
        exit;
    }

    // 4. කෑම වර්ග ලැයිස්තුව එක පෙළට සකස් කිරීම
    $itemsSummaryArray = [];
    foreach ($items as $item) {
        $itemTitle = filter_var($item['title'], FILTER_SANITIZE_SPECIAL_CHARS);
        $itemQty = filter_var($item['qty'], FILTER_VALIDATE_INT);
        $itemsSummaryArray[] = $itemTitle . " (x" . $itemQty . ")";
    }
    $items_string = implode(', ', $itemsSummaryArray);

    try {
        // 5. Database එකට ඇතුලත් කිරීම
        $query = "INSERT INTO orders (order_id, customer_name, customer_email, items_ordered, total_price, created_at) 
                  VALUES (:order_id, :customer_name, :customer_email, :items_ordered, :total_price, :created_at)";

        $stmt = $conn->prepare($query);
        $order_id = uniqid('ord_');
        $created_at = date('Y-m-d H:i:s');

        $stmt->execute([
            ':order_id' => $order_id,
            ':customer_name' => $name,
            ':customer_email' => $email,
            ':items_ordered' => $items_string,
            ':total_price' => $total,
            ':created_at' => $created_at
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => "Success! Order ($order_id) has been placed for $name. Total Amount: Rs. " . number_format($total, 2)
        ]);
    } catch(PDOException $e) {
        // Database එකෙන් එන සැබෑ දෝෂය (Error) බ්‍රව්සර් එකට එවීම (මඟ හැරීම පහසු වීමට)
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database Query Failure: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
exit;
?>