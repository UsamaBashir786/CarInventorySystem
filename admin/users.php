<?php
// Start session if not already started
session_start();

// Include database configuration
include '../config/db.php';
// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
  header("location: login.php");
  exit;
}
// Process form submissions
$success_message = '';
$error_message = '';

// Handle user deletion
if (isset($_POST['delete_user'])) {
  $user_id = $_POST['user_id'];

  // Don't allow admin to delete themselves
  if ($user_id == $_SESSION['admin_id']) {
    $error_message = "You cannot delete your own account.";
  } else {
    // Delete user
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
      $success_message = "User has been deleted successfully.";
    } else {
      $error_message = "Failed to delete user: " . $conn->error;
    }
  }
}

// Handle user creation or update
if (isset($_POST['save_user'])) {
  $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $marketing = isset($_POST['marketing']) ? 1 : 0;
  $password = $_POST['password'];

  // For existing user
  if ($user_id) {
    // If password field is empty, don't update password
    if (empty($password)) {
      $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, marketing_consent = ? WHERE id = ?";
      $stmt = $conn->prepare($update_query);
      $stmt->bind_param("ssssii", $first_name, $last_name, $email, $phone, $marketing, $user_id);
    } else {
      // If password is provided, update with new password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, marketing_consent = ? WHERE id = ?";
      $stmt = $conn->prepare($update_query);
      $stmt->bind_param("sssssii", $first_name, $last_name, $email, $phone, $hashed_password, $marketing, $user_id);
    }

    if ($stmt->execute()) {
      $success_message = "User has been updated successfully.";
    } else {
      $error_message = "Failed to update user: " . $conn->error;
    }
  }
  // For new user
  else {
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $error_message = "Email already exists.";
    } else {
      // Create new user
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $insert_query = "INSERT INTO users (first_name, last_name, email, phone, password, marketing_consent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $hashed_password, $marketing);

      if ($stmt->execute()) {
        $success_message = "New user has been created successfully.";
      } else {
        $error_message = "Failed to create user: " . $conn->error;
      }
    }
  }
}

// Get user details if viewing a specific user
$user_details = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
  $user_id = $_GET['view'];
  $query = "SELECT * FROM users WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $user_details = $result->fetch_assoc();
  }
}

// Get all users
$users = [];
$users_query = "SELECT id, first_name, last_name, email, phone, marketing_consent, created_at, last_login FROM users ORDER BY created_at DESC";
$result = $conn->query($users_query);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

// Count total users
$total_users = count($users);
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - User Management</title>
  <link rel="stylesheet" href="assets/css/index.css">
  <style>
    .modal {
      transition: opacity 0.25s ease;
    }

    body.modal-active {
      overflow-x: hidden;
      overflow-y: visible !important;
    }

    .opacity-95 {
      opacity: 0.95;
    }

    .user-table th {
      position: sticky;
      top: 0;
      background-color: #1f2937;
      z-index: 10;
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
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
        <p class="text-gray-600">Manage your system users and customer accounts</p>
      </div>

      <!-- Success/Error Messages -->
      <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
          <p><?php echo $success_message; ?></p>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
          <p><?php echo $error_message; ?></p>
        </div>
      <?php endif; ?>

      <?php if ($user_details): ?>
        <!-- User Details View -->
        <div class="dashboard-card bg-white p-6 w-full mb-6">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
              <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                <span class="text-3xl font-medium text-gray-700"><?php echo substr($user_details['first_name'], 0, 1); ?></span>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($user_details['email']); ?></p>
              </div>
            </div>
            <a href="users.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-300 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              Back to Users
            </a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold mb-4 text-gray-700">Contact Information</h3>
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-gray-500">Phone</p>
                  <p class="text-gray-800"><?php echo htmlspecialchars($user_details['phone']); ?></p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <p class="text-gray-800"><?php echo htmlspecialchars($user_details['email']); ?></p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Marketing Consent</p>
                  <p class="text-gray-800"><?php echo $user_details['marketing_consent'] ? 'Yes' : 'No'; ?></p>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold mb-4 text-gray-700">User Details</h3>
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-gray-500">Registered On</p>
                  <p class="text-gray-800"><?php echo date('F j, Y', strtotime($user_details['created_at'])); ?></p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Last Login</p>
                  <p class="text-gray-800"><?php echo $user_details['last_login'] ? date('F j, Y g:i A', strtotime($user_details['last_login'])) : 'Never'; ?></p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex space-x-3">
            <button onclick="editUser(<?php echo $user_details['id']; ?>, '<?php echo addslashes($user_details['first_name']); ?>', '<?php echo addslashes($user_details['last_name']); ?>', '<?php echo addslashes($user_details['email']); ?>', '<?php echo addslashes($user_details['phone']); ?>', <?php echo $user_details['marketing_consent']; ?>)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
              Edit User
            </button>
            <?php if ($user_details['id'] != $_SESSION['admin_id']): ?>
              <button onclick="confirmDelete(<?php echo $user_details['id']; ?>, '<?php echo addslashes($user_details['first_name'] . ' ' . $user_details['last_name']); ?>')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete User
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <!-- User Management Content -->
        <div class="dashboard-card bg-white p-6 w-full">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800">User Accounts</h2>
            <button id="addUserBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Add New User
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full user-table">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider rounded-tl-lg">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Marketing</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Last Login</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider rounded-tr-lg">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                  <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 table-row">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                              <span class="text-xl font-medium text-gray-700"><?php echo substr($user['first_name'], 0, 1); ?></span>
                            </div>
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['phone']); ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                          <?php echo $user['marketing_consent'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                          <?php echo $user['marketing_consent'] ? 'Yes' : 'No'; ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                          <button onclick="editUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name']); ?>', '<?php echo addslashes($user['last_name']); ?>', '<?php echo addslashes($user['email']); ?>', '<?php echo addslashes($user['phone']); ?>', <?php echo $user['marketing_consent']; ?>)" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                          </button>
                          <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                            <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['first_name'] . ' ' . $user['last_name']); ?>')" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                              </svg>
                            </button>
                          <?php endif; ?>
                          <a href="users.php?view=<?php echo $user['id']; ?>" class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-all" title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                              <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-between items-center">
            <div>
              <span class="text-sm text-gray-600">Showing <?php echo count($users); ?> of <?php echo $total_users; ?> users</span>
            </div>
            <div class="flex space-x-1">
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">Previous</button>
              <button class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700 transition-all">1</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">2</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">3</button>
              <button class="px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-600 text-sm hover:bg-gray-50 transition-all">Next</button>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Add/Edit User Modal -->
  <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl modal-animation">
        <div class="flex justify-between items-center border-b p-6">
          <div class="flex items-center">
            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Add New User</h3>
          </div>
          <button id="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-all p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form id="userForm" method="post" action="">
          <div class="p-6 max-h-[70vh] overflow-y-auto">
            <input type="hidden" id="user_id" name="user_id" value="">
            <div class="space-y-4">
              <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" name="first_name" id="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" name="last_name" id="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" id="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span id="passwordHint" class="text-xs text-gray-500">(Leave empty to keep current password)</span></label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
              </div>
              <div class="flex items-center">
                <input type="checkbox" name="marketing" id="marketing" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="marketing" class="ml-2 block text-sm text-gray-900">Marketing Consent</label>
              </div>
            </div>
          </div>
          <div class="flex justify-end border-t p-6 space-x-3">
            <button type="button" id="cancelBtn" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <button type="submit" name="save_user" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
              Save User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-2xl p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
        <h3 class="text-xl font-bold text-center text-gray-800 mb-4">Confirm User Deletion</h3>
        <p class="text-center text-gray-600 mb-6" id="deleteUserMessage">Are you sure you want to delete <span id="deleteUserName" class="font-medium"></span>? This action cannot be undone.</p>
        <div class="flex justify-center space-x-3">
          <button id="cancelDelete" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm">
            Cancel
          </button>
          <form method="post" action="">
            <input type="hidden" id="delete_user_id" name="user_id">
            <button type="submit" name="delete_user" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 font-medium text-sm">
              Delete User
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript for Modal Handling -->
  <script>
    // Modal Elements
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const closeModal = document.getElementById('closeModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelDelete = document.getElementById('cancelDelete');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const passwordHint = document.getElementById('passwordHint');

    // Add User Button
    addUserBtn.addEventListener('click', function() {
      modalTitle.innerText = "Add New User";
      userForm.reset();
      document.getElementById('user_id').value = '';
      document.getElementById('marketing').checked = false;
      passwordHint.classList.add('hidden');
      document.getElementById('password').required = true;
      userModal.classList.remove('hidden');
    });

    // Close Modal
    closeModal.addEventListener('click', function() {
      userModal.classList.add('hidden');
    });

    // Cancel Button
    cancelBtn.addEventListener('click', function() {
      userModal.classList.add('hidden');
    });

    // Close Delete Modal
    cancelDelete.addEventListener('click', function() {
      deleteModal.classList.add('hidden');
    });

    // Edit User Function
    function editUser(id, firstName, lastName, email, phone, marketing) {
      modalTitle.innerText = "Edit User";
      document.getElementById('user_id').value = id;
      document.getElementById('first_name').value = firstName;
      document.getElementById('last_name').value = lastName;
      document.getElementById('email').value = email;
      document.getElementById('phone').value = phone;
      document.getElementById('marketing').checked = marketing === 1;
      passwordHint.classList.remove('hidden');
      document.getElementById('password').required = false;
      userModal.classList.remove('hidden');
    }

    // Confirm Delete Function
    function confirmDelete(id, fullName) {
      document.getElementById('deleteUserName').innerText = fullName;
      document.getElementById('delete_user_id').value = id;
      deleteModal.classList.remove('hidden');
    }

    // Close Modals when clicked outside
    window.addEventListener('click', function(event) {
      if (event.target === userModal) {
        userModal.classList.add('hidden');
      }
      if (event.target === deleteModal) {
        deleteModal.classList.add('hidden');
      }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        userModal.classList.add('hidden');
        deleteModal.classList.add('hidden');
      }
    });
  </script>
</body>

</html>