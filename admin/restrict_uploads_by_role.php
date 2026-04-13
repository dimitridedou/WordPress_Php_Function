function restrict_uploads_by_role($file) {
    // Λίστα των επιτρεπόμενων ρόλων
    $allowed_roles = array('tech', 'developer', 'cybersecurity'); // άλλαξε ανάλογα
    // Λίστα των επιτρεπόμενων τύπων αρχείων
    $allowed_types = array('image/png', 'image/webp');
    // Μέγιστο μέγεθος σε bytes (200 KB)
    $max_size = 200 * 1024;

    // Πάρε τον τρέχοντα χρήστη
    $user = wp_get_current_user();

    // Έλεγχος αν ο χρήστης ανήκει σε έναν από τους επιτρεπόμενους ρόλους
    $has_role = false;
    foreach ($allowed_roles as $role) {
        if (in_array($role, (array) $user->roles)) {
            $has_role = true;
            break;
        }
    }

    // Αν δεν έχει τον σωστό ρόλο, απλά επέτρεψε το upload (ή μπλόκαρέ το, ανάλογα)
    if (!$has_role) {
        return $file;
    }

    // Έλεγχος τύπου αρχείου
    if (!in_array($file['type'], $allowed_types)) {
        $file['error'] = 'Μπορείτε να ανεβάσετε μόνο PNG ή WebP αρχεία.';
        return $file;
    }

    // Έλεγχος μεγέθους αρχείου
    if ($file['size'] > $max_size) {
        $file['error'] = 'Το αρχείο σας υπερβαίνει τα 200 KB.';
        return $file;
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'restrict_uploads_by_role');
