<?php
header('Content-Type: application/json');

// Replace with your API key
$apiKey = 'goldapi-fwwkpcsmbgd5q43-io';
$apiUrl = 'https://www.goldapi.io/api/XAU/INR'; // Or whatever your API is

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-access-token: $apiKey",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['price'])) {
        $pricePerOunce = floatval($data['price']);
        $pricePerGram = $pricePerOunce / 31.1035;

        echo json_encode(['rate' => round($pricePerGram, 2)]);
    } else {
        echo json_encode(['error' => 'Invalid API response']);
    }
} else {
    echo json_encode(['error' => 'API request failed']);
}
