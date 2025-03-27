<?php
session_start();
// Include database configuration
require_once 'config/db.php';

// Define variables and initialize with empty values
$firstName = $lastName = $email = $phone = $subject = $message = "";
$firstName_err = $lastName_err = $email_err = $subject_err = $message_err = "";
$form_success = false;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Validate first name
  if (empty(trim($_POST["firstName"]))) {
    $firstName_err = "Please enter your first name.";
  } else {
    $firstName = trim($_POST["firstName"]);
    // Check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/", $firstName)) {
      $firstName_err = "Only letters and white space allowed.";
    }
  }

  // Validate last name
  if (empty(trim($_POST["lastName"]))) {
    $lastName_err = "Please enter your last name.";
  } else {
    $lastName = trim($_POST["lastName"]);
    // Check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/", $lastName)) {
      $lastName_err = "Only letters and white space allowed.";
    }
  }

  // Validate email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter your email.";
  } else {
    $email = trim($_POST["email"]);
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email_err = "Invalid email format.";
    }
  }

  // Get phone (optional)
  $phone = !empty($_POST["phone"]) ? trim($_POST["phone"]) : "";

  // Validate subject
  if (empty(trim($_POST["subject"]))) {
    $subject_err = "Please select a subject.";
  } else {
    $subject = trim($_POST["subject"]);
  }

  // Validate message
  if (empty(trim($_POST["message"]))) {
    $message_err = "Please enter your message.";
  } else {
    $message = trim($_POST["message"]);
  }

  // Check privacy checkbox
  if (!isset($_POST["privacy"]) || $_POST["privacy"] != "on") {
    $privacy_err = "You must agree to the Privacy Policy.";
  }

  // Check input errors before inserting in database
  if (
    empty($firstName_err) && empty($lastName_err) && empty($email_err) &&
    empty($subject_err) && empty($message_err) && empty($privacy_err)
  ) {

    // Get user ID if logged in
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

    // Prepare an insert statement
    $sql = "INSERT INTO inquiries (user_id, name, email, phone, message, inquiry_type, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())";

    if ($stmt = $conn->prepare($sql)) {
      // Create full name
      $name = $firstName . " " . $lastName;

      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $message, $subject);

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        $form_success = true;

        // Send notification email to admin
        $to = "info@centralautogy.com";
        $email_subject = "New Contact Form Submission: $subject";
        $email_message = "Name: $name\n";
        $email_message .= "Email: $email\n";
        $email_message .= "Phone: $phone\n";
        $email_message .= "Subject: $subject\n\n";
        $email_message .= "Message:\n$message\n";

        $headers = "From: noreply@centralautogy.com\r\n";
        $headers .= "Reply-To: $email\r\n";

        // Disable mail() for security in this sample code
        // mail($to, $email_subject, $email_message, $headers);

        // Clear form fields
        $firstName = $lastName = $email = $phone = $subject = $message = "";
      } else {
        echo "Oops! Something went wrong. Please try again later.";
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
  <title>Contact Us - CentralAutogy</title>
  <link rel="stylesheet" href="assets/css/contact.css">
  <style>
    .input-icon {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
    }

    .form-input {
      transition: all 0.3s;
      padding-left: 2.5rem;
    }

    .map-container {
      height: 450px;
      border-radius: 0.75rem;
      overflow: hidden;
    }

    .success-message {
      animation: fadeIn 0.5s ease-out forwards;
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
  </style>
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>


  <!-- Main Content -->
  <main>
    <!-- Hero Section for Contact Page -->
    <section class="relative h-72 overflow-hidden">
      <!-- Background image div -->
      <div class="absolute inset-0">
        <img
          src="https://images.pexels.com/photos/821754/pexels-photo-821754.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2"
          alt="Contact us background"
          class="w-full h-full object-cover" />
      </div>

      <!-- Purple overlay as a separate div -->
      <div class="absolute inset-0 bg-indigo-900 opacity-80"></div>

      <!-- Content container -->
      <div class="relative container mx-auto px-4 h-full flex flex-col justify-center">
        <div class="text-white max-w-2xl">
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 drop-shadow-lg">
            Contact Us
          </h1>
          <p class="text-lg md:text-xl text-white/90 drop-shadow">
            We're here to help with any questions about our vehicles or services. Reach out to our team today.
          </p>
        </div>
      </div>
    </section>
    <!-- Contact Info Cards -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Visit Us Card -->
          <div class="contact-card bg-gray-50 p-8 text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Visit Us</h3>
            <p class="text-gray-600 mb-4">
              123 Central Avenue<br>
              Autogy City, CA 90210<br>
              United States
            </p>
            <a href="https://maps.google.com/?q=123+Central+Avenue+Autogy+City+CA+90210" target="_blank" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors inline-flex items-center">
              <span>Get Directions</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>

          <!-- Call Us Card -->
          <div class="contact-card bg-gray-50 p-8 text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Call Us</h3>
            <p class="text-gray-600 mb-4">
              Main Office: (800) 123-4567<br>
              Sales Department: (800) 123-8910<br>
              Customer Service: (800) 123-5678
            </p>
            <a href="tel:+18001234567" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors inline-flex items-center">
              <span>Call Now</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>

          <!-- Email Us Card -->
          <div class="contact-card bg-gray-50 p-8 text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Email Us</h3>
            <p class="text-gray-600 mb-4">
              General Inquiries: info@centralautogy.com<br>
              Sales Department: sales@centralautogy.com<br>
              Customer Support: support@centralautogy.com
            </p>
            <a href="mailto:info@centralautogy.com" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors inline-flex items-center">
              <span>Send Email</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Business Hours -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Business Hours</h2>
          <p class="text-gray-600 max-w-3xl mx-auto">Visit us during the following hours or schedule an appointment for a personalized experience.</p>
        </div>

        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="p-6 md:p-8">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Sales Department</h3>
              <ul class="space-y-3">
                <li class="flex justify-between items-center pb-2 border-b border-gray-100">
                  <span class="text-gray-700">Monday - Friday</span>
                  <span class="text-gray-600">9:00 AM - 8:00 PM</span>
                </li>
                <li class="flex justify-between items-center pb-2 border-b border-gray-100">
                  <span class="text-gray-700">Saturday</span>
                  <span class="text-gray-600">9:00 AM - 6:00 PM</span>
                </li>
                <li class="flex justify-between items-center">
                  <span class="text-gray-700">Sunday</span>
                  <span class="text-gray-600">10:00 AM - 4:00 PM</span>
                </li>
              </ul>
            </div>

            <div class="p-6 md:p-8 bg-gray-50">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Service Center</h3>
              <ul class="space-y-3">
                <li class="flex justify-between items-center pb-2 border-b border-gray-100">
                  <span class="text-gray-700">Monday - Friday</span>
                  <span class="text-gray-600">8:00 AM - 6:00 PM</span>
                </li>
                <li class="flex justify-between items-center pb-2 border-b border-gray-100">
                  <span class="text-gray-700">Saturday</span>
                  <span class="text-gray-600">8:00 AM - 4:00 PM</span>
                </li>
                <li class="flex justify-between items-center">
                  <span class="text-gray-700">Sunday</span>
                  <span class="text-gray-600">Closed</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="bg-indigo-50 p-6 text-center">
            <p class="text-indigo-800">
              <span class="font-medium">Note:</span> Extended hours are available by appointment. Contact us to schedule a visit outside regular business hours.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form & Map -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Get in Touch</h2>
          <p class="text-gray-600 max-w-3xl mx-auto">Have a question or interested in a specific vehicle? Fill out the form below and our team will get back to you as soon as possible.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
          <!-- Contact Form -->
          <div class="bg-gray-50 rounded-xl p-6 md:p-8 shadow-sm">
            <?php if (!$form_success): ?>
              <form id="contactForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Send Us a Message</h3>
                <p class="text-gray-600 text-sm mb-6">All fields marked with an asterisk (*) are required</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <div class="relative">
                      <div class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <input type="text" id="firstName" name="firstName" value="<?php echo $firstName; ?>" class="form-input w-full px-4 py-2 border <?php echo (!empty($firstName_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" required>
                      <?php if (!empty($firstName_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $firstName_err; ?></p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <div class="relative">
                      <div class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <input type="text" id="lastName" name="lastName" value="<?php echo $lastName; ?>" class="form-input w-full px-4 py-2 border <?php echo (!empty($lastName_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" required>
                      <?php if (!empty($lastName_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $lastName_err; ?></p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                  <div class="relative">
                    <div class="input-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                      </svg>
                    </div>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="form-input w-full px-4 py-2 border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none" required>
                    <?php if (!empty($email_err)): ?>
                      <p class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></p>
                    <?php endif; ?>
                  </div>
                </div>

                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <div class="relative">
                    <div class="input-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
                      </svg>
                    </div>
                    <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
                  </div>
                </div>

                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                  <div class="relative">
                    <div class="input-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <select id="subject" name="subject" class="form-input w-full px-4 py-2 border <?php echo (!empty($subject_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none appearance-none" required>
                      <option value="" <?php echo empty($subject) ? 'selected' : ''; ?>>Select a subject</option>
                      <option value="General Inquiry" <?php echo ($subject == "General Inquiry") ? 'selected' : ''; ?>>General Inquiry</option>
                      <option value="Vehicle Information" <?php echo ($subject == "Vehicle Information") ? 'selected' : ''; ?>>Vehicle Information</option>
                      <option value="Other" <?php echo ($subject == "Other") ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <?php if (!empty($subject_err)): ?>
                      <p class="text-red-500 text-xs mt-1"><?php echo $subject_err; ?></p>
                    <?php endif; ?>
                  </div>
                </div>

                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                  <textarea id="message" name="message" rows="4" class="w-full px-4 py-2 border <?php echo (!empty($message_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required><?php echo $message; ?></textarea>
                  <?php if (!empty($message_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $message_err; ?></p>
                  <?php endif; ?>
                </div>

                <div class="flex items-start">
                  <input type="checkbox" id="privacy" name="privacy" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" required>
                  <label for="privacy" class="ml-2 block text-sm text-gray-600">
                    I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-700">Privacy Policy</a> and consent to having this website store my submitted information.
                  </label>
                </div>
                <?php if (!empty($privacy_err)): ?>
                  <p class="text-red-500 text-xs -mt-4"><?php echo $privacy_err; ?></p>
                <?php endif; ?>

                <div>
                  <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors shadow-md flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                    Send Message
                  </button>
                </div>
              </form>
            <?php else: ?>
              <!-- Success Message -->
              <div id="successMessage" class="text-center p-6 success-message">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Thank You!</h3>
                <p class="text-gray-600">Your message has been sent successfully. We'll get back to you as soon as possible.</p>
                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mt-4 inline-block text-indigo-600 font-medium hover:text-indigo-700 transition-colors">Send Another Message</a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Map -->
          <div class="map-container shadow-sm">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3305.7152203583255!2d-118.35598248478066!3d34.0671798806063!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2b9233c8c8ccf%3A0x9effcd317e2c6aca!2s123%20Central%20Ave%2C%20Los%20Angeles%2C%20CA%2090012!5e0!3m2!1sen!2sus!4v1647367886021!5m2!1sen!2sus" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
          <p class="text-gray-600 max-w-3xl mx-auto">Find answers to common questions about our services, financing options, and buying process.</p>
        </div>

        <div class="max-w-4xl mx-auto">
          <!-- Question 1 -->
          <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <button class="faq-toggle w-full flex justify-between items-center p-6 text-left">
              <h3 class="font-medium text-gray-800 text-lg">What do I need to bring when purchasing a vehicle?</h3>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 faq-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div class="faq-content hidden px-6 pb-6">
              <p class="text-gray-600">When purchasing a vehicle, please bring the following items:</p>
              <ul class="list-disc pl-5 mt-2 text-gray-600 space-y-1">
                <li>Valid driver's license</li>
                <li>Proof of insurance</li>
                <li>Method of payment (cash, pre-approved loan, etc.)</li>
                <li>Trade-in vehicle (if applicable) along with its title and registration</li>
                <li>Down payment (if required by your financing arrangement)</li>
              </ul>
            </div>
          </div>

          <!-- Question 2 -->
          <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <button class="faq-toggle w-full flex justify-between items-center p-6 text-left">
              <h3 class="font-medium text-gray-800 text-lg">Do you offer financing options?</h3>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 faq-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div class="faq-content hidden px-6 pb-6">
              <p class="text-gray-600">Yes, we offer various financing options to help you purchase your vehicle. We work with multiple financial institutions to ensure you get the best possible rate and terms. Our finance team can help you explore options based on your credit situation, down payment amount, and budget. We offer both traditional auto loans and lease options for qualified buyers.</p>
            </div>
          </div>

          <!-- Question 3 -->
          <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <button class="faq-toggle w-full flex justify-between items-center p-6 text-left">
              <h3 class="font-medium text-gray-800 text-lg">Can I schedule a test drive online?</h3>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 faq-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div class="faq-content hidden px-6 pb-6">
              <p class="text-gray-600">Absolutely! You can schedule a test drive online by visiting our inventory page, selecting the vehicle you're interested in, and clicking the "Schedule Test Drive" button. You can also contact us directly through the form on this page or call our sales department to arrange a convenient time. We recommend scheduling at least 24 hours in advance to ensure the vehicle is ready for you when you arrive.</p>
            </div>
          </div>

          <!-- Question 4 -->
          <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <button class="faq-toggle w-full flex justify-between items-center p-6 text-left">
              <h3 class="font-medium text-gray-800 text-lg">What warranty coverage do your vehicles have?</h3>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 faq-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div class="faq-content hidden px-6 pb-6">
              <p class="text-gray-600">Warranty coverage varies depending on the vehicle:</p>
              <ul class="list-disc pl-5 mt-2 text-gray-600 space-y-1">
                <li>New vehicles typically come with the manufacturer's warranty</li>
                <li>Most of our pre-owned vehicles come with a limited warranty</li>
                <li>Certified pre-owned vehicles include extended warranty coverage</li>
                <li>Additional warranty and protection plans are available for purchase</li>
              </ul>
              <p class="mt-2 text-gray-600">Our sales team can provide specific warranty information for any vehicle you're interested in purchasing.</p>
            </div>
          </div>

          <!-- Question 5 -->
          <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
            <button class="faq-toggle w-full flex justify-between items-center p-6 text-left">
              <h3 class="font-medium text-gray-800 text-lg">Do you buy used vehicles?</h3>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 faq-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div class="faq-content hidden px-6 pb-6">
              <p class="text-gray-600">Yes, we purchase quality used vehicles. If you're looking to sell your car, you can bring it to our dealership for an appraisal. Our team will evaluate your vehicle and make you a competitive offer based on its condition, age, mileage, and market value. We also accept trade-ins toward the purchase of another vehicle, which may offer tax advantages in some situations.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script>
    // FAQ toggles
    const faqToggles = document.querySelectorAll('.faq-toggle');

    faqToggles.forEach(toggle => {
      toggle.addEventListener('click', function() {
        const content = this.nextElementSibling;
        const icon = this.querySelector('.faq-icon');

        content.classList.toggle('hidden');

        // Rotate icon when expanded
        if (content.classList.contains('hidden')) {
          icon.style.transform = 'rotate(0deg)';
        } else {
          icon.style.transform = 'rotate(180deg)';
        }
      });
    });
  </script>
</body>

</html>