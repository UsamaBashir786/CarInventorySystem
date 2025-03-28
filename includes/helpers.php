<?php

/**
 * Helper functions for site settings and assets
 * Works with existing database configuration
 */

/**
 * Establish database connection
 * @return mysqli|false Database connection object or false on failure
 */
function get_db_connection()
{
    static $conn = null;
    
    if ($conn === null) {
        // Use existing connection if available, otherwise create new
        global $conn;
        
        if (!isset($conn) || !$conn || $conn->connect_error) {
            $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            if ($conn->connect_error) {
                error_log('Database connection failed: ' . $conn->connect_error);
                return false;
            }
        }
    }
    
    return $conn;
}

/**
 * Get a site setting value
 * @param string $key Setting key
 * @param string $default Default value if setting not found
 * @return string Setting value
 */
function get_setting($key, $default = '')
{
    $conn = get_db_connection();
    if (!$conn) {
        return $default;
    }

    // Prepare the query to get the setting
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        return $default;
    }

    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $setting = $result->fetch_assoc();
        $value = $setting['setting_value'];
        $stmt->close();

        // Handle special placeholders
        $value = str_replace('[year]', date('Y'), $value);

        return $value;
    }

    $stmt->close();
    return $default;
}

/**
 * Get an asset URL
 * @param string $key Asset key
 * @param string $default Default value if asset not found
 * @return string Asset URL
 */
function get_asset_url($key, $default = '')
{
    $conn = get_db_connection();
    if (!$conn) {
        return $default;
    }

    // Prepare the query to get the asset path
    $stmt = $conn->prepare("SELECT asset_path FROM site_assets WHERE asset_key = ? LIMIT 1");
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        return $default;
    }

    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        $path = $asset['asset_path'];
        $stmt->close();
        return $path;
    }

    $stmt->close();
    return $default;
}

/**
 * Get an asset as an HTML image tag
 * @param string $key Asset key
 * @param string $alt Alt text
 * @param string $class CSS class
 * @param array $attributes Additional HTML attributes
 * @return string HTML img tag
 */
function get_asset_img($key, $alt = '', $class = '', $attributes = [])
{
    $path = get_asset_url($key);

    if (empty($path)) {
        return '';
    }

    // If alt is not provided, use the key as alt text
    if (empty($alt)) {
        $alt = ucwords(str_replace('_', ' ', $key));
    }

    // Build any additional attributes
    $attr_str = '';
    foreach ($attributes as $attr => $value) {
        $attr_str .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($value) . '"';
    }

    return sprintf(
        '<img src="%s" alt="%s"%s%s>',
        htmlspecialchars($path),
        htmlspecialchars($alt),
        $class ? ' class="' . htmlspecialchars($class) . '"' : '',
        $attr_str
    );
}

/**
 * Render the logo with SVG fallback
 * @param string $class CSS class
 * @param string $color Tailwind color class
 * @return string HTML for logo
 */
function render_logo($class = '', $color = 'indigo')
{
    $logo_path = get_asset_url('site_logo');

    if (!empty($logo_path)) {
        return sprintf(
            '<img src="%s" alt="Site Logo" class="%s">',
            htmlspecialchars($logo_path),
            htmlspecialchars($class)
        );
    } else {
        // Fallback to SVG
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s text-%s-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
            </svg>',
            htmlspecialchars($class),
            htmlspecialchars($color)
        );
    }
}

/**
 * Get all site settings
 * @return array Associative array of settings
 */
function get_all_settings()
{
    $conn = get_db_connection();
    if (!$conn) {
        return [];
    }

    $result = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    if (!$result) {
        error_log('Query failed: ' . $conn->error);
        return [];
    }

    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

/**
 * Get all site assets
 * @return array Associative array of assets
 */
function get_all_assets()
{
    $conn = get_db_connection();
    if (!$conn) {
        return [];
    }

    $result = $conn->query("SELECT asset_key, asset_path FROM site_assets");
    if (!$result) {
        error_log('Query failed: ' . $conn->error);
        return [];
    }

    $assets = [];
    while ($row = $result->fetch_assoc()) {
        $assets[$row['asset_key']] = $row['asset_path'];
    }

    return $assets;
}