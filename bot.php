<?php
// === LẤY TOKEN & KEY TỪ BIẾN MÔI TRƯỜNG ===
$BOT_TOKEN = $_SERVER['BOT_TOKEN'] ?? die('Thiếu BOT_TOKEN');
$API_KEY   = $_SERVER['API_KEY']   ?? die('Thiếu API_KEY');

// === NHẬN TIN NHẮN ===
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? '';

// === GỬI TIN NHẮN ===
function send($chat_id, $text, $token) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = http_build_query([
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ]);
    file_get_contents("$url?$data");
}

// === TẠO KEY ===
function createKeys($apiKey) {
    $url = 'https://api.authtool.app/public/v1/key/single-activate';
    $data = [
        'quantity' => 5,
        'packageIds' => [1, 2, 3],
        'duration' => 30,
        'unit' => 'day',
        'alias' => 'RenderBotKey',
        'isCleanable' => true
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey
        ],
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['response' => $response, 'code' => $code];
}

// === XỬ LÝ LỆNH ===
if ($text === '/createkey' && $chat_id) {
    send($chat_id, "Đang tạo 5 key...", $BOT_TOKEN);

    $result = createKeys($API_KEY);
    $json = json_decode($result['response'], true);

    if ($result['code'] == 201 && isset($json['data'])) {
        $msg = "<b>5 KEY ĐÃ TẠO THÀNH CÔNG!</b>\n\n";
        foreach ($json['data'] as $key) {
            $msg .= "<code>$key</code>\n";
        }
    } else {
        $msg = "Lỗi: " . ($json['message'] ?? 'Server error');
    }

    send($chat_id, $msg, $BOT_TOKEN);
}
?>
