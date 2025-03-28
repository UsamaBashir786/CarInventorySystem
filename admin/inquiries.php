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

// Get vehicle ID from URL parameter
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no valid ID provided
if ($vehicle_id <= 0) {
  $_SESSION['errors'] = ["Invalid vehicle ID"];
  header("Location: vehicles.php");
  exit;
}

// Get connection
$conn = getConnection();

// Get vehicle details
$query = "SELECT v.*, 
            m.name as make_name,
            vs.name as status_name, 
            vs.css_class as status_class 
          FROM vehicles v
          LEFT JOIN makes m ON v.make = m.name
          LEFT JOIN vehicle_status vs ON v.status = vs.name
          WHERE v.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['errors'] = ["Vehicle not found"];
  header("Location: vehicles.php");
  exit;
}

$vehicle = $result->fetch_assoc();
$stmt->close();

// Get vehicle images
$images_query = "SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, display_order ASC";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bind_param("i", $vehicle_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

$images = [];
while ($image = $images_result->fetch_assoc()) {
  $images[] = $image;
}
$images_stmt->close();

// Get vehicle inquiries
$inquiries_query = "SELECT vi.*, IFNULL(u.first_name, '') AS user_first_name, IFNULL(u.last_name, '') AS user_last_name  
                    FROM vehicle_inquiries vi
                    LEFT JOIN users u ON vi.user_id = u.id
                    WHERE vi.vehicle_id = ?
                    ORDER BY vi.submitted_at DESC";
$inquiries_stmt = $conn->prepare($inquiries_query);
$inquiries_stmt->bind_param("i", $vehicle_id);
$inquiries_stmt->execute();
$inquiries_result = $inquiries_stmt->get_result();

$inquiries = [];
while ($inquiry = $inquiries_result->fetch_assoc()) {
  $inquiries[] = $inquiry;
}
$inquiries_stmt->close();

// Handle status update for inquiries
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_inquiry_status'])) {
  $inquiry_id = intval($_POST['inquiry_id']);
  $new_status = $_POST['new_status'];

  $update_query = "UPDATE vehicle_inquiries SET status = ? WHERE id = ? AND vehicle_id = ?";
  $update_stmt = $conn->prepare($update_query);
  $update_stmt->bind_param("sii", $new_status, $inquiry_id, $vehicle_id);

  if ($update_stmt->execute()) {
    $_SESSION['success'] = "Inquiry status updated successfully.";
    // Refresh the page to show updated status
    header("Location: vehicle_details.php?id=" . $vehicle_id . "#inquiries");
    exit;
  } else {
    $_SESSION['errors'] = ["Failed to update inquiry status: " . $update_stmt->error];
  }
  $update_stmt->close();
}

// Default image if no images found
$defaultImage = "../assets/images/placeholder.jpg";
$primaryImage = $defaultImage;

if (!empty($images)) {
  $primaryImage = "../" . $images[0]['image_path'];
}

// Close the database connection
$conn->close();
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>Vehicle Details - CentralAutogy Admin</title>
  <link rel="stylesheet" href="assets/css/index.css">
  <style>
    .gallery-thumb {
      cursor: pointer;
      transition: all 0.2s;
      opacity: 0.7;
      border: 2px solid transparent;
    }

    .gallery-thumb:hover {
      opacity: 0.9;
    }

    .gallery-thumb.active {
      opacity: 1;
      border-color: #4f46e5;
    }

    .tab-button {
      position: relative;
      transition: all 0.3s;
    }

    .tab-button.active {
      color: #4f46e5;
    }

    .tab-button::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100%;
      height: 3px;
      background-color: #4f46e5;
      transform: scaleX(0);
      transition: transform 0.3s;
    }

    .tab-button.active::after {
      transform: scaleX(1);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .status-badge.New {
      background-color: #e0f2fe;
      color: #0369a1;
    }

    .status-badge.In-Progress {
      background-color: #fef3c7;
      color: #92400e;
    }

    .status-badge.Contacted {
      background-color: #dcfce7;
      color: #166534;
    }

    .status-badge.Closed {
      background-color: #f3f4f6;
      color: #4b5563;
    }
  </style>
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
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Vehicle Details</h1>
          <p class="text-gray-600">View and manage vehicle information</p>
        </div>
        <div>
          <a href="vehicles.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition duration-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Inventory
          </a>
        </div>
      </div>

      <!-- Flash Messages -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow transition-opacity duration-500">
          <div class="flex justify-between items-center">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <p><?php echo $_SESSION['success']; ?></p>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow transition-opacity duration-500">
          <div class="flex justify-between items-center">
            <div>
              <div class="flex items-center mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="font-medium">The following errors occurred:</p>
              </div>
              <ul class="list-disc list-inside pl-7">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                  <li><?php echo $error; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <button class="close-flash">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <?php unset($_SESSION['errors']); ?>
      <?php endif; ?>

      <!-- Car Details Header -->
      <div class="flex flex-col md:flex-row justify-between items-start mb-6 bg-white p-6 rounded-xl shadow-sm">
        <div>
          <div class="mb-1 flex items-center">
            <span class="<?php echo !empty($vehicle['status_class']) ? htmlspecialchars($vehicle['status_class']) : 'bg-green-100 text-green-800'; ?> text-xs px-2 py-1 rounded-full font-medium mr-2">
              <?php echo htmlspecialchars($vehicle['status']); ?>
            </span>
            <?php if (!empty($vehicle['vin'])): ?>
              <span class="text-gray-500 text-sm">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></span>
            <?php endif; ?>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">
            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
          </h1>
          <p class="text-gray-600">
            <?php echo htmlspecialchars($vehicle['body_style'] . ' • ' . $vehicle['exterior_color'] . ' • ' . $vehicle['interior_color'] . ' Interior'); ?>
          </p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="text-2xl md:text-3xl font-bold text-indigo-600">$<?php echo number_format($vehicle['price'], 0); ?></div>
          <div class="flex space-x-2 mt-2">
            <a href="edit_vehicle_form.php?id=<?php echo $vehicle_id; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
              Edit
            </a>
            <button id="statusDropdownBtn" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition duration-300 flex items-center text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
              Change Status
            </button>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="mb-6 bg-white rounded-xl shadow-sm">
        <div class="border-b border-gray-200">
          <div class="flex overflow-x-auto">
            <button class="tab-button active px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="vehicle-info">Vehicle Information</button>
            <button class="tab-button px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="inquiries">Inquiries <span class="ml-1 bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($inquiries); ?></span></button>
            <button class="tab-button px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="images">Images <span class="ml-1 bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded-full"><?php echo count($images); ?></span></button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Vehicle Information Tab -->
          <div id="vehicle-info" class="tab-content active">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <!-- Main Image -->
                <div class="bg-white rounded-xl overflow-hidden mb-4">
                  <div class="relative aspect-video">
                    <img id="mainImage" src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>" class="w-full h-full object-cover">
                  </div>
                </div>

                <!-- Basic Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="font-medium text-gray-800 mb-3">Basic Details</h3>
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Year</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['year']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Make</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['make']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Model</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['model']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Body Style</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['body_style']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Price</td>
                        <td class="py-2 text-gray-800 font-medium">$<?php echo number_format($vehicle['price'], 2); ?></td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Status</td>
                        <td class="py-2">
                          <span class="<?php echo !empty($vehicle['status_class']) ? htmlspecialchars($vehicle['status_class']) : 'bg-green-100 text-green-800'; ?> text-xs px-2 py-1 rounded-full font-medium">
                            <?php echo htmlspecialchars($vehicle['status']); ?>
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div>
                <!-- Technical Details -->
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                  <h3 class="font-medium text-gray-800 mb-3">Technical Details</h3>
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">VIN</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['vin']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Mileage</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo number_format($vehicle['mileage']); ?> miles</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Engine</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['engine']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Transmission</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['transmission']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Fuel Type</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['fuel_type']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Drive Type</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['drivetrain']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Exterior Color</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['exterior_color']); ?></td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Interior Color</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['interior_color']); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Description -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="font-medium text-gray-800 mb-3">Description</h3>
                  <?php if (!empty($vehicle['description'])): ?>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($vehicle['description'])); ?></p>
                  <?php else: ?>
                    <p class="text-gray-500 italic">No description available.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Inquiries Tab -->
          <div id="inquiries" class="tab-content">
            <?php if (empty($inquiries)): ?>
              <div class="bg-gray-50 p-8 rounded-lg text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No Inquiries Yet</h3>
                <p class="text-gray-600">There are no customer inquiries for this vehicle yet.</p>
              </div>
            <?php else: ?>
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="bg-gray-50">
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Name</th>
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Contact</th>
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Message</th>
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Date</th>
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                      <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($inquiries as $inquiry): ?>
                      <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3">
                          <div class="font-medium text-gray-800"><?php echo htmlspecialchars($inquiry['full_name']); ?></div>
                          <?php if (!empty($inquiry['user_id'])): ?>
                            <div class="text-xs text-gray-500">Registered User</div>
                          <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                          <div class="text-sm"><?php echo htmlspecialchars($inquiry['email']); ?></div>
                          <div class="text-xs text-gray-500"><?php echo htmlspecialchars($inquiry['phone']); ?></div>
                          <div class="text-xs text-indigo-600">Prefers: <?php echo htmlspecialchars(ucfirst($inquiry['contact_method'])); ?></div>
                        </td>
                        <td class="px-4 py-3">
                          <div class="max-w-xs text-sm text-gray-700 truncate">
                            <?php echo !empty($inquiry['message']) ? htmlspecialchars($inquiry['message']) : '<span class="text-gray-400 italic">No message</span>'; ?>
                          </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                          <?php echo date('M d, Y g:i A', strtotime($inquiry['submitted_at'])); ?>
                        </td>
                        <td class="px-4 py-3">
                          <span class="status-badge <?php echo str_replace(' ', '-', $inquiry['status']); ?>">
                            <?php echo htmlspecialchars($inquiry['status']); ?>
                          </span>
                        </td>
                        <td class="px-4 py-3">
                          <button onclick="showStatusModal(<?php echo $inquiry['id']; ?>, '<?php echo $inquiry['status']; ?>')" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Update Status
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- Images Tab -->
          <div id="images" class="tab-content">
            <?php if (empty($images)): ?>
              <div class="bg-gray-50 p-8 rounded-lg text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No Images Available</h3>
                <p class="text-gray-600 mb-4">This vehicle doesn't have any images uploaded yet.</p>
                <a href="edit_vehicle_form.php?id=<?php echo $vehicle_id; ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm">
                  Upload Images
                </a>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <?php foreach ($images as $image): ?>
                  <div class="relative group">
                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Vehicle Image" class="w-full h-40 object-cover rounded-lg shadow-sm">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center">
                      <div class="opacity-0 group-hover:opacity-100 flex space-x-2">
                        <a href="../<?php echo htmlspecialchars($image['image_path']); ?>" target="_blank" class="p-1.5 bg-white rounded-full text-indigo-600 hover:text-indigo-800 transition-colors">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                          </svg>
                        </a>
                        <?php if ($image['is_primary'] == 0): ?>
                          <form method="post" action="set_primary_image.php" class="inline">
                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                            <button type="submit" class="p-1.5 bg-white rounded-full text-yellow-600 hover:text-yellow-800 transition-colors" title="Set as primary">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php if ($image['is_primary'] == 1): ?>
                      <div class="absolute top-2 right-2">
                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Primary</span>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="mt-6">
                <a href="edit_vehicle_form.php?id=<?php echo $vehicle_id; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm inline-flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  Add More Images
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Status Update Modal -->
  <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Update Inquiry Status</h3>
          <button type="button" id="closeStatusModal" class="text-gray-400 hover:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form method="post" action="">
          <input type="hidden" name="inquiry_id" id="inquiry_id" value="">
          <div class="mb-4">
            <label for="new_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="new_status" name="new_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              <option value="New">New</option>
              <option value="In Progress">In Progress</option>
              <option value="Contacted">Contacted</option>
              <option value="Closed">Closed</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3">
            <button type="button" id="cancelStatusUpdate" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm">
              Cancel
            </button>
            <button type="submit" name="update_inquiry_status" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium text-sm">
              Update Status
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Gallery thumbnails (for main image display)
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainImage = document.getElementById('mainImage');

    if (galleryThumbs.length > 0 && mainImage) {
      galleryThumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
          // Remove active class from all thumbnails
          galleryThumbs.forEach(t => t.classList.remove('active'));

          // Add active class to clicked thumbnail
          this.classList.add('active');

          // Update main image
          mainImage.src = this.src;
        });
      });
    }

    // Tab navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
      button.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');

        // Remove active class from all buttons and contents
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        // Add active class to clicked button and corresponding content
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');

        // Update URL hash to allow direct linking to tabs
        window.location.hash = tabId;
      });
    });

    // Check if there's a hash in the URL to activate the corresponding tab
    document.addEventListener('DOMContentLoaded', function() {
      const hash = window.location.hash.substring(1);
      if (hash && document.getElementById(hash)) {
        const tabButton = document.querySelector(`.tab-button[data-tab="${hash}"]`);
        if (tabButton) {
          tabButton.click();
        }
      }
    });

    // Flash message close button
    const closeFlashButtons = document.querySelectorAll('.close-flash');
    closeFlashButtons.forEach(button => {
      button.addEventListener('click', function() {
        const flashMessage = this.closest('.bg-green-100, .bg-red-100');
        if (flashMessage) {
          flashMessage.style.opacity = '0';
          setTimeout(() => {
            flashMessage.style.display = 'none';
          }, 500);
        }
      });
    });

    // Status update modal
    const statusModal = document.getElementById('statusModal');
    const closeStatusModal = document.getElementById('closeStatusModal');
    const cancelStatusUpdate = document.getElementById('cancelStatusUpdate');
    const inquiryIdInput = document.getElementById('inquiry_id');
    const newStatusSelect = document.getElementById('new_status');

    function showStatusModal(inquiryId, currentStatus) {
      inquiryIdInput.value = inquiryId;
      newStatusSelect.value = currentStatus;
      statusModal.classList.remove('hidden');
    }

    function hideStatusModal() {
      statusModal.classList.add('hidden');
    }

    if (closeStatusModal) {
      closeStatusModal.addEventListener('click', hideStatusModal);
    }

    if (cancelStatusUpdate) {
      cancelStatusUpdate.addEventListener('click', hideStatusModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target === statusModal) {
        hideStatusModal();
      }
    });

    // Cancel with ESC key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && !statusModal.classList.contains('hidden')) {
        hideStatusModal();
      }
    });
  </script>
</body>

</html>