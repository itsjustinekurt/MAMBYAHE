<?php
// CSRF Handler
session_start();

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Clean up old CSRF token
function cleanup_csrf_token() {
    if (isset($_SESSION['csrf_token'])) {
        unset($_SESSION['csrf_token']);
    }
}

// Get CSRF token for forms
function get_csrf_token() {
    return generate_csrf_token();
}

// Add CSRF token to form
function add_csrf_token($form) {
    $token = generate_csrf_token();
    $form .= "<input type='hidden' name='csrf_token' value='" . $token . "'>";
    return $form;
}

// Add CSRF token to JavaScript
function add_csrf_token_js() {
    echo "<script>
        const csrf_token = '" . generate_csrf_token() . "';
    </script>";
}
?>
