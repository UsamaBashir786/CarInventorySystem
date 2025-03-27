<?php
// Start session
session_start();

// Include database configuration
require_once '../config/db.php';

// Define variables and initialize with empty values
$username_email = $password = "";
$username_email_err = $password_err = $login_err = "";

// Check if user is already logged in, redirect to dashboard
if (isset($_SESSION["admin_id"])) {
    header("location: index.php");
    exit;
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username/email
    if (empty(trim($_POST["username"]))) {
        $username_email_err = "Please enter your username or email.";
    } else {
        $username_email = trim($_POST["username"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Check input errors before authenticating
    if (empty($username_email_err) && empty($password_err)) {
        // Query to check if the username/email exists
        $sql = "SELECT id, username, email, password, first_name, last_name, role FROM admin_users WHERE username = ? OR email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ss", $username_email, $username_email);
            
            // Execute the query
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if user exists
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $username, $email, $hashed_password, $first_name, $last_name, $role);
                    
                    // Fetch values
                    if ($stmt->fetch()) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;
                            $_SESSION["admin_email"] = $email;
                            $_SESSION["admin_name"] = $first_name . " " . $last_name;
                            $_SESSION["admin_role"] = $role;
                            $_SESSION["admin_loggedin"] = true;
                            
                            // Update last login timestamp
                            $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                            if ($update_stmt = $conn->prepare($update_sql)) {
                                $update_stmt->bind_param("i", $id);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            // Remember me functionality
                            if (isset($_POST["remember"]) && $_POST["remember"] == "on") {
                                $token = bin2hex(random_bytes(32));
                                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                                
                                setcookie("admin_remember_token", $token, $expiry, "/", "", true, true);
                                
                                // Store the token in the database (you would need an admin_tokens table)
                                $token_sql = "INSERT INTO admin_tokens (admin_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
                                if ($token_stmt = $conn->prepare($token_sql)) {
                                    $token_stmt->bind_param("isi", $id, $token, $expiry);
                                    $token_stmt->execute();
                                    $token_stmt->close();
                                }
                            }
                            
                            // Log the login activity
                            $activity_sql = "INSERT INTO admin_activity_logs (admin_id, activity_type, ip_address, user_agent) 
                                           VALUES (?, 'login', ?, ?)";
                            if ($activity_stmt = $conn->prepare($activity_sql)) {
                                $ip_address = $_SERVER['REMOTE_ADDR'];
                                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                                $activity_stmt->bind_param("iss", $id, $ip_address, $user_agent);
                                $activity_stmt->execute();
                                $activity_stmt->close();
                            }
                            
                            // Redirect to admin dashboard
                            header("location: index.php");
                            exit;
                        } else {
                            // Password is incorrect
                            $login_err = "Invalid username/email or password.";
                        }
                    }
                } else {
                    // Username/email doesn't exist
                    $login_err = "Invalid username/email or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - Admin Login</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
  <div class="login-card bg-white w-full max-w-5xl flex flex-col md:flex-row">
    <!-- Left side - car image with overlay -->
    <div class="hidden md:block md:w-1/2 bg-car relative">
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/90 to-purple-900/70 flex flex-col justify-center items-center p-12">
        <div class="text-center">
          <div class="flex items-center justify-center mb-6 animated">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
              <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
            </svg>
            <h1 class="text-3xl font-bold text-white">CentralAutogy</h1>
          </div>
          <p class="text-white/90 text-lg mb-8 animated-delay-1">Your complete car inventory management solution</p>
          <div class="space-y-6 text-left animated-delay-2">
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Efficiently manage your entire vehicle inventory</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Track sales, orders, and customer interactions</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Generate comprehensive reports and analytics</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right side - Login form -->
    <div class="w-full md:w-1/2 py-8 px-4 sm:px-12 flex flex-col justify-center">
      <div class="max-w-md mx-auto w-full">
        <div class="text-center md:text-left mb-8 animated">
          <!-- Logo for mobile view -->
          <div class="flex items-center justify-center md:hidden mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
              <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-800">CentralAutogy</h1>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">Admin Login</h2>
          <p class="text-gray-600 mt-2">Sign in to access your dashboard</p>
        </div>

        <?php
        // Display error message if there is one
        if (!empty($login_err)) {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>' . $login_err . '</p>
                  </div>';
        }
        ?>

        <form id="loginForm" class="space-y-6 animated-delay-1" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="text" id="username" name="username" class="form-input w-full px-4 py-3 border <?php echo (!empty($username_email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Username or Email" value="<?php echo $username_email; ?>" required>
            <?php if (!empty($username_email_err)): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $username_email_err; ?></p>
            <?php endif; ?>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="password" id="password" name="password" class="form-input w-full px-4 py-3 border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Password" required>
            <?php if (!empty($password_err)): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $password_err; ?></p>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
              <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
          </div>

          <div>
            <button type="submit" class="login-btn w-full py-3 px-4 rounded-lg text-white font-medium shadow-md flex items-center justify-center">
              <span>Sign In</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 animated-delay-3">
          <div class="text-center">
            <p class="text-sm text-gray-600">
              Need help? Contact <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">IT Support</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toast notification function
    function showToast(message, type = 'success') {
      // Create toast element
      const toast = document.createElement('div');
      toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
      } text-white transform transition-all duration-500 opacity-0 translate-y-12`;

      // Create icon
      const icon = document.createElement('span');
      if (type === 'success') {
        icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>`;
      } else {
        icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>`;
      }

      // Create message text
      const text = document.createElement('span');
      text.textContent = message;
      text.className = 'font-medium';

      // Append elements
      toast.appendChild(icon);
      toast.appendChild(text);
      document.body.appendChild(toast);

      // Animate in
      setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-12');
      }, 10);

      // Animate out after 3 seconds
      setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-12');
        setTimeout(() => {
          document.body.removeChild(toast);
        }, 500);
      }, 3000);
    }

    <?php 
    // Show success toast if redirected from logout
    if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
      echo "document.addEventListener('DOMContentLoaded', function() { showToast('You have been successfully logged out', 'success'); });";
    }
    ?>
  </script>
</body>

</html>