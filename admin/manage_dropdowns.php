<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
  header("location: login.php");
  exit;
}
// Add this near the top of your file, after the session check and before any other functions
function getConnection() {
  $host = "localhost";
  $username = "root";  // Your database username
  $password = "";      // Your database password
  $database = "centralautogy";  // Your database name
  
  $conn = new mysqli($host, $username, $password, $database);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  return $conn;
}
// Include the database functions
require_once '../config/db.php';

// Get dropdown types for the tabs
$dropdownTypes = [
  'makes' => 'Makes',
  'models' => 'Models',
  'body_types' => 'Body Types',
  'fuel_types' => 'Fuel Types',
  'transmission_types' => 'Transmission Types',
  'drive_types' => 'Drive Types',
  'colors' => 'Colors',
  'vehicle_status' => 'Vehicle Status',
  'features' => 'Features'
];

// Set default active tab
$activeTab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $dropdownTypes) 
  ? $_GET['tab'] 
  : 'makes';

// Handle form submissions
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $conn = getConnection();
  
  // Handle add new item
  if (isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $table = $_POST['table'];
    $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    
    // Additional fields for specific tables
    $additionalFields = [];
    if ($table === 'models') {
      $additionalFields['make_id'] = (int)$_POST['make_id'];
    } elseif ($table === 'colors') {
      $additionalFields['hex_code'] = trim($_POST['hex_code']);
    } elseif ($table === 'features') {
      $additionalFields['category'] = trim($_POST['category']);
    } elseif ($table === 'vehicle_status') {
      $additionalFields['css_class'] = trim($_POST['css_class']);
    }
    
    if (empty($name)) {
      $errorMessage = "Name is required";
    } else {
      // Prepare the basic query
      $fields = ['name', 'display_order'];
      $placeholders = ['?', '?'];
      $types = 'si'; // string, integer
      $values = [$name, $displayOrder];
      
      // Add additional fields if needed
      foreach ($additionalFields as $field => $value) {
        $fields[] = $field;
        $placeholders[] = '?';
        if (is_int($value)) {
          $types .= 'i';
        } else {
          $types .= 's';
        }
        $values[] = $value;
      }
      
      // Build the query
      $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") 
              VALUES (" . implode(', ', $placeholders) . ")";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param($types, ...$values);
      
      if ($stmt->execute()) {
        $successMessage = "New item added successfully!";
      } else {
        $errorMessage = "Error adding item: " . $conn->error;
      }
      
      $stmt->close();
    }
  }
  
  // Handle delete item
  elseif (isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    $table = $_POST['table'];
    
    // Check for dependencies before deletion
    $canDelete = true;
    $dependencyError = "";
    
    if ($table === 'makes') {
      // Check if make has models
      $checkSql = "SELECT COUNT(*) AS count FROM models WHERE make_id = ?";
      $checkStmt = $conn->prepare($checkSql);
      $checkStmt->bind_param("i", $id);
      $checkStmt->execute();
      $result = $checkStmt->get_result();
      $count = $result->fetch_assoc()['count'];
      $checkStmt->close();
      
      if ($count > 0) {
        $canDelete = false;
        $dependencyError = "Cannot delete this make because it has $count models associated with it. Please delete those models first.";
      }
    }
    
    if ($canDelete) {
      $sql = "DELETE FROM $table WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      
      if ($stmt->execute()) {
        $successMessage = "Item deleted successfully!";
      } else {
        $errorMessage = "Error deleting item: " . $conn->error;
      }
      
      $stmt->close();
    } else {
      $errorMessage = $dependencyError;
    }
  }
  
  // Handle edit item
  elseif (isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $table = $_POST['table'];
    $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    
    // Additional fields for specific tables
    $updateFields = ['name = ?', 'display_order = ?'];
    $types = 'si'; // string, integer
    $values = [$name, $displayOrder];
    
    if ($table === 'models') {
      $makeId = (int)$_POST['make_id'];
      $updateFields[] = 'make_id = ?';
      $types .= 'i';
      $values[] = $makeId;
    } elseif ($table === 'colors') {
      $hexCode = trim($_POST['hex_code']);
      $updateFields[] = 'hex_code = ?';
      $types .= 's';
      $values[] = $hexCode;
    } elseif ($table === 'features') {
      $category = trim($_POST['category']);
      $updateFields[] = 'category = ?';
      $types .= 's';
      $values[] = $category;
    } elseif ($table === 'vehicle_status') {
      $cssClass = trim($_POST['css_class']);
      $updateFields[] = 'css_class = ?';
      $types .= 's';
      $values[] = $cssClass;
    }
    
    // Add ID for WHERE clause
    $types .= 'i';
    $values[] = $id;
    
    if (empty($name)) {
      $errorMessage = "Name is required";
    } else {
      $sql = "UPDATE $table SET " . implode(', ', $updateFields) . " WHERE id = ?";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param($types, ...$values);
      
      if ($stmt->execute()) {
        $successMessage = "Item updated successfully!";
      } else {
        $errorMessage = "Error updating item: " . $conn->error;
      }
      
      $stmt->close();
    }
  }
  
  $conn->close();
}

// Get items for the active tab
function getDropdownItems($table) {
  $conn = getConnection();
  $items = [];
  
  if ($table === 'models') {
    // For models, join with makes table
    $sql = "SELECT m.*, mk.name as make_name 
            FROM models m 
            JOIN makes mk ON m.make_id = mk.id 
            ORDER BY m.display_order, m.name";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $items[] = $row;
      }
    }
  } else {
    // For other tables
    $sql = "SELECT * FROM $table ORDER BY display_order, name";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $items[] = $row;
      }
    }
  }
  
  $conn->close();
  return $items;
}

// Get all makes for models dropdown
function getAllMakes() {
  $conn = getConnection();
  $makes = [];
  
  $sql = "SELECT id, name FROM makes ORDER BY name";
  $result = $conn->query($sql);
  
  if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $makes[] = $row;
    }
  }
  
  $conn->close();
  return $makes;
}

// Get feature categories
function getFeatureCategories() {
  return ['Safety', 'Comfort', 'Technology', 'Performance', 'Other'];
}

// Get items for the active tab
$items = getDropdownItems($activeTab);

// Get additional data for specific tables
$makes = ($activeTab === 'models') ? getAllMakes() : [];
$featureCategories = ($activeTab === 'features') ? getFeatureCategories() : [];
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Manage Dropdowns - CentralAutogy</title>
  <link rel="stylesheet" href="assets/css/index.css">
  <!-- Add color picker for hex codes -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">
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
        <h1 class="text-2xl font-bold text-gray-800">Manage Dropdown Options</h1>
        <p class="text-gray-600">Add, edit, or delete options that appear in dropdown menus throughout the system</p>
      </div>

      <!-- Success/Error Messages -->
      <?php if (!empty($successMessage)): ?>
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
          <p><?php echo htmlspecialchars($successMessage); ?></p>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($errorMessage)): ?>
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
          <p><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
      <?php endif; ?>

      <!-- Tabs and Content -->
      <div class="bg-white rounded-lg shadow-md">
        <!-- Tabs -->
        <div class="border-b overflow-x-auto">
          <div class="flex">
            <?php foreach ($dropdownTypes as $key => $label): ?>
              <a href="?tab=<?php echo $key; ?>" class="px-4 py-3 text-sm font-medium whitespace-nowrap <?php echo ($key === $activeTab) ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 hover:text-gray-800'; ?>">
                <?php echo htmlspecialchars($label); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Add New Button -->
          <div class="mb-6">
            <button id="addNewBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Add New <?php echo htmlspecialchars($dropdownTypes[$activeTab]); ?>
            </button>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">ID</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Name</th>
                  <?php if ($activeTab === 'models'): ?>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Make</th>
                  <?php endif; ?>
                  <?php if ($activeTab === 'colors'): ?>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Color</th>
                  <?php endif; ?>
                  <?php if ($activeTab === 'features'): ?>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Category</th>
                  <?php endif; ?>
                  <?php if ($activeTab === 'vehicle_status'): ?>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status Style</th>
                  <?php endif; ?>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Display Order</th>
                  <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr class="border-b border-gray-100">
                    <td colspan="<?php echo ($activeTab === 'models' || $activeTab === 'colors' || $activeTab === 'features' || $activeTab === 'vehicle_status') ? '5' : '4'; ?>" class="px-4 py-3 text-center text-gray-500">
                      No items found. Click "Add New" to create one.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($items as $item): ?>
                    <tr class="border-b border-gray-100 table-row">
                      <td class="px-4 py-3 text-gray-800"><?php echo $item['id']; ?></td>
                      <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($item['name']); ?></td>
                      <?php if ($activeTab === 'models'): ?>
                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($item['make_name']); ?></td>
                      <?php endif; ?>
                      <?php if ($activeTab === 'colors'): ?>
                        <td class="px-4 py-3">
                          <div class="flex items-center">
                            <div class="w-6 h-6 rounded mr-2" style="background-color: <?php echo htmlspecialchars($item['hex_code']); ?>"></div>
                            <span><?php echo htmlspecialchars($item['hex_code']); ?></span>
                          </div>
                        </td>
                      <?php endif; ?>
                      <?php if ($activeTab === 'features'): ?>
                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($item['category']); ?></td>
                      <?php endif; ?>
                      <?php if ($activeTab === 'vehicle_status'): ?>
                        <td class="px-4 py-3">
                          <span class="px-3 py-1 <?php echo $item['css_class']; ?> rounded-full text-xs font-medium">
                            Example
                          </span>
                        </td>
                      <?php endif; ?>
                      <td class="px-4 py-3 text-gray-800"><?php echo $item['display_order']; ?></td>
                      <td class="px-4 py-3 text-right">
                        <div class="flex justify-end space-x-2">
                          <button class="edit-btn p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" 
                                  title="Edit" 
                                  data-id="<?php echo $item['id']; ?>"
                                  data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                  data-order="<?php echo $item['display_order']; ?>"
                                  <?php if ($activeTab === 'models'): ?>
                                  data-make-id="<?php echo $item['make_id']; ?>"
                                  <?php endif; ?>
                                  <?php if ($activeTab === 'colors'): ?>
                                  data-hex-code="<?php echo htmlspecialchars($item['hex_code']); ?>"
                                  <?php endif; ?>
                                  <?php if ($activeTab === 'features'): ?>
                                  data-category="<?php echo htmlspecialchars($item['category']); ?>"
                                  <?php endif; ?>
                                  <?php if ($activeTab === 'vehicle_status'): ?>
                                  data-css-class="<?php echo htmlspecialchars($item['css_class']); ?>"
                                  <?php endif; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                          </button>
                          <button class="delete-btn p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" 
                                  title="Delete"
                                  data-id="<?php echo $item['id']; ?>"
                                  data-name="<?php echo htmlspecialchars($item['name']); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Add New Modal -->
  <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl modal-animation">
        <div class="flex justify-between items-center border-b p-6">
          <h3 class="text-xl font-bold text-gray-800">Add New <?php echo htmlspecialchars($dropdownTypes[$activeTab]); ?></h3>
          <button id="closeAddModalBtn" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-all p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form method="post" class="p-6 space-y-4">
          <input type="hidden" name="table" value="<?php echo $activeTab; ?>">
          
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
          </div>
          
          <?php if ($activeTab === 'models'): ?>
            <div>
              <label for="make_id" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
              <select id="make_id" name="make_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Make</option>
                <?php foreach ($makes as $make): ?>
                  <option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'colors'): ?>
            <div>
              <label for="hex_code" class="block text-sm font-medium text-gray-700 mb-1">Color Code</label>
              <div class="flex">
                <input type="text" id="hex_code" name="hex_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm" placeholder="#000000">
                <div id="color-picker" class="ml-2"></div>
              </div>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'features'): ?>
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select id="category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <?php foreach ($featureCategories as $category): ?>
                  <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'vehicle_status'): ?>
            <div>
              <label for="css_class" class="block text-sm font-medium text-gray-700 mb-1">CSS Class</label>
              <input type="text" id="css_class" name="css_class" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm" placeholder="bg-green-100 text-green-800">
              <div class="mt-2">
                <span class="px-3 py-1 rounded-full text-xs font-medium preview-span">Preview</span>
              </div>
            </div>
          <?php endif; ?>
          
          <div>
            <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input type="number" id="display_order" name="display_order" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            <p class="text-xs text-gray-500 mt-1">Lower numbers display first. Items with the same order are sorted alphabetically.</p>
          </div>
          
          <div class="pt-4">
            <button type="submit" name="add_item" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-2.5 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-md">
              Add Item
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl modal-animation">
        <div class="flex justify-between items-center border-b p-6">
          <h3 class="text-xl font-bold text-gray-800">Edit <?php echo htmlspecialchars($dropdownTypes[$activeTab]); ?></h3>
          <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-all p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form method="post" class="p-6 space-y-4">
          <input type="hidden" name="table" value="<?php echo $activeTab; ?>">
          <input type="hidden" id="edit_id" name="id" value="">
          
          <div>
            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
          </div>
          
          <?php if ($activeTab === 'models'): ?>
            <div>
              <label for="edit_make_id" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
              <select id="edit_make_id" name="make_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Make</option>
                <?php foreach ($makes as $make): ?>
                  <option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'colors'): ?>
            <div>
              <label for="edit_hex_code" class="block text-sm font-medium text-gray-700 mb-1">Color Code</label>
              <div class="flex">
                <input type="text" id="edit_hex_code" name="hex_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm" placeholder="#000000">
                <div id="edit-color-picker" class="ml-2"></div>
              </div>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'features'): ?>
            <div>
              <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select id="edit_category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <?php foreach ($featureCategories as $category): ?>
                  <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          
          <?php if ($activeTab === 'vehicle_status'): ?>
            <div>
              <label for="edit_css_class" class="block text-sm font-medium text-gray-700 mb-1">CSS Class</label>
              <input type="text" id="edit_css_class" name="css_class" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm" placeholder="bg-green-100 text-green-800">
              <div class="mt-2">
                <span class="px-3 py-1 rounded-full text-xs font-medium edit-preview-span">Preview</span>
              </div>
            </div>
          <?php endif; ?>
          
          <div>
            <label for="edit_display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input type="number" id="edit_display_order" name="display_order" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            <p class="text-xs text-gray-500 mt-1">Lower numbers display first. Items with the same order are sorted alphabetically.</p>
          </div>
          
          <div class="pt-4">
            <button type="submit" name="edit_item" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-2.5 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-md">
              Update Item
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl modal-animation">
        <div class="p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-4">Confirm Deletion</h3>
          <p class="text-gray-600 mb-6">Are you sure you want to delete "<span id="delete-item-name"></span>"? This action cannot be undone.</p>
          
          <form method="post" class="flex space-x-3">
            <input type="hidden" name="table" value="<?php echo $activeTab; ?>">
            <input type="hidden" id="delete_id" name="id" value="">
            
            <button type="button" id="cancelDeleteBtn" class="flex-1 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm focus:outline-none">
              Cancel
            </button>
            <button type="submit" name="delete_item" class="flex-1 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 font-medium text-sm focus:outline-none">
              Delete
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add Modal
      const addModal = document.getElementById('addModal');
      const addNewBtn = document.getElementById('addNewBtn');
      const closeAddModalBtn = document.getElementById('closeAddModalBtn');

      // Edit Modal
      const editModal = document.getElementById('editModal');
      const closeEditModalBtn = document.getElementById('closeEditModalBtn');
      const editBtns = document.querySelectorAll('.edit-btn');
      
      // Delete Modal
      const deleteModal = document.getElementById('deleteModal');
      const deleteBtns = document.querySelectorAll('.delete-btn');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

      // Add Modal Controls
      if (addModal && addNewBtn && closeAddModalBtn) {
        addNewBtn.addEventListener('click', function() {
          addModal.classList.remove('hidden');
        });
        
        closeAddModalBtn.addEventListener('click', function() {
          addModal.classList.add('hidden');
        });
      }

      // Edit Modal Controls
      if (editModal && closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', function() {
          editModal.classList.add('hidden');
        });
        
        editBtns.forEach(btn => {
          btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const order = this.getAttribute('data-order');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_display_order').value = order;
            
            // Handle specific fields for different tabs
            <?php if ($activeTab === 'models'): ?>
            const makeId = this.getAttribute('data-make-id');
            document.getElementById('edit_make_id').value = makeId;
            <?php endif; ?>
            
            <?php if ($activeTab === 'colors'): ?>
            const hexCode = this.getAttribute('data-hex-code');
            document.getElementById('edit_hex_code').value = hexCode;
            updateEditColorPicker(hexCode);
            <?php endif; ?>
            
            <?php if ($activeTab === 'features'): ?>
            const category = this.getAttribute('data-category');
            document.getElementById('edit_category').value = category;
            <?php endif; ?>
            
            <?php if ($activeTab === 'vehicle_status'): ?>
            const cssClass = this.getAttribute('data-css-class');
            document.getElementById('edit_css_class').value = cssClass;
            updateEditCssPreview(cssClass);
            <?php endif; ?>
            
            editModal.classList.remove('hidden');
          });
        });
      }

      // Delete Modal Controls
      if (deleteModal && cancelDeleteBtn) {
        deleteBtns.forEach(btn => {
          btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('delete-item-name').textContent = name;
            
            deleteModal.classList.remove('hidden');
          });
        });
        
        cancelDeleteBtn.addEventListener('click', function() {
          deleteModal.classList.add('hidden');
        });
      }
      
      <?php if ($activeTab === 'colors'): ?>
      // Initialize color picker for add modal
      const pickr = Pickr.create({
        el: '#color-picker',
        theme: 'classic',
        default: '#000000',
        components: {
          preview: true,
          opacity: true,
          hue: true,
          interaction: {
            hex: true,
            rgba: true,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            save: true
          }
        }
      });
      
      pickr.on('save', (color, instance) => {
        const hexValue = color.toHEXA().toString();
        document.getElementById('hex_code').value = hexValue;
        instance.hide();
      });
      
      // Initialize color picker for edit modal
      const editPickr = Pickr.create({
        el: '#edit-color-picker',
        theme: 'classic',
        default: '#000000',
        components: {
          preview: true,
          opacity: true,
          hue: true,
          interaction: {
            hex: true,
            rgba: true,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            save: true
          }
        }
      });
      
      editPickr.on('save', (color, instance) => {
        const hexValue = color.toHEXA().toString();
        document.getElementById('edit_hex_code').value = hexValue;
        instance.hide();
      });
      
      function updateEditColorPicker(hexCode) {
        editPickr.setColor(hexCode);
      }
      <?php endif; ?>
      
      <?php if ($activeTab === 'vehicle_status'): ?>
      // CSS Class preview for add modal
      const cssClassInput = document.getElementById('css_class');
      const previewSpan = document.querySelector('.preview-span');
      
      if (cssClassInput && previewSpan) {
        cssClassInput.addEventListener('input', function() {
          updateCssPreview(this.value);
        });
        
        function updateCssPreview(cssClass) {
          // Remove all existing classes
          previewSpan.className = '';
          // Add the base classes
          previewSpan.classList.add('px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium');
          // Add the user-entered classes
          const classes = cssClass.trim().split(/\s+/);
          classes.forEach(className => {
            if (className) {
              previewSpan.classList.add(className);
            }
          });
        }
      }
      
      // CSS Class preview for edit modal
      const editCssClassInput = document.getElementById('edit_css_class');
      const editPreviewSpan = document.querySelector('.edit-preview-span');
      
      if (editCssClassInput && editPreviewSpan) {
        editCssClassInput.addEventListener('input', function() {
          updateEditCssPreview(this.value);
        });
        
        function updateEditCssPreview(cssClass) {
          // Remove all existing classes
          editPreviewSpan.className = '';
          // Add the base classes
          editPreviewSpan.classList.add('px-3', 'py-1', 'rounded-full', 'text-xs', 'font-medium');
          // Add the user-entered classes
          const classes = cssClass.trim().split(/\s+/);
          classes.forEach(className => {
            if (className) {
              editPreviewSpan.classList.add(className);
            }
          });
        }
      }
      <?php endif; ?>
    });
  </script>
  <script src="assets/js/index.js"></script>
</body>

</html>