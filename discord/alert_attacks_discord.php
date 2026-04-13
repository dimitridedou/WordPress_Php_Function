add_action('wp_login_failed', 'grcode_failed_login_monitor');

/* ========= ΡΥΘΜΙΣΗ WEBHOOK ========= */
function grcode_get_webhook_url() {
    return 'Webhook_here_';
}

/* ========= BOT DETECTION ========= */
function grcode_is_bot($ua) {
    $bots = [
        'bot','crawl','slurp','spider','curl','wget','python','java',
        'headless','scanner','httpclient','libwww','scrapy','feed'
    ];

    foreach ($bots as $b) {
        if (stripos($ua, $b) !== false) {
            return "Yes ({$b})";
        }
    }
    return "No";
}

/* ========= OS, BROWSER & DEVICE INFORMATION ========= */
function grcode_get_os_browser_device($ua) {

    $os = 'Unknown OS';
    $browser = 'Unknown Browser';
    $device = 'Desktop';

    // OS
    if (preg_match('/windows/i', $ua)) $os = 'Windows';
    elseif (preg_match('/macintosh|mac os x/i', $ua)) $os = 'Mac OS';
    elseif (preg_match('/linux/i', $ua)) $os = 'Linux';
    elseif (preg_match('/android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iphone|ipad/i', $ua)) $os = 'iOS';

    // Browser
    if (preg_match('/chrome/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/firefox/i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/safari/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/edge/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/opera/i', $ua)) $browser = 'Opera';

    // Device
    if (preg_match('/mobile/i', $ua) || preg_match('/iphone|android/i', $ua)) $device = 'Mobile';
    elseif (preg_match('/ipad|tablet/i', $ua)) $device = 'Tablet';

    return [$os, $browser, $device];
}

/* ========= MAIN FUNCTION ========= */
function grcode_failed_login_monitor($username) {

    $key = 'grcode_failed_logins_counter';
    $attempts = (int) get_transient($key);
    $attempts++;

    set_transient($key, $attempts, 15 * MINUTE_IN_SECONDS);

    if ($attempts >= 5) {

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'No UA';
        $time = current_time('Y-m-d H:i:s');
		$request_method => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        list($os, $browser, $device) = grcode_get_os_browser_device($ua);
        $is_bot = grcode_is_bot($ua);
        $hostname = @gethostbyaddr($ip);
        $all_cookie => $_COOKIE,  // όλα τα cookies
        $webhook_url = grcode_get_webhook_url();
		$referer          = $_SERVER['HTTP_REFERER'] ?? 'None';
    	$server_name      = $_SERVER['SERVER_NAME'] ?? 'Unknown';
    	$server_protocol  = $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown';
		$request_uri      = $_SERVER['REQUEST_URI'] ?? 'Unknown';
    	$query_string     = $_SERVER['QUERY_STRING'] ?? '';
    	$http_host        = $_SERVER['HTTP_HOST'] ?? 'Unknown';
    $http_connection  = $_SERVER['HTTP_CONNECTION'] ?? '';
        $message = [
            "content" =>
                "**🚨 5 Failed Login Attempts Detected**\n" .
                "**Username:** {$username}\n" .
				"**Request Method:** {$request_method}\n" .
                "**IP:** {$ip}\n" .
                "**Hostname:** {$hostname}\n" .
                "**Operating System:** {$os}\n" .
                "**Browser:** {$browser}\n" .
                "**Device:** {$device}\n" .
                "**Bot Detected:** {$is_bot}\n" .
                "**User Agent:** {$ua}\n" .
                "**Time:** {$time}" .
				"**Server Νame :** {$server_name}" .
				"**Server Protocol:** {$server_protocol}" .
				"**Query String:** {$query_string}" .
				"**Server Νame :** {$server_name}" .
				"**Server Protocol:** {$server_protocol}" .
				"**http connection** {$http_connection}" .
				"**Request uri:** {$request_uri}" .
				"**Cookies:** {$all_cookie}"
        ];

        wp_remote_post($webhook_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($message),
            'method'  => 'POST',
            'timeout' => 10,
        ]);

        delete_transient($key);
    }
}
