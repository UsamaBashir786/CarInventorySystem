<?php
// Include database configuration
require_once 'config/db.php';

// Initialize variables for form data and errors
$firstName = $lastName = $email = $phone = $password = $confirmPassword = "";
$firstNameErr = $lastNameErr = $emailErr = $phoneErr = $passwordErr = $confirmPasswordErr = $termsErr = "";
$registrationSuccess = false;
$registrationError = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate first name
  if (empty($_POST["firstName"])) {
    $firstNameErr = "First name is required";
  } else {
    $firstName = test_input($_POST["firstName"]);
    // Check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/", $firstName)) {
      $firstNameErr = "Only letters and white space allowed";
    }
  }

  // Validate last name
  if (empty($_POST["lastName"])) {
    $lastNameErr = "Last name is required";
  } else {
    $lastName = test_input($_POST["lastName"]);
    // Check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/", $lastName)) {
      $lastNameErr = "Only letters and white space allowed";
    }
  }

  // Validate email
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    } else {
      // Check if email already exists in the database
      $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
        $emailErr = "Email already exists. Please use a different email or login.";
      }
      $stmt->close();
    }
  }

  // Validate phone
  if (empty($_POST["phone"])) {
    $phoneErr = "Phone number is required";
  } else {
    $phone = test_input($_POST["phone"]);
    // Check if phone number is valid (simple validation)
    if (!preg_match("/^[0-9]{10,15}$/", str_replace(['-', '(', ')', ' '], '', $phone))) {
      $phoneErr = "Invalid phone number format";
    }
  }

  // Validate password
  if (empty($_POST["password"])) {
    $passwordErr = "Password is required";
  } else {
    $password = test_input($_POST["password"]);
    // Check if password is strong enough
    if (strlen($password) < 8) {
      $passwordErr = "Password must be at least 8 characters long";
    }
  }

  // Validate confirm password
  if (empty($_POST["confirmPassword"])) {
    $confirmPasswordErr = "Please confirm your password";
  } else {
    $confirmPassword = test_input($_POST["confirmPassword"]);
    // Check if passwords match
    if ($password != $confirmPassword) {
      $confirmPasswordErr = "Passwords don't match";
    }
  }

  // Validate terms agreement
  if (!isset($_POST["terms"]) || $_POST["terms"] != "on") {
    $termsErr = "You must agree to the Terms of Service and Privacy Policy";
  }

  // Check marketing preference
  $marketingPreference = isset($_POST["marketing"]) ? 1 : 0;

  // If no errors, proceed with registration
  if (
    empty($firstNameErr) && empty($lastNameErr) && empty($emailErr) && empty($phoneErr) &&
    empty($passwordErr) && empty($confirmPasswordErr) && empty($termsErr)
  ) {

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Current date for registration timestamp
    $registrationDate = date('Y-m-d H:i:s');

    // Prepare an insert statement
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, marketing_consent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $hashedPassword, $marketingPreference, $registrationDate);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
      $registrationSuccess = true;

      // Optional: Start a session and set session variables
      session_start();
      $_SESSION["user_id"] = $stmt->insert_id;
      $_SESSION["first_name"] = $firstName;
      $_SESSION["last_name"] = $lastName;
      $_SESSION["email"] = $email;

      // Redirect to success page or login page
      header("location: login.php?registered=true");
      exit();
    } else {
      $registrationError = "Something went wrong. Please try again later.";
    }

    // Close statement
    $stmt->close();
  }
}

// Function to sanitize form data
function test_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Include HTML header and content
// The HTML form will be displayed if no submission has occurred or if there were errors
// The success message will be shown if registration was successful
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Register - CentralAutogy</title>
  <link rel="stylesheet" href="assets/css/register.css">
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <div class="register-card bg-white w-full max-w-5xl flex flex-col md:flex-row">
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
          <p class="text-white/90 text-lg mb-8 animated-delay-1">Start your journey with us today</p>
          <div class="space-y-4 text-left animated-delay-2">
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Save your favorite vehicles</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Get exclusive offers and updates</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Streamlined financing application</p>
            </div>
            <div class="flex items-center">
              <div class="bg-white/20 rounded-full p-1.5 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-white/90">Schedule test drives online</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right side - Registration form -->
    <div class="w-full md:w-1/2 py-8 px-4 sm:px-12 flex flex-col justify-center">
      <div class="max-w-md mx-auto w-full">
        <div class="text-center md:text-left mb-6 animated">
          <!-- Logo for mobile view -->
          <div class="flex items-center justify-center md:hidden mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
              <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-800">CentralAutogy</h1>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
          <p class="text-gray-600 mt-2">Join our community of car enthusiasts</p>
        </div>

        <?php if ($registrationError): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo $registrationError; ?></span>
          </div>
        <?php endif; ?>

        <form id="registerForm" class="space-y-4 animated-delay-1" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-group">
              <div class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </div>
              <input type="text" id="firstName" name="firstName" class="form-input w-full px-4 py-3 border <?php echo $firstNameErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="First Name" value="<?php echo $firstName; ?>" required>
              <?php if ($firstNameErr): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $firstNameErr; ?></p>
              <?php endif; ?>
            </div>

            <div class="input-group">
              <div class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </div>
              <input type="text" id="lastName" name="lastName" class="form-input w-full px-4 py-3 border <?php echo $lastNameErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Last Name" value="<?php echo $lastName; ?>" required>
              <?php if ($lastNameErr): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $lastNameErr; ?></p>
              <?php endif; ?>
            </div>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
            </div>
            <input type="email" id="email" name="email" class="form-input w-full px-4 py-3 border <?php echo $emailErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Email Address" value="<?php echo $email; ?>" required>
            <?php if ($emailErr): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $emailErr; ?></p>
            <?php endif; ?>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
              </svg>
            </div>
            <input type="tel" id="phone" name="phone" class="form-input w-full px-4 py-3 border <?php echo $phoneErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Phone Number" value="<?php echo $phone; ?>" required>
            <?php if ($phoneErr): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $phoneErr; ?></p>
            <?php endif; ?>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="password" id="password" name="password" class="form-input w-full px-4 py-3 border <?php echo $passwordErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Password" required>
            <?php if ($passwordErr): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $passwordErr; ?></p>
            <?php endif; ?>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input w-full px-4 py-3 border <?php echo $confirmPasswordErr ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" placeholder="Confirm Password" required>
            <?php if ($confirmPasswordErr): ?>
              <p class="text-red-500 text-xs mt-1"><?php echo $confirmPasswordErr; ?></p>
            <?php endif; ?>
          </div>

          <div class="flex items-start mt-4">
            <input type="checkbox" id="terms" name="terms" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" required>
            <label for="terms" class="ml-2 block text-sm text-gray-600">
              I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a>
            </label>
          </div>
          <?php if ($termsErr): ?>
            <p class="text-red-500 text-xs"><?php echo $termsErr; ?></p>
          <?php endif; ?>

          <div class="flex items-start">
            <input type="checkbox" id="marketing" name="marketing" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label for="marketing" class="ml-2 block text-sm text-gray-600">
              I'd like to receive updates about new inventory, promotions, and car-buying tips
            </label>
          </div>

          <div class="pt-2">
            <button type="submit" class="register-btn w-full py-3 px-4 rounded-lg text-white font-medium shadow-md flex items-center justify-center">
              <span>Create Account</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <div class="text-center">
            <p class="text-gray-600 text-sm">
              Already have an account? <a href="login.php" class="text-indigo-600 font-medium hover:text-indigo-500">Login here</a>
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

    // Client-side password validation (in addition to server-side)
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    function validatePassword() {
      if (password.value != confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords don't match");
      } else {
        confirmPassword.setCustomValidity('');
      }
    }

    password.onchange = validatePassword;
    confirmPassword.onkeyup = validatePassword;

    // Form validation enhancement
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      // Client-side validation is in place
      // The form will be submitted to the server where PHP validation will occur
    });
  </script>
</body>

</html>