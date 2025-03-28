<?php
session_start();
// Include necessary configuration and helper functions
require_once 'config/db.php';
require_once 'includes/helpers.php';

// Get site settings and assets
$site_settings = get_all_settings();
$site_assets = get_all_assets();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
  // Store current page as the redirect target after login
  $_SESSION['redirect_after_login'] = 'saved-vehicles.php';
  header("Location: login.php");
  exit;
}

// Function to get saved vehicles for the current user
function getSavedVehicles($conn, $user_id)
{
  $query = "SELECT v.*, sv.saved_at, 
                m.name as make_name,
                vs.name as status_name, 
                vs.css_class as status_class 
              FROM saved_vehicles sv
              JOIN vehicles v ON sv.vehicle_id = v.id
              LEFT JOIN makes m ON v.make = m.name
              LEFT JOIN vehicle_status vs ON v.status = vs.name
              WHERE sv.user_id = ?
              ORDER BY sv.saved_at DESC";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $vehicles = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      // Get the primary image for this vehicle
      $imageQuery = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? AND is_primary = 1 LIMIT 1";
      $imageStmt = $conn->prepare($imageQuery);
      $imageStmt->bind_param("i", $row['id']);
      $imageStmt->execute();
      $imageResult = $imageStmt->get_result();

      if ($imageResult && $imageResult->num_rows > 0) {
        $imageRow = $imageResult->fetch_assoc();
        $row['primary_image'] = $imageRow['image_path'];
      } else {
        // Fallback to any image if no primary image
        $anyImageQuery = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? LIMIT 1";
        $anyImageStmt = $conn->prepare($anyImageQuery);
        $anyImageStmt->bind_param("i", $row['id']);
        $anyImageStmt->execute();
        $anyImageResult = $anyImageStmt->get_result();

        if ($anyImageResult && $anyImageResult->num_rows > 0) {
          $anyImageRow = $anyImageResult->fetch_assoc();
          $row['primary_image'] = $anyImageRow['image_path'];
        } else {
          // Use a placeholder if no images available
          $row['primary_image'] = 'assets/images/placeholder.jpg';
        }

        $anyImageStmt->close();
      }

      $imageStmt->close();
      $vehicles[] = $row;
    }
  }

  $stmt->close();
  return $vehicles;
}

// Handle remove vehicle from saved list
if (isset($_POST['remove_vehicle']) && isset($_POST['vehicle_id'])) {
  $vehicle_id = (int)$_POST['vehicle_id'];

  $removeQuery = "DELETE FROM saved_vehicles WHERE user_id = ? AND vehicle_id = ?";
  $removeStmt = $conn->prepare($removeQuery);
  $removeStmt->bind_param("ii", $_SESSION['user_id'], $vehicle_id);
  $removeStmt->execute();
  $removeStmt->close();

  // Redirect to same page to refresh the list
  header("Location: saved-vehicles.php?removed=1");
  exit;
}

// Get saved vehicles for the current user
$savedVehicles = getSavedVehicles($conn, $_SESSION['user_id']);
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">

  <!-- Dynamic Page Title -->
  <title><?php echo htmlspecialchars($site_settings['site_name'] ?? 'CentralAutogy'); ?> - <?php echo $page_title ?? 'Car Inventory Management'; ?></title>

  <!-- Favicon Handling -->
  <?php
  // Prioritize assets table, then fall back to site settings
  $favicon_path = $site_assets['favicon'] ?? $site_settings['favicon_path'] ?? 'assets/img/fav.png';
  ?>
  <link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon_path); ?>" type="image/x-icon">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    .saved-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
    }

    .saved-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .vehicle-image {
      height: 200px;
      object-fit: cover;
      width: 100%;
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

    /* Empty state animation */
    @keyframes float {
      0% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-10px);
      }

      100% {
        transform: translateY(0px);
      }
    }

    .float-animation {
      animation: float 3s ease-in-out infinite;
    }
  </style>
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Saved Vehicles</h1>
      <p class="text-gray-600">Manage your list of saved vehicles that you're interested in.</p>
    </div>

    <?php if (isset($_GET['removed']) && $_GET['removed'] == 1): ?>
      <!-- Toast notification for removed vehicle -->
      <div id="toast" class="toast show">
        <div class="toast-icon">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </div>
        <div>Vehicle removed from your saved list</div>
      </div>
    <?php endif; ?>

    <!-- Saved Vehicles Grid -->
    <?php if (empty($savedVehicles)): ?>
      <!-- Empty state -->
      <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <div class="max-w-md mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-6 float-animation" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
          <h2 class="text-xl font-semibold text-gray-800 mb-3">No saved vehicles yet</h2>
          <p class="text-gray-600 mb-6">Browse our inventory and save vehicles you're interested in. They'll appear here for easy reference.</p>
          <a href="index.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
            Browse Inventory
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- Grid display of saved vehicles -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($savedVehicles as $vehicle): ?>
          <div class="saved-card bg-white shadow-sm">
            <div class="relative">
              <img src="<?php echo htmlspecialchars($vehicle['primary_image']); ?>" alt="<?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>" class="vehicle-image">
              <span class="absolute top-2 right-2 <?php echo !empty($vehicle['status_class']) ? htmlspecialchars($vehicle['status_class']) : 'bg-green-100 text-green-800'; ?> text-xs px-2 py-1 rounded-full font-medium">
                <?php echo htmlspecialchars($vehicle['status_name']); ?>
              </span>
              <form method="POST" action="saved-vehicles.php" class="absolute top-2 left-2">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                <button type="submit" name="remove_vehicle" class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors" title="Remove from saved">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                  </svg>
                </button>
              </form>
            </div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
              <p class="text-gray-600 text-sm mb-3">
                <?php echo htmlspecialchars($vehicle['year']); ?> ·
                <?php echo number_format($vehicle['mileage']); ?> mi ·
                <?php echo htmlspecialchars($vehicle['fuel_type']); ?>
              </p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['body_style']); ?></span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['transmission']); ?></span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($vehicle['drivetrain']); ?></span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Saved on <?php echo date('M j, Y', strtotime($vehicle['saved_at'])); ?></span>
                  <p class="text-indigo-600 font-semibold text-xl">$<?php echo number_format($vehicle['price'], 2); ?></p>
                </div>
                <a href="car-details.php?id=<?php echo $vehicle['id']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Navigation buttons -->
      <div class="mt-10 flex justify-between">
        <a href="index.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          Back to Inventory
        </a>

        <a href="#" id="compareBtn" class="inline-flex items-center bg-indigo-100 text-indigo-600 hover:bg-indigo-200 px-4 py-2 rounded-lg font-medium transition-colors <?php echo count($savedVehicles) < 2 ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo count($savedVehicles) < 2 ? 'disabled' : ''; ?>>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          Compare Vehicles
        </a>
      </div>
    <?php endif; ?>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Toast notification auto-hide
    const toast = document.getElementById('toast');
    if (toast) {
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Compare button functionality (for future implementation)
    const compareBtn = document.getElementById('compareBtn');
    if (compareBtn && !compareBtn.hasAttribute('disabled')) {
      compareBtn.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Compare functionality will be implemented in a future update.');
      });
    }
  </script>
</body>

</html>