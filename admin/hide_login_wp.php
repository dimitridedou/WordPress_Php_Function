add_action('init', function() {
    // Παίρνουμε τη current URL
    $current_url = $_SERVER['REQUEST_URI'];

    // Αν ο χρήστης δεν είναι logged in
    if (!is_user_logged_in()) {
        // Αν πάει σε /wp-admin ή /wp-login.php
        if (strpos($current_url, '/wp-admin') !== false || strpos($current_url, '/wp-login.php') !== false) {
            wp_redirect(home_url('/login/')); // Redirect στη custom login σελίδα
            exit;
        }
    }
});
