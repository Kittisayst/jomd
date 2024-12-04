<?php
// Test API endpoints
function testEndpoint($endpoint, $method = 'GET', $data = null) {
    $curl = curl_init();
    $url = "http://" . $_SERVER['HTTP_HOST'] . 
           dirname($_SERVER['PHP_SELF']) . 
           "/includes/api/" . $endpoint;

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }
    }

    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return [
        'response' => json_decode($response, true),
        'status' => $httpCode
    ];
}

// Test cases
echo "Testing API endpoints:\n\n";

// 1. Get all files
echo "1. Testing get-files.php...\n";
$result = testEndpoint('get-files.php');
echo "Status: " . $result['status'] . "\n";
echo "Found files: " . count($result['response']['data']) . "\n\n";

// 2. Search files
echo "2. Testing search-files.php...\n";
$result = testEndpoint('search-files.php?keyword=Test');
echo "Status: " . $result['status'] . "\n";
echo "Found files: " . count($result['response']['data']) . "\n\n";

// 3. Save new file
echo "3. Testing save-file.php...\n";
$data = [
    'title' => 'API Test File',
    'content' => '# API Test Content'
];
$result = testEndpoint('save-file.php', 'POST', $data);
echo "Status: " . $result['status'] . "\n";
echo "Message: " . ($result['response']['message'] ?? 'No message') . "\n\n";

// 4. Get single file
if (isset($result['response']['data']['id'])) {
    $fileId = $result['response']['data']['id'];
    echo "4. Testing get-file.php...\n";
    $result = testEndpoint("get-file.php?id=$fileId");
    echo "Status: " . $result['status'] . "\n";
    echo "File found: " . ($result['response']['success'] ? 'Yes' : 'No') . "\n\n";
}

// Output results summary
echo "\nAPI Test Summary:\n";
echo "================\n";
echo "All endpoints should return 200 status code\n";
echo "All responses should be valid JSON\n";
echo "Check if any errors occurred above\n";
?>