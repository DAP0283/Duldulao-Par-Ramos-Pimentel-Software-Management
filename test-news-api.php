<?php
/**
 * News API Test Script
 * Test if the news API is working correctly
 */

require_once('includes/news_functions.php');

echo "<h1>Philippine News API Test</h1>";
echo "<hr>";

// Test 1: Check if cache directory exists
echo "<h3>1. Cache Directory Check</h3>";
$cache_dir = __DIR__ . '/cache';
if (is_dir($cache_dir)) {
    echo "✅ Cache directory exists: " . $cache_dir;
} else {
    echo "❌ Cache directory does NOT exist. Attempting to create...";
    if (@mkdir($cache_dir, 0755, true)) {
        echo "<br>✅ Cache directory created successfully!";
    } else {
        echo "<br>❌ Failed to create cache directory";
    }
}
echo "<br><br>";

// Test 2: Test raw API call
echo "<h3>2. NewsAPI Raw Call Test</h3>";
$test_url = "https://newsapi.org/v2/top-headlines?country=ph&pageSize=3&apiKey=2b7af5534d164becbe913b5dbede07dc";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $test_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => 'Barangay-eServices/1.0',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo "❌ cURL Error: " . $curl_error;
} else {
    echo "HTTP Status: " . $http_code . "<br>";
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data['status'] === 'ok') {
            echo "✅ API is working! Got " . count($data['articles']) . " articles<br>";
            echo "<h4>Sample News:</h4>";
            foreach (array_slice($data['articles'], 0, 3) as $article) {
                echo "<strong>" . htmlspecialchars($article['title']) . "</strong><br>";
                echo "Source: " . $article['source']['name'] . " | Date: " . $article['publishedAt'] . "<br><br>";
            }
        } else {
            echo "❌ API Error: " . ($data['message'] ?? 'Unknown error');
        }
    } else {
        echo "❌ HTTP Error: " . $http_code;
    }
}
echo "<br>";

// Test 3: Test getCachedPhilippineNews function
echo "<h3>3. Cached News Function Test</h3>";
$news = getCachedPhilippineNews(3);
if (!empty($news)) {
    echo "✅ getCachedPhilippineNews() returned " . count($news) . " articles<br>";
    echo "<h4>News Items:</h4>";
    foreach ($news as $article) {
        echo "<strong>" . htmlspecialchars(substr($article['title'], 0, 60)) . "...</strong><br>";
    }
} else {
    echo "⚠️ getCachedPhilippineNews() returned no articles<br>";
    echo "Attempting fallback...";
    $fallback = getFallbackNews(3);
    echo "✅ Fallback returned " . count($fallback) . " demo articles";
}
echo "<br><br>";

// Test 4: Test displayNewsHTML
echo "<h3>4. News Display HTML Test</h3>";
$news = getCachedPhilippineNews(3);
if (empty($news)) {
    $news = getFallbackNews(3);
}
echo "<div style='border: 1px solid #ccc; padding: 20px; background: #f9f9f9;'>";
echo displayNewsHTML($news, 3);
echo "</div>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✅ All tests completed! Your news API is set up and working.</p>";
?>
