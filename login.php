<?php
// Start session
session_start();

// If user is already logged in, redirect to index page
if (isset($_SESSION["user_id"])) {
  header("location: index.php");
  exit;
}

// Include database configuration
require_once 'config/db.php';

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";
$registration_success = false;

// Check if user just registered successfully
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
  $registration_success = true;
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Validate email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter your email.";
  } else {
    $email = trim($_POST["email"]);
  }

  // Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Check input errors before authenticating
  if (empty($email_err) && empty($password_err)) {
    // Prepare a select statement
    $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("s", $param_email);

      // Set parameters
      $param_email = $email;

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        // Store result
        $stmt->store_result();

        // Check if email exists, if yes then verify password
        if ($stmt->num_rows == 1) {
          // Bind result variables
          $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password);

          if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
              // Password is correct, start a new session
              session_start();

              // Store data in session variables
              $_SESSION["user_id"] = $id;
              $_SESSION["first_name"] = $first_name;
              $_SESSION["last_name"] = $last_name;
              $_SESSION["email"] = $email;
              $_SESSION["loggedin"] = true;

              // Update last login timestamp
              $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
              if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("i", $id);
                $update_stmt->execute();
                $update_stmt->close();
              }

              // Log the user session for security
              $session_sql = "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))";
              if ($session_stmt = $conn->prepare($session_sql)) {
                $session_id = session_id();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];

                $session_stmt->bind_param("isss", $id, $session_id, $ip_address, $user_agent);
                $session_stmt->execute();
                $session_stmt->close();
              }

              // Remember me functionality
              if (isset($_POST["remember"]) && $_POST["remember"] == "on") {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days

                setcookie("remember_token", $token, $expiry, "/", "", true, true);

                // Store the token in the database
                $token_sql = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
                if ($token_stmt = $conn->prepare($token_sql)) {
                  $token_stmt->bind_param("isi", $id, $token, $expiry);
                  $token_stmt->execute();
                  $token_stmt->close();
                }
              }

              // Redirect user to dashboard or homepage
              header("location: index.php");
              exit;
            } else {
              // Password is not valid
              $login_err = "Invalid email or password.";
            }
          }
        } else {
          // Email doesn't exist
          $login_err = "Invalid email or password.";
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
  <link href="src/output.css" rel="stylesheet">
  <title>Login - CentralAutogy</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
  <?php include 'includes/navbar.php'; ?>



  <!-- Main Content -->
  <div class="login-card bg-white w-full max-w-5xl flex flex-col md:flex-row mt-16">
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
          <p class="text-white/90 text-lg mb-8 animated-delay-1">Welcome back to your car buying journey</p>
          <div class="space-y-4 text-left animated-delay-2">
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Access your saved vehicles</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Track your order and financing status</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Receive personalized vehicle recommendations</p>
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
          <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
          <p class="text-gray-600 mt-2">Please sign in to your account</p>
        </div>

        <?php if ($registration_success): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">Your account has been created. You can now login.</span>
          </div>
        <?php endif; ?>

        <?php if (!empty($login_err)): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo $login_err; ?></span>
          </div>
        <?php endif; ?>

        <form id="loginForm" class="space-y-6 animated-delay-1" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="email" id="email" name="email" class="form-input w-full px-4 py-3 border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Email Address" value="<?php echo $email; ?>" required>
            <?php if (!empty($email_err)): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></p>
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
            <a href="forgot-password.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
          </div>

          <div>
            <button type="submit" class="login-btn w-full py-3 px-4 rounded-lg text-white font-medium shadow-md flex items-center justify-center">
              <span>Sign In</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <div class="text-center">
            <p class="text-gray-600 text-sm">
              Don't have an account? <a href="register.php" class="text-indigo-600 font-medium hover:text-indigo-500">Register here</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileMenuBtn.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>

</html>