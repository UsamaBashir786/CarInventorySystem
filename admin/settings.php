<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
  header("location: login.php");
  exit;
}

// Include database connection
require_once '../config/db.php';

// Function to get database connection if not already defined
if (!function_exists('getConnection')) {
  function getConnection()
  {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "centralautogy";

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
  }
}

// Get connection
$conn = getConnection();

// Function to get current settings from database
function getSettings($conn)
{
  // Check if settings table exists
  $tableCheck = $conn->query("SHOW TABLES LIKE 'site_settings'");

  if ($tableCheck->num_rows == 0) {
    // Create settings table if it doesn't exist
    $createTable = "CREATE TABLE site_settings (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      setting_key VARCHAR(100) NOT NULL UNIQUE,
      setting_value TEXT,
      setting_group VARCHAR(50) DEFAULT 'general',
      setting_type VARCHAR(20) DEFAULT 'text',
      display_name VARCHAR(100) NOT NULL,
      description TEXT DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $conn->query($createTable);

    // Insert default settings
    $defaultSettings = [
      ['site_name', 'CentralAutogy', 'General', 'text', 'Website Name', 'The name of the website'],
      ['site_tagline', 'Your one-stop destination for finding the perfect vehicle', 'General', 'text', 'Website Tagline', 'A short description of the website'],
      ['favicon_path', '', 'Assets', 'file', 'Favicon', 'Website favicon'],
      ['navbar_logo_path', '', 'Assets', 'file', 'Navbar Logo', 'Logo displayed in the navigation bar'],
      ['footer_logo_path', '', 'Assets', 'file', 'Footer Logo', 'Logo displayed in the footer']
    ];

    $insertStmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group, setting_type, display_name, description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
      $insertStmt->bind_param("ssssss", $setting[0], $setting[1], $setting[2], $setting[3], $setting[4], $setting[5]);
      $insertStmt->execute();
    }
    $insertStmt->close();
  }

  // Check if site_assets table exists
  $assetTableCheck = $conn->query("SHOW TABLES LIKE 'site_assets'");

  if ($assetTableCheck->num_rows == 0) {
    // Create site_assets table if it doesn't exist
    $createAssetTable = "CREATE TABLE site_assets (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      asset_key VARCHAR(100) NOT NULL UNIQUE,
      asset_path VARCHAR(255) NOT NULL,
      asset_type VARCHAR(20) DEFAULT 'image',
      mime_type VARCHAR(100) DEFAULT NULL,
      original_filename VARCHAR(255) DEFAULT NULL,
      display_name VARCHAR(100) NOT NULL,
      description TEXT DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $conn->query($createAssetTable);
  }

  // Get all settings
  $settings = [];
  $query = "SELECT setting_key, setting_value FROM site_settings";
  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $settings[$row['setting_key']] = $row['setting_value'];
    }
  }

  return $settings;
}

// Function to update settings
function updateSetting($conn, $settingName, $settingValue)
{
  $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
  $stmt->bind_param("ss", $settingValue, $settingName);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function handleFileUpload($file, $targetDir, $assetKey = null)
{
  global $conn; // Make sure to use the global database connection

  // Check if directory exists, create if not
  if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
  }

  $fileName = basename($file["name"]);
  $targetFile = $targetDir . $fileName;
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  // Check if file is an actual image
  $check = getimagesize($file["tmp_name"]);
  if ($check === false) {
    return ["success" => false, "message" => "File is not an image."];
  }

  // Check file size (5MB limit)
  if ($file["size"] > 5000000) {
    return ["success" => false, "message" => "File is too large. Maximum size is 5MB."];
  }

  // Allow only certain file formats
  if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "svg" && $imageFileType != "ico") {
    return ["success" => false, "message" => "Only JPG, JPEG, PNG, GIF, SVG & ICO files are allowed."];
  }

  // Generate a unique filename to avoid overwriting
  $newFileName = uniqid() . "." . $imageFileType;
  $targetFile = $targetDir . $newFileName;
  $relativePath = substr($targetFile, 3); // Remove '../' from the path

  // Try to upload file
  if (move_uploaded_file($file["tmp_name"], $targetFile)) {
    // If an asset key is provided, save to site_assets table
    if ($assetKey !== null) {
      try {
        // Prepare variables
        $assetType = 'image';
        $mimeType = mime_content_type($targetFile);

        // Modify the SQL to match the table structure
        $stmt = $conn->prepare("INSERT INTO site_assets 
          (asset_key, asset_path, asset_type, mime_type, original_filename, display_name, description) 
          VALUES (?, ?, ?, ?, ?, ?, '')
          ON DUPLICATE KEY UPDATE 
          asset_path = ?, 
          mime_type = ?, 
          original_filename = ?, 
          updated_at = CURRENT_TIMESTAMP");

        // Ensure all variables are prepared before binding
        $stmt->bind_param(
          "sssssssss",
          $assetKey,      // 1. asset_key
          $relativePath,  // 2. asset_path
          $assetType,     // 3. asset_type
          $mimeType,      // 4. mime_type
          $fileName,      // 5. original_filename
          $assetKey,      // 6. display_name
          $relativePath,  // 7. asset_path (for update)
          $mimeType,      // 8. mime_type (for update)
          $fileName       // 9. original_filename (for update)
        );

        if (!$stmt->execute()) {
          // If database insertion fails, you might want to delete the uploaded file
          unlink($targetFile);
          return ["success" => false, "message" => "Failed to save asset to database: " . $stmt->error];
        }
        $stmt->close();
      } catch (Exception $e) {
        // Log the full error for debugging
        error_log("Asset save error: " . $e->getMessage());
        return ["success" => false, "message" => "Database error: " . $e->getMessage()];
      }
    }

    return ["success" => true, "path" => $relativePath, "message" => "File uploaded successfully."];
  } else {
    return ["success" => false, "message" => "Failed to upload file."];
  }
}
// Initialize variables
$settings = getSettings($conn);
$success_message = "";
$error_message = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Update basic settings
  if (isset($_POST['update_basic_settings'])) {
    $siteName = trim($_POST['site_name']);
    $siteTagline = trim($_POST['site_tagline']);

    // Update site name
    if (updateSetting($conn, 'site_name', $siteName)) {
      $settings['site_name'] = $siteName;
    }

    // Update site tagline
    if (updateSetting($conn, 'site_tagline', $siteTagline)) {
      $settings['site_tagline'] = $siteTagline;
    }

    $success_message = "Basic settings updated successfully.";
  }

  // Handle favicon upload
  if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
    $result = handleFileUpload($_FILES['favicon'], "../assets/images/", "favicon");

    if ($result['success']) {
      if (updateSetting($conn, 'favicon_path', $result['path'])) {
        $settings['favicon_path'] = $result['path'];
        $success_message = "Favicon uploaded and updated successfully.";
      } else {
        $error_message = "Failed to update favicon in database.";
      }
    } else {
      $error_message = $result['message'];
    }
  }

  // Handle navbar logo upload
  if (isset($_FILES['navbar_logo']) && $_FILES['navbar_logo']['error'] == 0) {
    $result = handleFileUpload($_FILES['navbar_logo'], "../assets/images/logos/", "navbar_logo");

    if ($result['success']) {
      if (updateSetting($conn, 'navbar_logo_path', $result['path'])) {
        $settings['navbar_logo_path'] = $result['path'];
        $success_message = "Navbar logo uploaded and updated successfully.";
      } else {
        $error_message = "Failed to update navbar logo in database.";
      }
    } else {
      $error_message = $result['message'];
    }
  }

  // Handle footer logo upload
  if (isset($_FILES['footer_logo']) && $_FILES['footer_logo']['error'] == 0) {
    $result = handleFileUpload($_FILES['footer_logo'], "../assets/images/logos/", "footer_logo");

    if ($result['success']) {
      if (updateSetting($conn, 'footer_logo_path', $result['path'])) {
        $settings['footer_logo_path'] = $result['path'];
        $success_message = "Footer logo uploaded and updated successfully.";
      } else {
        $error_message = "Failed to update footer logo in database.";
      }
    } else {
      $error_message = $result['message'];
    }
  }
}

// Close connection
$conn->close();
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Website Settings - CentralAutogy Admin</title>
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body class="bg-gray-50">
  <?php include 'includes/header.php'; ?>
  <div class="flex h-[calc(100vh-64px)]">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-y-auto bg-gray-50">
      <!-- Mobile menu button -->
      <div class="md:hidden mb-6">
        <button id="mobileMenuBtn" class="flex items-center justify-center bg-white shadow-md rounded-lg p-2 w-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <span class="ml-2 text-indigo-600 font-medium">Menu</span>
        </button>
      </div>

      <!-- Page Title -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Website Settings</h1>
        <p class="text-gray-600">Manage your website's basic settings and appearance</p>
      </div>

      <!-- Flash Messages -->
      <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $success_message; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rulefill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $error_message; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Settings Tabs -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="border-b">
          <nav class="flex overflow-x-auto" aria-label="Tabs">
            <button class="tab-btn active px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 border-indigo-500 text-indigo-600" data-tab="general">
              General Settings
            </button>
            <button class="tab-btn px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="logos">
              Logos & Favicon
            </button>
          </nav>
        </div>

        <!-- General Settings Tab -->
        <div class="tab-content active p-6" id="general-tab">
          <form method="post" action="">
            <div class="space-y-6">
              <div>
                <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">Website Name</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'CentralAutogy'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <p class="text-xs text-gray-500 mt-1">This will be displayed in the browser title bar and throughout the website</p>
              </div>

              <div>
                <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-1">Website Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? 'Your one-stop destination for finding the perfect vehicle'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <p class="text-xs text-gray-500 mt-1">A short description of your business that appears in the footer</p>
              </div>

              <div class="pt-4">
                <button type="submit" name="update_basic_settings" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-sm">
                  Save General Settings
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Logos & Favicon Tab -->
        <div class="tab-content hidden p-6" id="logos-tab">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Favicon Upload -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Website Favicon</h3>
              <p class="text-sm text-gray-600 mb-4">Upload a favicon (.ico, .png) for your website. This will appear in browser tabs.</p>

              <?php if (!empty($settings['favicon_path'])): ?>
                <div class="mb-4">
                  <p class="text-sm text-gray-600 mb-2">Current Favicon:</p>
                  <img src="<?php echo '../' . htmlspecialchars($settings['favicon_path']); ?>" alt="Current Favicon" class="h-10 w-10 object-contain border rounded">
                </div>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="file-drop-area">
                  <input type="file" id="favicon" name="favicon" class="file-input" accept=".ico,.png,.jpg,.jpeg">
                  <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Click to upload favicon</p>
                    <p class="text-xs text-gray-500">Recommended: 32x32 pixels</p>
                  </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-sm">
                  Update Favicon
                </button>
              </form>
            </div>

            <!-- Navbar Logo Upload -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Navbar Logo</h3>
              <p class="text-sm text-gray-600 mb-4">Upload a logo for your website's navigation bar. This will appear at the top of every page.</p>

              <?php if (!empty($settings['navbar_logo_path'])): ?>
                <div class="mb-4">
                  <p class="text-sm text-gray-600 mb-2">Current Navbar Logo:</p>
                  <img src="<?php echo '../' . htmlspecialchars($settings['navbar_logo_path']); ?>" alt="Current Navbar Logo" class="h-10 object-contain border rounded">
                </div>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="file-drop-area">
                  <input type="file" id="navbar_logo" name="navbar_logo" class="file-input" accept=".png,.jpg,.jpeg,.svg">
                  <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Click to upload logo</p>
                    <p class="text-xs text-gray-500">Recommended: 180x50 pixels</p>
                  </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-sm">
                  Update Navbar Logo
                </button>
              </form>
            </div>

            <!-- Footer Logo Upload -->
            <div class="bg-gray-50 p-5 rounded-lg space-y-4">
              <h3 class="text-lg font-bold text-gray-800 mb-2">Footer Logo</h3>
              <p class="text-sm text-gray-600 mb-4">Upload a logo for your website's footer. This will appear at the bottom of every page.</p>

              <?php if (!empty($settings['footer_logo_path'])): ?>
                <div class="mb-4">
                  <p class="text-sm text-gray-600 mb-2">Current Footer Logo:</p>
                  <img src="<?php echo '../' . htmlspecialchars($settings['footer_logo_path']); ?>" alt="Current Footer Logo" class="h-10 object-contain border rounded">
                </div>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="file-drop-area">
                  <input type="file" id="footer_logo" name="footer_logo" class="file-input" accept=".png,.jpg,.jpeg,.svg">
                  <div class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Click to upload logo</p>
                    <p class="text-xs text-gray-500">Recommended: 180x50 pixels</p>
                  </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-sm">
                  Update Footer Logo
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>


    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Tab functionality
      const tabButtons = document.querySelectorAll('.tab-btn');
      const tabContents = document.querySelectorAll('.tab-content');

      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all tab buttons
          tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('border-indigo-500');
            btn.classList.remove('text-indigo-600');
            btn.classList.add('border-transparent');
            btn.classList.add('text-gray-500');
          });

          // Add active class to clicked button
          this.classList.add('active');
          this.classList.add('border-indigo-500');
          this.classList.add('text-indigo-600');
          this.classList.remove('border-transparent');
          this.classList.remove('text-gray-500');

          // Hide all tab contents
          tabContents.forEach(content => {
            content.classList.add('hidden');
            content.classList.remove('active');
          });

          // Show the corresponding tab content
          const tabId = this.getAttribute('data-tab');
          const tabContent = document.getElementById(`${tabId}-tab`);
          tabContent.classList.remove('hidden');
          tabContent.classList.add('active');
        });
      });

      // File input display filename
      const fileInputs = document.querySelectorAll('.file-input');
      fileInputs.forEach(input => {
        input.addEventListener('change', function() {
          const fileName = this.files[0]?.name || 'No file selected';
          const fileArea = this.closest('.file-drop-area');
          const fileNameDisplay = fileArea.querySelector('p.text-sm');
          if (fileNameDisplay) {
            if (this.files[0]) {
              fileNameDisplay.textContent = fileName;
            } else {
              fileNameDisplay.textContent = 'Click to upload logo';
            }
          }
        });
      });

      // Close flash messages
      const closeFlashButtons = document.querySelectorAll('.close-flash');
      closeFlashButtons.forEach(button => {
        button.addEventListener('click', function() {
          const flashMessage = this.closest('[role="alert"]');
          if (flashMessage) {
            flashMessage.style.opacity = '0';
            setTimeout(() => {
              flashMessage.style.display = 'none';
            }, 300);
          }
        });
      });

      // Drag and drop functionality for file uploads
      const fileDropAreas = document.querySelectorAll('.file-drop-area');
      fileDropAreas.forEach(dropArea => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
          e.preventDefault();
          e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
          dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
          dropArea.classList.add('border-indigo-500');
          dropArea.classList.add('bg-indigo-50');
        }

        function unhighlight() {
          dropArea.classList.remove('border-indigo-500');
          dropArea.classList.remove('bg-indigo-50');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
          const dt = e.dataTransfer;
          const files = dt.files;
          const fileInput = dropArea.querySelector('.file-input');
          if (fileInput && files.length > 0) {
            fileInput.files = files;

            // Manually trigger change event
            const event = new Event('change', {
              bubbles: true
            });
            fileInput.dispatchEvent(event);
          }
        }
      });
    });
  </script>
</body>

</html>