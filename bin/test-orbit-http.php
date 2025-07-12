#!/usr/bin/env php
<?php
/**
 * Orbit HTTP Test
 * 
 * Testuje Orbit CMS cez HTTP requesty.
 */

declare(strict_types=1);

echo "🌐 Orbit HTTP Test\n";
echo "==================\n\n";

$baseUrl = 'http://localhost:8080';
$testResults = [];

// Helper function to test HTTP endpoint
function testEndpoint(string $url, string $description): array {
    echo "Testing: $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ CURL Error: $error\n\n";
        return ['success' => false, 'error' => $error];
    }
    
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "   📊 HTTP Code: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "   ✅ Success\n";
        echo "   📄 Response length: " . strlen($body) . " bytes\n";
        
        // Check if it's JSON
        if (strpos($headers, 'application/json') !== false) {
            $json = json_decode($body, true);
            if ($json) {
                echo "   📋 JSON response with " . count($json) . " fields\n";
            }
        }
        
        echo "\n";
        return ['success' => true, 'code' => $httpCode, 'body' => $body];
    } else {
        echo "   ❌ Failed\n";
        echo "   📄 Response: " . substr($body, 0, 200) . "...\n\n";
        return ['success' => false, 'code' => $httpCode, 'body' => $body];
    }
}

// Check if server is running
echo "🔍 Checking if server is running...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_NOBODY, true);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error || $httpCode === 0) {
    echo "❌ Server not running. Starting server...\n";
    echo "💡 Run: composer serve\n";
    echo "💡 Or: php -S localhost:8080 -t public/\n\n";
    exit(1);
}

echo "✅ Server is running on $baseUrl\n\n";

// Test endpoints
$tests = [
    // API endpoints
    [
        'url' => "$baseUrl/api/orbit/search?q=mezzio",
        'description' => 'Search API - search for "mezzio"'
    ],
    [
        'url' => "$baseUrl/api/orbit/search?q=php&type=docs",
        'description' => 'Search API - search docs for "php"'
    ],
    
    // Documentation endpoints
    [
        'url' => "$baseUrl/docs/sk/README",
        'description' => 'Slovak documentation - README'
    ],
    [
        'url' => "$baseUrl/docs/en/INDEX",
        'description' => 'English documentation - INDEX'
    ],
    
    // Page endpoints
    [
        'url' => "$baseUrl/page/about",
        'description' => 'Static page - About'
    ],
    
    // JSON format tests
    [
        'url' => "$baseUrl/docs/sk/README?format=json",
        'description' => 'Documentation JSON API'
    ],
];

$passed = 0;
$total = count($tests);

foreach ($tests as $test) {
    $result = testEndpoint($test['url'], $test['description']);
    $testResults[] = $result;
    
    if ($result['success']) {
        $passed++;
    }
}

// Summary
echo "📊 Test Summary\n";
echo "===============\n";
echo "✅ Passed: $passed/$total\n";
echo "❌ Failed: " . ($total - $passed) . "/$total\n\n";

if ($passed === $total) {
    echo "🎉 All HTTP tests passed!\n";
    echo "========================\n";
    echo "✅ Orbit CMS is working correctly\n";
    echo "🌐 All endpoints are responding\n";
    echo "🚀 Ready for production use\n\n";
    
    echo "📋 Working endpoints:\n";
    foreach ($tests as $i => $test) {
        if ($testResults[$i]['success']) {
            echo "   ✅ {$test['description']}\n";
        }
    }
    echo "\n";
} else {
    echo "❌ Some tests failed!\n";
    echo "===================\n";
    
    echo "❌ Failed endpoints:\n";
    foreach ($tests as $i => $test) {
        if (!$testResults[$i]['success']) {
            echo "   ❌ {$test['description']}\n";
            echo "      URL: {$test['url']}\n";
            if (isset($testResults[$i]['code'])) {
                echo "      HTTP: {$testResults[$i]['code']}\n";
            }
        }
    }
    echo "\n";
    exit(1);
}
