<?php

/**
 * Helper functions for site settings and assets
 */

// Function to get a site setting value
function get_setting($key, $default = '')
{
  global $conn;

  // If connection is not initialized, create it
  if (!isset($conn) || !$conn) {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
      return $default;
    }
  }

  // Prepare the query to get the setting
  $query = "SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1";
  $stmt = $conn->prepare($query);

  if (!$stmt) {
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
    if (strpos($value, '[year]') !== false) {
      $value = str_replace('[year]', date('Y'), $value);
    }

    return $value;
  }

  $stmt->close();
  return $default;
}

// Function to get an asset URL
function get_asset_url($key, $default = '')
{
  global $conn;

  // If connection is not initialized, create it
  if (!isset($conn) || !$conn) {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
      return $default;
    }
  }

  // Prepare the query to get the asset path
  $query = "SELECT asset_path FROM site_assets WHERE asset_key = ? LIMIT 1";
  $stmt = $conn->prepare($query);

  if (!$stmt) {
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

// Function to get an asset as an HTML image tag
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
    $attr_str .= ' ' . $attr . '="' . htmlspecialchars($value) . '"';
  }

  return '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '"' .
    ($class ? ' class="' . htmlspecialchars($class) . '"' : '') . $attr_str . '>';
}

// Function to render the logo, with SVG fallback if asset is not found
function render_logo($class = '', $color = 'indigo')
{
  $logo_path = get_asset_url('site_logo');

  if (!empty($logo_path)) {
    return '<img src="' . htmlspecialchars($logo_path) . '" alt="Site Logo" class="' . $class . '">';
  } else {
    // Fallback to SVG
    return '
        <svg xmlns="http://www.w3.org/2000/svg" class="' . $class . ' text-' . $color . '-600" viewBox="0 0 20 20" fill="currentColor">
          <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
          <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
        </svg>';
  }
}

// Function to get all settings as an associative array
function get_all_settings()
{
  global $conn;

  // If connection is not initialized, create it
  if (!isset($conn) || !$conn) {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
      return [];
    }
  }

  $query = "SELECT setting_key, setting_value FROM site_settings";
  $result = $conn->query($query);

  if (!$result) {
    return [];
  }

  $settings = [];
  while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
  }

  return $settings;
}

// Function to get all assets as an associative array
function get_all_assets()
{
  global $conn;

  // If connection is not initialized, create it
  if (!isset($conn) || !$conn) {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
      return [];
    }
  }

  $query = "SELECT asset_key, asset_path FROM site_assets";
  $result = $conn->query($query);

  if (!$result) {
    return [];
  }

  $assets = [];
  while ($row = $result->fetch_assoc()) {
    $assets[$row['asset_key']] = $row['asset_path'];
  }

  return $assets;
}
