<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Toyota Camry 2022 - CentralAutogy</title>
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
          <a href="index.php" class="text-indigo-600 font-medium">Inventory</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">About Us</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Financing</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">Contact</a>
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
          <a href="index.php" class="text-indigo-600 font-medium py-2">Inventory</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">About Us</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Financing</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Contact</a>
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
              <span class="text-gray-500 text-sm">Toyota Camry 2022</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Car Details Header -->
    <div class="flex flex-col md:flex-row justify-between items-start mb-6">
      <div>
        <div class="mb-1 flex items-center">
          <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium mr-2">Available</span>
          <span class="text-gray-500 text-sm">Stock #: TC22-12450</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Toyota Camry (2022)</h1>
        <p class="text-gray-600">LE Sedan 4D • Pearl White • Black Interior</p>
      </div>
      <div class="mt-4 md:mt-0">
        <div class="text-2xl md:text-3xl font-bold text-indigo-600">$24,990</div>
        <div class="text-gray-500 text-sm">Est. $450/month*</div>
      </div>
    </div>

    <!-- Car Gallery & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-12">
      <!-- Gallery - 3 cols wide on lg -->
      <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
          <!-- Main Image -->
          <div class="relative aspect-video">
            <img id="mainImage" src="https://images.unsplash.com/photo-1550355291-bbee04a92027?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=1200&q=80" alt="Toyota Camry" class="w-full h-full object-cover">
          </div>

          <!-- Thumbnails -->
          <div class="grid grid-cols-5 gap-2 p-2">
            <img src="https://images.unsplash.com/photo-1550355291-bbee04a92027?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=300&q=80" alt="Toyota Camry Front" class="gallery-thumb active aspect-video object-cover rounded">
            <img src="https://images.unsplash.com/photo-1632245889029-da8fefe6dcf8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fHRveW90YSUyMGNhbXJ5fGVufDB8fDB8fHww&auto=format&fit=crop&w=300&q=80" alt="Toyota Camry Interior" class="gallery-thumb aspect-video object-cover rounded">
            <img src="https://images.unsplash.com/photo-1620674161452-0a97f0a75734?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHRveW90YSUyMGNhbXJ5fGVufDB8fDB8fHww&auto=format&fit=crop&w=300&q=80" alt="Toyota Camry Rear" class="gallery-thumb aspect-video object-cover rounded">
            <img src="https://images.unsplash.com/photo-1567899806688-d0a624ec7bdd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTZ8fHRveW90YSUyMGNhbXJ5fGVufDB8fDB8fHww&auto=format&fit=crop&w=300&q=80" alt="Toyota Camry Side" class="gallery-thumb aspect-video object-cover rounded">
            <img src="https://images.unsplash.com/photo-1592803816834-15690445fe6d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fHRveW90YSUyMGNhbXJ5JTIwaW50ZXJpb3J8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=300&q=80" alt="Toyota Camry Dashboard" class="gallery-thumb aspect-video object-cover rounded">
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
              <p class="text-gray-700 mb-4">This 2022 Toyota Camry LE is in excellent condition with only 12,450 miles. It features a fuel-efficient 2.5L 4-cylinder engine paired with an 8-speed automatic transmission, providing a perfect balance of performance and efficiency.</p>

              <p class="text-gray-700 mb-4">The exterior features a sleek Pearl White finish that's sure to turn heads, while the Black interior provides a comfortable and stylish cabin space. This Camry comes equipped with Toyota's Safety Sense package, including lane departure alert, dynamic radar cruise control, and pre-collision system.</p>

              <p class="text-gray-700 mb-4">Perfect for daily commuting or family trips, this Toyota Camry offers reliability, comfort, and modern technology at an affordable price. Don't miss the opportunity to own one of America's most trusted sedans.</p>

              <p class="text-gray-700 mb-4">Clean title, one owner, and detailed service history available. Schedule a test drive today!</p>
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
                      Dual-Zone Automatic Climate Control
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      7-inch Touchscreen Infotainment System
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Apple CarPlay & Android Auto
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Fabric-Trimmed Seats
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Power Driver's Seat with Lumbar Support
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Bluetooth Connectivity
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
                      Toyota Safety Sense 2.0
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Pre-Collision System with Pedestrian Detection
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Lane Departure Alert with Steering Assist
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Dynamic Radar Cruise Control
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Automatic High Beams
                    </li>
                    <li class="flex items-center text-gray-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Backup Camera
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
                        <td class="py-2 text-gray-800 font-medium">2.5L 4-Cylinder</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Horsepower</td>
                        <td class="py-2 text-gray-800 font-medium">203 hp @ 6,600 rpm</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Torque</td>
                        <td class="py-2 text-gray-800 font-medium">184 lb-ft @ 5,000 rpm</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Transmission</td>
                        <td class="py-2 text-gray-800 font-medium">8-Speed Automatic</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Drive Type</td>
                        <td class="py-2 text-gray-800 font-medium">Front-Wheel Drive</td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Fuel Economy</td>
                        <td class="py-2 text-gray-800 font-medium">28 City / 39 Highway</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div>
                  <h3 class="font-medium text-gray-800 mb-3">Dimensions & Capacity</h3>
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Length</td>
                        <td class="py-2 text-gray-800 font-medium">192.1 inches</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Width</td>
                        <td class="py-2 text-gray-800 font-medium">72.4 inches</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Height</td>
                        <td class="py-2 text-gray-800 font-medium">56.9 inches</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Wheelbase</td>
                        <td class="py-2 text-gray-800 font-medium">111.2 inches</td>
                      </tr>
                      <tr class="border-b">
                        <td class="py-2 text-gray-600">Seating Capacity</td>
                        <td class="py-2 text-gray-800 font-medium">5 Passengers</td>
                      </tr>
                      <tr>
                        <td class="py-2 text-gray-600">Cargo Volume</td>
                        <td class="py-2 text-gray-800 font-medium">15.1 cubic feet</td>
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
            <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition-colors text-center shadow-sm flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
              </svg>
              Contact Seller
            </a>
            <a href="#" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
              </svg>
              Save Vehicle
            </a>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <a href="#" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
              </svg>
              Share
            </a>
            <a href="#" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors text-center flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              Download Brochure
            </a>
          </div>
        </div>

        <!-- Financing Calculator -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <h3 class="font-semibold text-gray-800 text-lg mb-4">Estimate Your Payment</h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Price</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500">$</span>
                </div>
                <input type="number" id="vehiclePrice" value="24990" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Down Payment</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500">$</span>
                </div>
                <input type="number" id="downPayment" value="3000" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Term (Months)</label>
              <select id="term" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="36">36 Months</option>
                <option value="48">48 Months</option>
                <option value="60" selected>60 Months</option>
                <option value="72">72 Months</option>
                <option value="84">84 Months</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%)</label>
              <input type="number" id="interestRate" value="4.5" step="0.1" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="pt-4 border-t border-gray-200">
              <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Estimated monthly payment:</span>
                <span id="monthlyPayment" class="text-xl font-semibold text-indigo-600">$412</span>
              </div>
              <p class="text-xs text-gray-500">
                *This is only an estimate. Contact us for accurate financing options based on your credit score and qualifications.
              </p>
            </div>
          </div>
        </div>

        <!-- Quick Specs -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <h3 class="font-semibold text-gray-800 text-lg mb-4">Quick Overview</h3>

          <div class="grid grid-cols-2 gap-4">
            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Mileage</div>
              <div class="font-medium text-gray-800">12,450 mi</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Year</div>
              <div class="font-medium text-gray-800">2022</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Engine</div>
              <div class="font-medium text-gray-800">2.5L 4-Cylinder</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Transmission</div>
              <div class="font-medium text-gray-800">8-Speed Auto</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Fuel Type</div>
              <div class="font-medium text-gray-800">Gasoline</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Drive Type</div>
              <div class="font-medium text-gray-800">FWD</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Ext. Color</div>
              <div class="font-medium text-gray-800">Pearl White</div>
            </div>

            <div class="feature-card bg-gray-50 p-3 rounded-lg">
              <div class="text-xs text-gray-500 mb-1">Int. Color</div>
              <div class="font-medium text-gray-800">Black</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Similar Cars -->
    <div class="mb-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Similar Vehicles</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Similar Car 1 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="relative">
            <img src="https://images.unsplash.com/photo-1583267746897-2cf415887172?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8YXVkaSUyMGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=600&q=60" alt="Audi A4" class="w-full h-48 object-cover">
          </div>
          <div class="p-4">
            <div class="mb-1">
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
            </div>
            <h3 class="font-semibold text-gray-800">Audi A4</h3>
            <p class="text-gray-600 text-sm mb-2">2022 · 7,200 mi · Gasoline</p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-indigo-600 font-semibold text-lg">$42,100</p>
              </div>
              <a href="#" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View Details</a>
            </div>
          </div>
        </div>

        <!-- Similar Car 2 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="relative">
            <img src="https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fGhvbmRhJTIwYWNjb3JkfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Honda Accord" class="w-full h-48 object-cover">
          </div>
          <div class="p-4">
            <div class="mb-1">
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
            </div>
            <h3 class="font-semibold text-gray-800">Honda Accord</h3>
            <p class="text-gray-600 text-sm mb-2">2021 · 22,100 mi · Gasoline</p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-indigo-600 font-semibold text-lg">$26,900</p>
              </div>
              <a href="#" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View Details</a>
            </div>
          </div>
        </div>

        <!-- Similar Car 3 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="relative">
            <img src="https://images.unsplash.com/photo-1619767886558-efdc259cde1a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8bmlzc2FuJTIwYWx0aW1hfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Nissan Altima" class="w-full h-48 object-cover">
          </div>
          <div class="p-4">
            <div class="mb-1">
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
            </div>
            <h3 class="font-semibold text-gray-800">Nissan Altima</h3>
            <p class="text-gray-600 text-sm mb-2">2022 · 15,700 mi · Gasoline</p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-indigo-600 font-semibold text-lg">$23,450</p>
              </div>
              <a href="#" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View Details</a>
            </div>
          </div>
        </div>

        <!-- Similar Car 4 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="relative">
            <img src="https://images.unsplash.com/photo-1580273916550-e323be2ae537?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTV8fG1hemRhfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Mazda 6" class="w-full h-48 object-cover">
          </div>
          <div class="p-4">
            <div class="mb-1">
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
            </div>
            <h3 class="font-semibold text-gray-800">Mazda 6</h3>
            <p class="text-gray-600 text-sm mb-2">2021 · 18,200 mi · Gasoline</p>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-indigo-600 font-semibold text-lg">$25,300</p>
              </div>
              <a href="#" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View Details</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white pt-12 pb-8">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Company Info -->
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
              <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
              <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
            </svg>
            <h3 class="text-lg font-bold">CentralAutogy</h3>
          </div>
          <p class="text-gray-400 mb-4">Your one-stop destination for finding the perfect vehicle. We provide a wide selection of high-quality cars at competitive prices.</p>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Browse Inventory</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Financing Options</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div>
          <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
          <ul class="space-y-3">
            <li class="flex items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-400">1234 Auto Lane, Car City, ST 12345</span>
            </li>
            <li class="flex items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
              </svg>
              <span class="text-gray-400">(555) 123-4567</span>
            </li>
            <li class="flex items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
              <span class="text-gray-400">info@centralautogy.com</span>
            </li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div>
          <h4 class="text-lg font-semibold mb-4">Newsletter</h4>
          <p class="text-gray-400 mb-4">Subscribe to our newsletter for the latest updates on new inventory and special offers.</p>
          <form class="mb-2">
            <div class="flex">
              <input type="email" placeholder="Your email address" class="px-4 py-2 w-full rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
              <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-r-lg text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="border-t border-gray-700 mt-10 pt-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm">&copy; 2023 CentralAutogy. All rights reserved.</p>
          <div class="mt-4 md:mt-0">
            <div class="flex space-x-4">
              <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
              <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileMenuBtn.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });

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
        mainImage.src = this.src.replace('w=300', 'w=1200');
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

    // Financing Calculator
    const vehiclePrice = document.getElementById('vehiclePrice');
    const downPayment = document.getElementById('downPayment');
    const term = document.getElementById('term');
    const interestRate = document.getElementById('interestRate');
    const monthlyPayment = document.getElementById('monthlyPayment');

    function calculatePayment() {
      const principal = parseFloat(vehiclePrice.value) - parseFloat(downPayment.value);
      const monthlyRate = parseFloat(interestRate.value) / 100 / 12;
      const numberOfPayments = parseInt(term.value);

      // Monthly payment formula: P * (r(1+r)^n) / ((1+r)^n - 1)
      if (monthlyRate === 0) {
        // If interest rate is 0, simple division
        const payment = principal / numberOfPayments;
        monthlyPayment.textContent = '$' + payment.toFixed(0);
      } else {
        const x = Math.pow(1 + monthlyRate, numberOfPayments);
        const payment = (principal * x * monthlyRate) / (x - 1);
        monthlyPayment.textContent = '$' + payment.toFixed(0);
      }
    }

    // Calculate payment when inputs change
    vehiclePrice.addEventListener('input', calculatePayment);
    downPayment.addEventListener('input', calculatePayment);
    term.addEventListener('change', calculatePayment);
    interestRate.addEventListener('input', calculatePayment);

    // Calculate initial payment
    calculatePayment();
  </script>
</body>

</html>