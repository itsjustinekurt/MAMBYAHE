<?php
// Language translations
$translations = [
    'en' => [
        'settings' => 'Settings',
        'account_settings' => 'Account Settings',
        'username' => 'Username',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_new_password' => 'Confirm New Password',
        'update_account' => 'Update Account',
        'notification_settings' => 'Notification Settings',
        'email_notifications' => 'Email Notifications',
        'sms_notifications' => 'SMS Notifications',
        'ride_requests' => 'Ride Requests',
        'ride_updates' => 'Ride Updates',
        'save_preferences' => 'Save Preferences',
        'privacy_settings' => 'Privacy Settings',
        'show_profile' => 'Show Profile to Passengers',
        'share_location' => 'Share Location with Passengers',
        'show_rating' => 'Show Rating to Passengers',
        'save_privacy_settings' => 'Save Privacy Settings',
        'app_settings' => 'App Settings',
        'language' => 'Language',
        'theme' => 'Theme',
        'distance_unit' => 'Distance Unit',
        'save_app_settings' => 'Save App Settings',
        'success' => 'Success',
        'error' => 'Error',
        'required' => 'is required',
        'at_least_8_chars' => 'must be at least 8 characters long',
        'passwords_do_not_match' => 'New passwords do not match',
        'current_password_required' => 'Current password is required when changing password',
        'incorrect_password' => 'Current password is incorrect',
        'settings_updated' => 'Settings updated successfully',
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System Default',
        'km' => 'Kilometers',
        'mi' => 'Miles'
    ],
    'tl' => [
        'settings' => 'Mga Setting',
        'account_settings' => 'Mga Setting ng Account',
        'username' => 'Username',
        'current_password' => 'Kasalukuyang Password',
        'new_password' => 'Bagong Password',
        'confirm_new_password' => 'Kumpirma ang Bagong Password',
        'update_account' => 'I-update ang Account',
        'notification_settings' => 'Mga Setting ng Notification',
        'email_notifications' => 'Email Notifications',
        'sms_notifications' => 'SMS Notifications',
        'ride_requests' => 'Mga Request ng Serye',
        'ride_updates' => 'Mga Update ng Serye',
        'save_preferences' => 'I-save ang Preferences',
        'privacy_settings' => 'Mga Setting ng Privacy',
        'show_profile' => 'Ipakita ang Profile sa mga Pasahero',
        'share_location' => 'Ibahagi ang Lokasyon sa mga Pasahero',
        'show_rating' => 'Ipakita ang Rating sa mga Pasahero',
        'save_privacy_settings' => 'I-save ang Mga Setting ng Privacy',
        'app_settings' => 'Mga Setting ng App',
        'language' => 'Wika',
        'theme' => 'Tema',
        'distance_unit' => 'Unit ng Layo',
        'save_app_settings' => 'I-save ang Mga Setting ng App',
        'success' => 'Tagumpay',
        'error' => 'Error',
        'required' => 'ay kinakailangan',
        'at_least_8_chars' => 'ay dapat na may 8 o higit pang character',
        'passwords_do_not_match' => 'Hindi tumutugon ang bagong password',
        'current_password_required' => 'Kinakailangan ang kasalukuyang password kapag pagbabago ng password',
        'incorrect_password' => 'Mali ang kasalukuyang password',
        'settings_updated' => 'Nagtagumpay na na-update ang mga setting',
        'light' => 'Liwanag',
        'dark' => 'Madilim',
        'system' => 'Default ng System',
        'km' => 'Kilometro',
        'mi' => 'Milya'
    ]
];

// Get current language from session or default to English
$current_language = $_SESSION['language'] ?? 'en';

// Function to get translation
function __($key, $language = null) {
    global $translations, $current_language;
    $language = $language ?? $current_language;
    return $translations[$language][$key] ?? $key;
}

// Function to get all translations for a language
function get_translations($language = null) {
    global $translations, $current_language;
    $language = $language ?? $current_language;
    return $translations[$language] ?? $translations['en'];
}
