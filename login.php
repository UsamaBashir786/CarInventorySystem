<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Login - CentralAutogy</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      height: 100%;
      background-color: #f9fafb;
    }

    .login-card {
      margin-top: 250px;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .bg-car {
      background-image: url('https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
      background-size: cover;
      background-position: center;
    }

    .input-group {
      position: relative;
    }

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

    .form-input:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    .login-btn {
      background: linear-gradient(to right, #4f46e5, #7e22ce);
      transition: all 0.3s;
    }

    .login-btn:hover {
      background: linear-gradient(to right, #4338ca, #6b21a8);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
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

    .animated {
      animation: fadeIn 0.6s ease-out forwards;
    }

    .animated-delay-1 {
      opacity: 0;
      animation: fadeIn 0.6s ease-out 0.1s forwards;
    }

    .animated-delay-2 {
      opacity: 0;
      animation: fadeIn 0.6s ease-out 0.2s forwards;
    }
  </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
  <!-- Top Navigation -->
  <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-30">
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
          <a href="contact.php" class="text-gray-600 hover:text-indigo-600 transition-colors">Contact</a>
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
          <a href="contact.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Contact</a>
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

        <form id="loginForm" class="space-y-6 animated-delay-1">
          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="email" id="email" name="email" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none" placeholder="Email Address" required>
          </div>

          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="password" id="password" name="password" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none" placeholder="Password" required>
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

          <div class="text-center">
            <p class="text-gray-600 text-sm">
              Don't have an account? <a href="register.php" class="text-indigo-600 font-medium hover:text-indigo-500">Register here</a>
            </p>
          </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200 animated-delay-2">
          <p class="text-center text-sm text-gray-600 mb-4">Or continue with</p>
          <div class="grid grid-cols-2 gap-4">
            <button class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
              </svg>
              <span class="text-gray-700 font-medium text-sm">Google</span>
            </button>
            <button class="flex items-center justify-center py-2.5 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M22 12c0-5.523-4.477-10-10-10s-10 4.477-10 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54v-2.891h2.54v-2.203c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.875h2.773l-.443 2.891h-2.33v6.988C18.343 21.128 22 16.991 22 12z" fill="#1877F2" />
              </svg>
              <span class="text-gray-700 font-medium text-sm">Facebook</span>
            </button>
          </div>
        </div>
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

    // Login form submission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();

      // Get form data
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      // In a real application, you would send this data to the server for authentication
      // For demo purposes, redirect to the home page
      console.log('Login attempt with:', {
        email,
        password
      });

      // Redirect to the index page (or dashboard if you have one)
      window.location.href = 'index.php';
    });
  </script>
</body>

</html>