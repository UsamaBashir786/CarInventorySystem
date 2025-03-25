<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - Admin Login</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      height: 100%;
      background-color: #f9fafb;
    }

    .login-card {
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .bg-car {
      background-image: url('https://images.unsplash.com/photo-1614200179396-2bdb77ebf81b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1932&q=80');
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

    .animated-delay-3 {
      opacity: 0;
      animation: fadeIn 0.6s ease-out 0.3s forwards;
    }
  </style>
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

        <form id="loginForm" class="space-y-6 animated-delay-1">
          <div class="input-group">
            <div class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
            <input type="text" id="username" name="username" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none" placeholder="Username or Email" required>
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
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;

      // Basic validation
      if (!username || !password) {
        showToast('Please enter both username and password', 'error');
        return;
      }

      // In a real application, you would verify credentials on the server
      // For demo purposes, just redirect to dashboard
      window.location.href = 'index.php';
    });

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
  </script>
</body>

</html>