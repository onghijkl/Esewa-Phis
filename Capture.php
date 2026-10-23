<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'error'=>'POST only']); exit; }
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { echo json_encode(['ok'=>false,'error'=>'bad json']); exit; }
$data['_ip'] = $_SERVER['REMOTE_ADDR'] ?? null;
$data['_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
$data['_time'] = date('c');
$dir = __DIR__ . '/uploads';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$imagePath = null;
if (!empty($data['image']) && is_string($data['image'])) {
    $b64 = preg_replace('#^data:image/\w+;base64,#i', '', $data['image']);
    $bin = base64_decode($b64, true);
    if ($bin !== false && strlen($bin) > 0) {
        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $imagePath = 'uploads/' . $name;
        file_put_contents(__DIR__ . '/' . $imagePath, $bin);
        $data['_image_file'] = $imagePath;
    }
    $data['image'] = '[base64 omitted]';
}
file_put_contents(__DIR__ . '/data.txt', json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
echo json_encode(['ok' => true, 'image' => $imagePath]);
