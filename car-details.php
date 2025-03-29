<?php
session_start();
// Include database connection
require_once 'config/db.php';

// Get vehicle ID from URL parameter
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect to index if no ID provided
if ($vehicle_id <= 0) {
  header("Location: index.php");
  exit;
}

// Check if vehicle is saved by the current user
$is_saved = false;
if (isset($_SESSION['user_id'])) {
  $check_saved_query = "SELECT id FROM saved_vehicles WHERE user_id = ? AND vehicle_id = ?";
  $check_stmt = $conn->prepare($check_saved_query);
  $check_stmt->bind_param("ii", $_SESSION['user_id'], $vehicle_id);
  $check_stmt->execute();
  $saved_result = $check_stmt->get_result();
  $is_saved = $saved_result->num_rows > 0;
  $check_stmt->close();
}

// Function to get vehicle details
function getVehicleDetails($conn, $vehicle_id)
{
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

  if ($result->num_rows == 0) {
    return null;
  }

  $vehicle = $result->fetch_assoc();
  $stmt->close();
  return $vehicle;
}

// Function to get vehicle images
function getVehicleImages($conn, $vehicle_id)
{
  // Check if display_order column exists in the vehicle_images table
  $checkColumnQuery = "SHOW COLUMNS FROM vehicle_images LIKE 'display_order'";
  $checkResult = $conn->query($checkColumnQuery);

  if ($checkResult && $checkResult->num_rows > 0) {
    // If the display_order column exists, use it in the query
    $query = "SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, display_order ASC";
  } else {
    // If display_order doesn't exist, just use is_primary for ordering
    $query = "SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC";
  }

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $vehicle_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $images = [];
  while ($row = $result->fetch_assoc()) {
    $images[] = $row;
  }

  $stmt->close();
  return $images;
}

// Get vehicle details
$vehicle = getVehicleDetails($conn, $vehicle_id);

// Redirect to index if vehicle not found
if ($vehicle === null) {
  header("Location: index.php");
  exit;
}

// Get vehicle images
$images = getVehicleImages($conn, $vehicle_id);

// Default image if no images found
$defaultImage = "assets/images/placeholder.jpg";
$primaryImage = $defaultImage;

if (!empty($images)) {
  $primaryImage = $images[0]['image_path'];
}

// Vehicle title for SEO
$pageTitle = $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' - CentralAutogy';

// Generate PDF brochure data
$brochureData = array(
  'id' => $vehicle['id'],
  'make' => $vehicle['make'],
  'model' => $vehicle['model'],
  'year' => $vehicle['year'],
  'price' => $vehicle['price'] !== null ? $vehicle['price'] : 'Contact for price',
  'mileage' => $vehicle['mileage'],
  'vin' => $vehicle['vin'],
  'status' => $vehicle['status'],
  'engine' => $vehicle['engine'],
  'transmission' => $vehicle['transmission'],
  'fuel_type' => $vehicle['fuel_type'],
  'drivetrain' => $vehicle['drivetrain'],
  'body_style' => $vehicle['body_style'],
  'exterior_color' => $vehicle['exterior_color'],
  'interior_color' => $vehicle['interior_color'],
  'description' => $vehicle['description'],
  'image' => $primaryImage
);

// Encode for share functionality
$shareData = json_encode($brochureData);
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <!-- Open Graph tags for sharing -->
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars(substr($vehicle['description'], 0, 200) . '...'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($primaryImage); ?>">
  <meta property="og:url" content="<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
  <meta property="og:type" content="website">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    .feature-card {
      transition: all 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    /* Gallery thumbnails */
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

    /* Custom tab styles */
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

    /* Share modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal.show {
      display: flex;
      opacity: 1;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      border-radius: 0.5rem;
      padding: 1.5rem;
      max-width: 500px;
      width: 90%;
      transform: translateY(-20px);
      transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
      transform: translateY(0);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .modal-close {
      cursor: pointer;
      padding: 0.5rem;
    }

    .share-option {
      display: flex;
      align-items: center;
      padding: 0.75rem;
      margin-bottom: 0.5rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .share-option:hover {
      background-color: #f3f4f6;
    }

    .share-icon {
      width: 2rem;
      height: 2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin-right: 0.75rem;
    }

    /* Toast notification */
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #4f46e5;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 4px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .toast.show {
      transform: translateY(0);
      opacity: 1;
    }

    .toast-icon {
      margin-right: 0.75rem;
    }
  </style>
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <a href="index.php" class="text-gray-600 hover:text-indigo-600 text-sm">Home</a>
          </li>
          <li>
            <div class="flex items-center">
              <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
              </svg>
              <a href="index.php" class="text-gray-600 hover:text-indigo-600 text-sm">Inventory</a>
            </div>
          </li>
          <li aria-current="page">
            <div class="flex items-center">
              <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
              </svg>
              <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Car Details Header -->
    <div class="flex flex-col md:flex-row justify-between items-start mb-6">
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
          <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
        </h1>
        <p class="text-gray-600">
          <?php echo htmlspecialchars($vehicle['body_style'] . ' • ' . $vehicle['exterior_color'] . ' • ' . $vehicle['interior_color'] . ' Interior'); ?>
        </p>
      </div>
      <div class="mt-4 md:mt-0">
        <?php if ($vehicle['price'] !== null && $vehicle['price'] != 0): ?>
          <div class="text-2xl md:text-3xl font-bold text-indigo-600">$<?php echo number_format($vehicle['price'], 0); ?></div>
          <div class="text-gray-500 text-sm">
            Est. $<?php echo number_format($vehicle['price'] / 60, 0); ?>/month*
          </div>
        <?php else: ?>
          <div class="text-2xl md:text-3xl font-bold text-indigo-600">Contact for price</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Car Gallery & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-12">
      <!-- Gallery - 3 cols wide on lg -->
      <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
          <!-- Main Image -->
          <div class="relative aspect-video">
            <img id="mainImage" src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>" class="w-full h-full object-cover">
          </div>

          <!-- Thumbnails -->
          <div class="grid grid-cols-5 gap-2 p-2">
            <?php if (empty($images)): ?>
              <img src="<?php echo htmlspecialchars($defaultImage); ?>" alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?> - No Image" class="gallery-thumb active aspect-video object-cover rounded">
            <?php else: ?>
              <?php foreach (array_slice($images, 0, 5) as $index => $image): ?>
                <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                  alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>"
                  class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?> aspect-video object-cover rounded">
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Vehicle Description -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="border-b border-gray-200">
            <div class="flex overflow-x-auto">
              <button class="tab-button active px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="description">Description</button>
              <button class="tab-button px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="features">Features</button>
              <button class="tab-button px-6 py-4 font-medium text-gray-700 whitespace-nowrap" data-tab="specs">Specifications</button>
            </div>
          </div>

          <div class="p-6">
            <!-- Description Tab -->
            <div id="description" class="tab-content active">
              <?php if (!empty($vehicle['description'])): ?>
                <?php
                // Split description into paragraphs
                $paragraphs = explode("\n", $vehicle['description']);
                foreach ($paragraphs as $paragraph):
                  if (trim($paragraph) !== ''):
                ?>
                    <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($paragraph); ?></p>
                <?php
                  endif;
                endforeach;
                ?>
              <?php else: ?>
                <p class="text-gray-700 mb-4">This <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?> comes equipped with a <?php echo htmlspecialchars($vehicle['engine']); ?> engine and <?php echo htmlspecialchars($vehicle['transmission']); ?> transmission.</p>
                <p class="text-gray-700 mb-4">The exterior features a sleek <?php echo htmlspecialchars($vehicle['exterior_color']); ?> finish, while the <?php echo htmlspecialchars($vehicle['interior_color']); ?> interior provides a comfortable and stylish cabin space.</p>
                <p class="text-gray-700 mb-4">With only <?php echo number_format($vehicle['mileage']); ?> miles, this vehicle offers excellent value and reliability. Contact us today to schedule a test drive!</p>
              <?php endif; ?>
            </div>

            <!-- Features Tab -->
            <div id="features" class="tab-content">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <h3 class="font-medium text-gray-800 mb-3">Interior Features</h3>
                  <ul class="space-y-2">
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      <?php echo $vehicle['transmission']; ?>
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      <?php echo $vehicle['interior_color']; ?> Interior
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Infotainment System
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Climate Control
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Power Windows/Locks
                    </li>
                  </ul>
                </div>

                <div>
                  <h3 class="font-medium text-gray-800 mb-3">Safety & Technology</h3>
                  <ul class="space-y-2">
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Anti-lock Braking System
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Electronic Stability Control
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Airbag System
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Backup Camera
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Bluetooth Connectivity
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Specifications Tab -->
            <div id="specs" class="tab-content">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                  <h3 class="font-medium text-gray-800 mb-3">Engine & Performance</h3>
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Engine</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['engine']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Transmission</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['transmission']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Drive Type</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['drivetrain']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Fuel Type</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['fuel_type']); ?></td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Mileage</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo number_format($vehicle['mileage']); ?> miles</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div>
                  <h3 class="font-medium text-gray-800 mb-3">Dimensions & Details</h3>
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Body Style</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['body_style']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Exterior Color</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['exterior_color']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Interior Color</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['interior_color']); ?></td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">VIN</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['vin']); ?></td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Year</td>
                        <td class="py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($vehicle['year']); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar - 2 cols wide on lg -->
      <div class="lg:col-span-2">
        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="#contact-form" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition-colors text-center shadow-sm flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
              </svg>
              Contact Seller
            </a>
            <button id="saveVehicleBtn" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center <?php echo $is_saved ? 'text-red-600 hover:text-red-700' : ''; ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 <?php echo $is_saved ? 'text-red-600' : 'text-indigo-600'; ?>" viewBox="0 0 20 20" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>" stroke="currentColor">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
              </svg>
              <span><?php echo $is_saved ? 'Saved' : 'Save Vehicle'; ?></span>
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <button id="shareBtn" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
              </svg>
              Share
            </button>
            <button id="downloadBrochureBtn" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              Download Brochure
            </button>
          </div>
        </div>
        <!-- Booking Form -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6" id="contact-form">
          <h3 class="font-semibold text-gray-800 text-lg mb-4">Interested in this Vehicle?</h3>
          <form id="bookingForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label for="fullName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="fullName" name="fullName" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" id="phone" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label for="contactMethod" class="block text-sm font-medium text-gray-700 mb-1">Preferred Contact Method</label>
                <select id="contactMethod" name="contactMethod" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option value="">Select an option</option>
                  <option value="email">Email</option>
                  <option value="phone">Phone</option>
                  <option value="text">Text Message</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label for="additionalInfo" class="block text-sm font-medium text-gray-700 mb-1">Questions or Comments</label>
              <textarea id="additionalInfo" name="additionalInfo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="mb-4">
              <label class="inline-flex items-center">
                <input type="checkbox" name="termsAgree" required class="rounded text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600">I agree to the <a href="#" class="text-indigo-600 hover:underline">terms and conditions</a></span>
              </label>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-medium transition-colors shadow-sm">
              Contact About This Vehicle
            </button>
          </form>
        </div>
        <!-- Quick Specs -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <h3 class="font-semibold text-gray-800 text-lg mb-4">Quick Overview</h3>

          <div class="grid grid-cols-2 gap-4">
            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Mileage</div>
              <div class="font-medium text-gray-800"><?php echo number_format($vehicle['mileage']); ?> mi</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Year</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['year']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Engine</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['engine']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Transmission</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['transmission']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Fuel Type</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['fuel_type']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Drive Type</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['drivetrain']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Ext. Color</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['exterior_color']); ?></div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Int. Color</div>
              <div class="font-medium text-gray-800"><?php echo htmlspecialchars($vehicle['interior_color']); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Similar Cars -->
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Similar Vehicles</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        // Get similar vehicles based on make, model, or body style
        $similarQuery = "SELECT v.*, vs.css_class as status_class FROM vehicles v
                         LEFT JOIN vehicle_status vs ON v.status = vs.name
                         WHERE v.id != ? 
                         AND (v.make = ? OR v.body_style = ?)
                         AND v.status = 'Available'
                         LIMIT 4";

        $similarStmt = $conn->prepare($similarQuery);
        $similarStmt->bind_param("iss", $vehicle_id, $vehicle['make'], $vehicle['body_style']);
        $similarStmt->execute();
        $similarResult = $similarStmt->get_result();

        if ($similarResult->num_rows > 0) {
          while ($similarVehicle = $similarResult->fetch_assoc()):
            // Get primary image for similar vehicle
            $imageQuery = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? LIMIT 1";
            $imageStmt = $conn->prepare($imageQuery);
            $imageStmt->bind_param("i", $similarVehicle['id']);
            $imageStmt->execute();
            $imageResult = $imageStmt->get_result();

            $similarImage = $defaultImage;
            if ($imageResult->num_rows > 0) {
              $imageRow = $imageResult->fetch_assoc();
              $similarImage = $imageRow['image_path'];
            }
            $imageStmt->close();
        ?>
            <!-- Similar Car Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
              <div class="relative">
                <img src="<?php echo htmlspecialchars($similarImage); ?>"
                  alt="<?php echo htmlspecialchars($similarVehicle['year'] . ' ' . $similarVehicle['make'] . ' ' . $similarVehicle['model']); ?>"
                  class="w-full h-48 object-cover">
              </div>
              <div class="p-4">
                <div class="mb-1">
                  <span class="<?php echo !empty($similarVehicle['status_class']) ? htmlspecialchars($similarVehicle['status_class']) : 'bg-green-100 text-green-800'; ?> text-xs px-2 py-1 rounded-full font-medium">
                    <?php echo htmlspecialchars($similarVehicle['status']); ?>
                  </span>
                </div>
                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($similarVehicle['make'] . ' ' . $similarVehicle['model']); ?></h3>
                <p class="text-gray-600 text-sm mb-2">
                  <?php echo htmlspecialchars($similarVehicle['year']); ?> ·
                  <?php echo number_format($similarVehicle['mileage']); ?> mi ·
                  <?php echo htmlspecialchars($similarVehicle['fuel_type']); ?>
                </p>
                <div class="flex justify-between items-end">
                  <div>
                    <?php if ($similarVehicle['price'] !== null && $similarVehicle['price'] != 0): ?>
                      <p class="text-indigo-600 font-semibold text-lg">$<?php echo number_format($similarVehicle['price']); ?></p>
                    <?php else: ?>
                      <p class="text-indigo-600 font-semibold text-lg">Contact for price</p>
                    <?php endif; ?>
                  </div>
                  <a href="car-details.php?id=<?php echo $similarVehicle['id']; ?>" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View Details</a>
                </div>
              </div>
            </div>
        <?php
          endwhile;
        } else {
          // If no similar vehicles found, show a message
          echo '<div class="col-span-full text-center py-4">';
          echo '<p class="text-gray-600">No similar vehicles currently available.</p>';
          echo '</div>';
        }

        // Close statement
        $similarStmt->close();
        ?>
      </div>
    </div>
  </main>

  <!-- Share Modal -->
  <div id="shareModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="text-lg font-semibold text-gray-800">Share this Vehicle</h3>
        <button class="modal-close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div>
        <div class="share-option" data-type="facebook">
          <div class="share-icon bg-blue-100 text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M9.198 21.5h4v-8.01h3.604l.396-3.98h-4V7.5a1 1 0 011-1h3v-4h-3a5 5 0 00-5 5v2.01h-2l-.396 3.98h2.396v8.01z" />
            </svg>
          </div>
          <span>Share on Facebook</span>
        </div>
        <div class="share-option" data-type="twitter">
          <div class="share-icon bg-blue-100 text-blue-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
            </svg>
          </div>
          <span>Share on Twitter</span>
        </div>
        <div class="share-option" data-type="whatsapp">
          <div class="share-icon bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
            </svg>
          </div>
          <span>Share on WhatsApp</span>
        </div>
        <div class="share-option" data-type="email">
          <div class="share-icon bg-gray-100 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
          <span>Share via Email</span>
        </div>
        <div class="share-option" data-type="copy">
          <div class="share-icon bg-gray-100 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
            </svg>
          </div>
          <span>Copy Link</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" class="toast">
    <div class="toast-icon">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
    </div>
    <div id="toastMessage">Success message here</div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Gallery thumbnails
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainImage = document.getElementById('mainImage');

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

    // Tabs
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
      });
    });

    // Share functionality
    const shareBtn = document.getElementById('shareBtn');
    const shareModal = document.getElementById('shareModal');
    const modalClose = document.querySelector('.modal-close');
    const shareOptions = document.querySelectorAll('.share-option');
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');

    // Show share modal
    shareBtn.addEventListener('click', function() {
      shareModal.classList.add('show');
    });

    // Close share modal
    modalClose.addEventListener('click', function() {
      shareModal.classList.remove('show');
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target === shareModal) {
        shareModal.classList.remove('show');
      }
    });

    // Share options handling
    shareOptions.forEach(option => {
      option.addEventListener('click', function() {
        const shareType = this.getAttribute('data-type');
        const url = window.location.href;
        const title = "<?php echo addslashes($pageTitle); ?>";

        switch (shareType) {
          case 'facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
            break;
          case 'twitter':
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
            break;
          case 'whatsapp':
            window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
            break;
          case 'email':
            window.location.href = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent('Check out this vehicle: ' + url)}`;
            break;
          case 'copy':
            navigator.clipboard.writeText(url).then(() => {
              showToast('Link copied to clipboard!');
            });
            break;
        }

        shareModal.classList.remove('show');
      });
    });

    // Show toast notification
    function showToast(message) {
      toastMessage.textContent = message;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Save vehicle functionality
    const saveVehicleBtn = document.getElementById('saveVehicleBtn');

    saveVehicleBtn.addEventListener('click', function() {
      <?php if (isset($_SESSION['user_id'])): ?>
        const vehicleId = <?php echo $vehicle_id; ?>;
        const isSaved = <?php echo $is_saved ? 'true' : 'false'; ?>;

        fetch('save_vehicle.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `vehicle_id=${vehicleId}&action=${isSaved ? 'remove' : 'add'}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update button appearance
              if (isSaved) {
                this.querySelector('svg').setAttribute('fill', 'none');
                this.classList.remove('text-red-600', 'hover:text-red-700');
                this.querySelector('span').textContent = 'Save Vehicle';
                showToast('Vehicle removed from saved list');
              } else {
                this.querySelector('svg').setAttribute('fill', 'currentColor');
                this.classList.add('text-red-600', 'hover:text-red-700');
                this.querySelector('span').textContent = 'Saved';
                showToast('Vehicle saved to your list');
              }

              // Refresh page to update the saved status
              setTimeout(() => {
                location.reload();
              }, 1000);
            } else {
              showToast('Error: ' + data.message);
            }
          })
          .catch(error => {
            showToast('Error saving vehicle. Please try again.');
            console.error('Error:', error);
          });
      <?php else: ?>
        // Redirect to login page if user is not logged in
        window.location.href = 'login.php?redirect=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>';
      <?php endif; ?>
    });

    // Download brochure functionality
    const downloadBrochureBtn = document.getElementById('downloadBrochureBtn');

    downloadBrochureBtn.addEventListener('click', function() {
      const vehicleData = <?php echo $shareData; ?>;

      fetch('generate_brochure.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(vehicleData)
        })
        .then(response => {
          if (response.ok) {
            return response.blob();
          }
          throw new Error('Network response was not ok.');
        })
        .then(blob => {
          // Create a link element
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = url;
          a.download = `${vehicleData.year}_${vehicleData.make}_${vehicleData.model}_brochure.pdf`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);

          showToast('Brochure downloaded successfully!');
        })
        .catch(error => {
          showToast('Error generating brochure. Please try again.');
          console.error('Error:', error);
        });
    });

    // Form submission with AJAX
    const bookingForm = document.getElementById('bookingForm');

    if (bookingForm) {
      bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Simple form validation
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const contactMethod = document.getElementById('contactMethod').value;
        const additionalInfo = document.getElementById('additionalInfo').value.trim();
        const termsAgreed = document.querySelector('input[name="termsAgree"]').checked;
        const vehicleId = <?php echo $vehicle_id; ?>;

        if (!fullName || !email || !phone || !contactMethod || !termsAgreed) {
          alert('Please fill out all required fields and agree to the terms and conditions.');
          return;
        }

        // Create form data object
        const formData = new FormData();
        formData.append('vehicle_id', vehicleId);
        formData.append('full_name', fullName);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('contact_method', contactMethod);
        formData.append('message', additionalInfo);
        formData.append('terms_agreed', termsAgreed ? 1 : 0);

        // Show loading indicator
        const submitBtn = bookingForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending...';

        // Send AJAX request
        fetch('process_inquiry.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Show success message
              bookingForm.innerHTML = `
              <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-green-700">
                      Thank you for your interest! A representative will contact you shortly.
                    </p>
                  </div>
                </div>
              </div>
            `;
            } else {
              // Show error message
              alert('There was an error submitting your inquiry. Please try again or contact us directly.');
              submitBtn.disabled = false;
              submitBtn.innerHTML = originalBtnText;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('There was an error submitting your inquiry. Please try again or contact us directly.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
          });
      });
    }
  </script>
</body>

</html>