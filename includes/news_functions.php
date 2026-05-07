<?php
/**
 * News Functions
 * Fetches Philippine news from NewsAPI with robust caching
 */

/**
 * Get Philippine news headlines
 * Fixed: Switched from 'top-headlines' to 'everything' for better result reliability
 */
function getPhilippineNews($limit = 5) {
    // API Key from environment or hardcoded fallback
    $api_key = getenv('NEWS_API_KEY') ?: '2b7af5534d164becbe913b5dbede07dc'; 
    
    // Technical Fix: Using 'everything' with a search query is more reliable for regional news 
    // than the 'country=ph' filter which often returns 0 results on free accounts.
    $query = urlencode('Philippines OR "Metro Manila"');
    $api_url = "https://newsapi.org/v2/everything?q={$query}&sortBy=publishedAt&language=en&pageSize=" . intval($limit) . "&apiKey=" . urlencode($api_key);
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Barangay-eServices/1.0',
            CURLOPT_SSL_VERIFYPEER => false, // Set to true if you have valid CA certs on your server
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            
            // Check if articles exist; if totalResults is 0, this returns an empty array
            if ($data && isset($data['status']) && $data['status'] === 'ok' && !empty($data['articles'])) {
                return $data['articles'];
            }
        }
    } catch (Exception $e) {
        error_log('News API Exception: ' . $e->getMessage());
    }
    
    return [];
}

/**
 * Get cached news with layered fallback
 */
function getCachedPhilippineNews($limit = 5, $cache_time = 1800) {
    $cache_dir = __DIR__ . '/../cache';
    $cache_file = $cache_dir . '/news_cache.json';
    
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    
    // 1. Try Fresh Cache
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        $cached_data = json_decode(@file_get_contents($cache_file), true);
        if ($cached_data) return array_slice($cached_data, 0, $limit);
    }
    
    // 2. Try Fresh API
    $news = getPhilippineNews($limit);
    
    // 3. Cache and Return if API succeeded
    if (!empty($news)) {
        @file_put_contents($cache_file, json_encode($news), LOCK_EX);
        return $news;
    }
    
    // 4. API returned 0 results? Try Old Cache (Even if expired)
    if (file_exists($cache_file)) {
        $old_data = json_decode(@file_get_contents($cache_file), true);
        if ($old_data) return array_slice($old_data, 0, $limit);
    }
    
    // 5. Ultimate Fallback: Demo News
    return getFallbackNews($limit);
}

/**
 * Fallback news items
 */
function getFallbackNews($limit = 5) {
    $fallback = [
        [
            'title' => 'Barangay Community Development Program Launched',
            'description' => 'Local government units initiate new community development initiatives to improve services.',
            'source' => ['name' => 'Barangay News'],
            'publishedAt' => date('c', strtotime('-1 day')),
            'url' => '#',
            'urlToImage' => null
        ],
        [
            'title' => 'Online Services Adoption Growing in Local Government',
            'description' => 'More barangays embrace digital transformation to provide better citizen services.',
            'source' => ['name' => 'PH News'],
            'publishedAt' => date('c', strtotime('-2 days')),
            'url' => '#',
            'urlToImage' => null
        ]
    ];
    return array_slice($fallback, 0, $limit);
}

/**
 * Display News HTML matching your dashboard UI
 */
function displayNewsHTML($news, $limit = 5) {
    $html = '<section class="news-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">📰 Philippine News</h3>
                    <span class="badge badge-success" style="background-color: #10b981; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">LIVE UPDATES</span>
                </div>
                <div class="news-container" style="display: flex; flex-direction: column; gap: 15px;">';
    
    if (empty($news)) {
        $html .= '<p class="text-muted">No news currently available.</p>';
    } else {
        foreach (array_slice($news, 0, $limit) as $article) {
            $title = htmlspecialchars($article['title']);
            $source = htmlspecialchars($article['source']['name']);
            $date = date('M d, Y', strtotime($article['publishedAt']));
            $desc = htmlspecialchars($article['description'] ?? '');
            
            $html .= "
            <div class='news-item' style='border-left: 4px solid #3b82f6; background: #f9fafb; padding: 15px; border-radius: 8px;'>
                <h4 style='margin: 0 0 5px 0; font-size: 16px;'><a href='{$article['url']}' target='_blank' style='color: #2563eb; text-decoration: none;'>{$title}</a></h4>
                <p style='margin: 0 0 10px 0; font-size: 14px; color: #4b5563;'>{$desc}</p>
                <div style='font-size: 12px; color: #9ca3af;'>
                    <span style='background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; margin-right: 10px;'>{$source}</span>
                    <span>{$date}</span>
                </div>
            </div>";
        }
    }
    
    $html .= '</div></section>';
    return $html;
}