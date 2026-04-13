add_action('template_redirect', function() {
    // Αν ο χρήστης είναι ήδη συνδεδεμένος
    if (is_user_logged_in()) {
        // Αν είναι στη σελίδα /login/
        if (strpos($_SERVER['REQUEST_URI'], '/login/') !== false) {
            wp_redirect(home_url('/user/')); // Redirect στη σελίδα χρήστη
            exit;
        }
    }
});
