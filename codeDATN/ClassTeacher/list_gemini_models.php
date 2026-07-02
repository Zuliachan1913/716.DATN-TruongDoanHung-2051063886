<?php
$apiKey = 'AQ.Ab8RN6K-KU_TqwWXUuOanuWi2zZbgQdZ6z18XyCwnmKlf7Y74w';

// First, let's see what models are available
$listUrl = "https://generativelanguage.googleapis.com/v1/models?key=$apiKey";

$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== LIST MODELS ===\n";
echo "HTTP Code: $httpCode\n";
$data = @json_decode($response, true);
if ($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo $response;
}
?>
