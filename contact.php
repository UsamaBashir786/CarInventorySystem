<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Contact Us - CentralAutogy</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body, html {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    .contact-card {
      transition: all 0.3s ease;
      border-radius: 12px;
    }

    .contact-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .bg-gradient {
      background: linear-gradient(to right, #4f46e5, #8b5cf6);
    }
    
    .form-input:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
    
    .map-container {
      height: 450px;
      filter: grayscale(40%);
      border-radius: 12px;
      overflow: hidden;
      z-index: 10;
    }
    
    /* Custom input styles */
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
    
    /* Success message animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .success-message {
      animation: fadeIn 0.6s ease-out forwards;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Top Navigation -->
  <header class="bg-white shadow-sm sticky top-0 z-30">
    <nav class="container mx-auto px-4 py-3">
      <div class="flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
          </svg>
          <h1 class="text-xl font-bold text-gray-800">CentralAutogy</h1>
        </div>
        
        <!-- Navigation links - Desktop -->
        <div class="hidden md:flex items-center space-x-6">
          <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors">Home</a>
          <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors">Inventory</a>
          <a href="about.php" class="text-gray-600 hover:text-indigo-600 transition-colors">About Us</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Financing</a>
          <a href="contact.php" class="text-indigo-600 font-medium">Contact</a>
        </div>
        
        <!-- CTA Buttons -->
        <div class="flex items-center space-x-3">
          <a href="#" class="hidden sm:block text-gray-600 hover:text-indigo-600 transition-colors">
            <div class="flex items-center space-x-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
              </svg>
              <span>Saved</span>
            </div>
          </a>
          <a href="login.php" class="hidden sm:block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm">
            Login
          </a>
          
          <!-- Mobile menu button -->
          <button id="mobileMenuBtn" class="md:hidden text-gray-600 hover:text-indigo-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Mobile menu -->
      <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2">
        <div class="flex flex-col space-y-2">
          <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Home</a>
          <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Inventory</a>
          <a href="about.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">About Us</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Financing</a>
          <a href="contact.php" class="text-indigo-600 font-medium py-2">Contact</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
            </svg>
            Saved Cars
          </a>
          <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors text-center">
            Login
          </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Main Content -->
  <main>
    <!-- Hero Section -->
    <section class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/90 to-purple-900/80 z-10"></div>
      <div class="absolute inset-0 bg-black/40 z-0"></div>
      <div class="relative h-72 bg-[url('https://images.pexels.com/photos/821754/pexels-photo-821754.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2')] bg-cover bg-center z-0">
        <div class="container mx-auto px-4 h-full flex flex-col justify-center z-20 relative">
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">Contact Us</h1>
          <p class="text-xl text-white/90 max-w-2xl">We're here to help with any questions about our vehicles or services. Reach out to our team today.</p>
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
              1234 Auto Lane<br>
              Car City, ST 12345<br>
              United States
            </p>
            <a href="https://maps.google.com" target="_blank" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors inline-flex items-center">
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
              Main Office: (555) 123-4567<br>
              Sales Department: (555) 123-8901<br>
              Customer Service: (555) 123-5678
            </p>
            <a href="tel:+15551234567" class="text-indigo-600 font-medium hover:text-indigo-700 transition-colors inline-flex items-center">
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
                  <span class="text-gray-600">Closed</span>
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
            <form id="contactForm" class="space-y-6">
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
                    <input type="text" id="firstName" name="firstName" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" required>
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
                    <input type="text" id="lastName" name="lastName" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" required>
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
                  <input type="email" id="email" name="email" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none" required>
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
                  <input type="tel" id="phone" name="phone" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
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
                  <select id="subject" name="subject" class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none appearance-none" required>
                    <option value="">Select a subject</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Vehicle Information">Vehicle Information</option>
                    <option value="Test Drive Request">Test Drive Request</option>
                    <option value="Financing Options">Financing Options</option>
                    <option value="Service & Maintenance">Service & Maintenance</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                <textarea id="message" name="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required></textarea>
              </div>
              
              <div class="flex items-start">
                <input type="checkbox" id="privacy" name="privacy" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" required>
                <label for="privacy" class="ml-2 block text-sm text-gray-600">
                  I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-700">Privacy Policy</a> and consent to having this website store my submitted information.
                </label>
              </div>
              
              <div>
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors shadow-md flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                  </svg>
                  Send Message
                </button>
              </div>
            </form>
            
            <!-- Success Message (hidden by default) -->
            <div id="successMessage" class="hidden text-center p-6 success-message">
              <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-2">Thank You!</h3>
              <p class="text-gray-600">Your message has been sent successfully. We'll get back to you as soon as possible.</p>
              <button id="sendAnotherBtn" class="mt-4 text-indigo-600 font-medium hover:text-indigo-700 transition-colors">Send Another Message</button>
            </div>
          </div>
          
          <!-- Map -->
          <div class="map-container shadow-sm">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215