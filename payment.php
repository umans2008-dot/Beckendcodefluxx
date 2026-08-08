<?php
require_once 'config.php'; //[span_4](start_span)[span_4](end_span)

// Header CORS wajib agar Netlify bisa mengakses backend ini
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? ''; //[span_5](start_span)[span_5](end_span)

// 1. BUAT TRANSAKSI BARU (QRIS)
if ($action === 'create') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT); //[span_6](start_span)[span_6](end_span)
    $note = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Pembelian Produk'; //[span_7](start_span)[span_7](end_span)
    
    if (!$amount || $amount < 1000) { //[span_8](start_span)[span_8](end_span)
        echo json_encode(['success' => false, 'message' => 'Nominal tidak valid (min. Rp1.000)']); //[span_9](start_span)[span_9](end_span)
        exit; //[span_10](start_span)[span_10](end_span)
    }

    $uniqueCode = 'ORDER-' . time() . rand(100, 999); //[span_11](start_span)[span_11](end_span)
    $validTime = 1800; //[span_12](start_span)[span_12](end_span)
    $typeFee = '1'; //[span_13](start_span)[span_13](end_span)

    $signature = md5(PAYDISINI_API_KEY . $uniqueCode . PAYDISINI_SERVICE_QRIS . $amount . $validTime . 'NewTransaction'); //[span_14](start_span)[span_14](end_span)

    $payload = [
        'key' => PAYDISINI_API_KEY, //[span_15](start_span)[span_15](end_span)
        'request' => 'new', //[span_16](start_span)[span_16](end_span)
        'unique_code' => $uniqueCode, //[span_17](start_span)[span_17](end_span)
        'service' => PAYDISINI_SERVICE_QRIS, //[span_18](start_span)[span_18](end_span)
        'amount' => $amount, //[span_19](start_span)[span_19](end_span)
        'note' => $note, //[span_20](start_span)[span_20](end_span)
        'valid_time' => $validTime, //[span_21](start_span)[span_21](end_span)
        'type_fee' => $typeFee, //[span_22](start_span)[span_22](end_span)
        'signature' => $signature //[span_23](start_span)[span_23](end_span)
    ];

    $ch = curl_init(PAYDISINI_ENDPOINT); //[span_24](start_span)[span_24](end_span)
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload)
    ]); //[span_25](start_span)[span_25](end_span)
    $response = curl_exec($ch); //[span_26](start_span)[span_26](end_span)
    curl_close($ch); //[span_27](start_span)[span_27](end_span)

    echo $response; //[span_28](start_span)[span_28](end_span)
    exit; //[span_29](start_span)[span_29](end_span)
}

// 2. CEK STATUS TRANSAKSI
if ($action === 'check') {
    $uniqueCode = filter_input(INPUT_GET, 'unique_code', FILTER_SANITIZE_SPECIAL_CHARS); //[span_30](start_span)[span_30](end_span)

    $signature = md5(PAYDISINI_API_KEY . $uniqueCode . 'StatusTransaction'); //[span_31](start_span)[span_31](end_span)

    $payload = [
        'key' => PAYDISINI_API_KEY, //[span_32](start_span)[span_32](end_span)
        'request' => 'status', //[span_33](start_span)[span_33](end_span)
        'unique_code' => $uniqueCode, //[span_34](start_span)[span_34](end_span)
        'signature' => $signature //[span_35](start_span)[span_35](end_span)
    ];

    $ch = curl_init(PAYDISINI_ENDPOINT); //[span_36](start_span)[span_36](end_span)
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload)
    ]); //[span_37](start_span)[span_37](end_span)
    $response = curl_exec($ch); //[span_38](start_span)[span_38](end_span)
    curl_close($ch); //[span_39](start_span)[span_39](end_span)

    echo $response; //[span_40](start_span)[span_40](end_span)
    exit; //[span_41](start_span)[span_41](end_span)
}

// 3. WEBHOOK / CALLBACK
if ($action === 'callback') {
    $key = $_POST['key'] ?? ''; //[span_42](start_span)[span_42](end_span)
    $uniqueCode = $_POST['unique_code'] ?? ''; //[span_43](start_span)[span_43](end_span)
    $status = $_POST['status'] ?? ''; //[span_44](start_span)[span_44](end_span)
    $signature = $_POST['signature'] ?? ''; //[span_45](start_span)[span_45](end_span)

    $expectedSignature = md5(PAYDISINI_API_KEY . $uniqueCode . 'CallbackStatus'); //[span_46](start_span)[span_46](end_span)

    if ($signature === $expectedSignature && $status === 'Success') { //[span_47](start_span)[span_47](end_span)
        echo json_encode(['success' => true]); //[span_48](start_span)[span_48](end_span)
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid signature or pending']); //[span_49](start_span)[span_49](end_span)
    }
    exit; //[span_50](start_span)[span_50](end_span)
}
