<?php
$apiKey = 'AQ.Ab8RN6K-KU_TqwWXUuOanuWi2zZbgQdZ6z18XyCwnmKlf7Y74w';

// Test different model names
$models = ['gemini-pro', 'gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-2.0'];

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1/models/$model:generateContent?key=$apiKey";
    
    $payload = [
        'contents' => [[
            'role' => 'user',
            'parts' => [['text' => 'Hello']]
        ]]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Model: $model\n";
    echo "HTTP Code: $httpCode\n";
    
    $data = @json_decode($response, true);
    if ($data && isset($data['error'])) {
        echo "Error: " . $data['error']['message'] . "\n";
    } elseif ($httpCode === 200) {
        echo "SUCCESS!\n";
        break;
    } else {
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
    echo "\n";
}
?>
