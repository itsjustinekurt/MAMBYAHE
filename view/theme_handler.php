<?php
// Theme handler functions
function get_theme() {
    // Get theme from session or default to light
    return $_SESSION['theme'] ?? 'light';
}

function get_theme_class() {
    $theme = get_theme();
    return $theme === 'dark' ? 'theme-dark' : 'theme-light';
}

function get_theme_styles() {
    return "
        /* Theme Variables */
        :root {
            --primary-color: #198754;
            --background-color: #f8f9fa;
            --card-background: white;
            --text-color: #333;
            --border-color: #dee2e6;
        }

        .theme-dark {
            --primary-color: #198754;
            --background-color: #1a1a1a;
            --card-background: #2d2d2d;
            --text-color: #ffffff;
            --border-color: #444;
        }

        /* Common Theme Styles */
        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .sidebar {
            background: var(--card-background);
            color: var(--text-color);
        }

        .content {
            background: var(--background-color);
        }

        .settings-card {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .form-control {
            background-color: var(--card-background);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #157347;
            border-color: #157347;
        }

        /* Theme-specific styles */
        .theme-light .form-control:focus {
            color: #495057;
        }

        .theme-dark .form-control:focus {
            color: #ffffff;
        }
    ";
}

// Add theme styles to head
function add_theme_styles() {
    echo "<style>" . get_theme_styles() . "</style>";
}

// Add theme class to body
function add_theme_class() {
    echo "class='" . get_theme_class() . "'";
}
?>
