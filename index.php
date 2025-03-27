<?php 
session_start();

?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>CentralAutogy - Find Your Perfect Car</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    .car-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
      height: 100%;
    }

    .car-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .car-image {
      height: 200px;
      object-fit: cover;
      width: 100%;
    }

    .list-view .car-card {
      display: flex;
      flex-direction: row;
      height: auto;
    }

    .list-view .car-image {
      width: 280px;
      height: 100%;
      min-height: 160px;
    }

    .filter-card {
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .range-slider {
      -webkit-appearance: none;
      width: 100%;
      height: 6px;
      border-radius: 5px;
      background: #e5e7eb;
      outline: none;
    }

    .range-slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #4f46e5;
      cursor: pointer;
    }

    .range-slider::-moz-range-thumb {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #4f46e5;
      cursor: pointer;
      border: none;
    }

    .view-btn.active {
      background-color: #4f46e5;
      color: white;
    }

    /* For mobile filters */
    .mobile-filter-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 40;
      display: none;
    }

    .mobile-filter-drawer {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 85%;
      max-width: 320px;
      background-color: white;
      z-index: 50;
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
      overflow-y: auto;
    }

    .mobile-filter-overlay.open {
      display: block;
    }

    .mobile-filter-drawer.open {
      transform: translateX(0);
    }
  </style>
</head>

<body class="bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-6">
    <!-- Hero Section -->
    <div class="mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 md:p-8 lg:p-10 text-white shadow-lg">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-3">Find Your Perfect Car</h1>
        <p class="text-white/90 mb-6 max-w-2xl">Browse our extensive collection of high-quality vehicles. Use our advanced search to find exactly what you're looking for.</p>

        <!-- Search bar -->
        <div class="flex flex-col sm:flex-row gap-3 mb-2">
          <div class="flex-grow">
            <input type="text" placeholder="Search by make, model, or features..." class="w-full px-4 py-3 border border-gray-300 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
          </div>
          <button class="bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-medium transition-colors whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
            Search
          </button>
        </div>

        <!-- Popular searches -->
        <div class="flex flex-wrap gap-2 mt-4">
          <span class="text-xs text-white/80">Popular:</span>
          <a href="#" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">Toyota</a>
          <a href="#" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">Honda Civic</a>
          <a href="#" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">Tesla</a>
          <a href="#" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">SUVs</a>
          <a href="#" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">Electric</a>
        </div>
      </div>
    </div>

    <!-- Mobile filter toggle -->
    <div class="lg:hidden mb-6">
      <button id="mobileFilterBtn" class="w-full bg-white shadow-sm border border-gray-200 text-gray-700 px-4 py-3 rounded-lg flex items-center justify-center space-x-2 hover:bg-gray-50 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
        </svg>
        <span>Filters (25 cars found)</span>
      </button>
    </div>

    <!-- Content Area (Sidebar + Cars) -->
    <div class="flex flex-wrap lg:flex-nowrap gap-6">
      <!-- Sidebar Filters (Desktop) -->
      <div class="hidden lg:block w-full lg:w-1/4 xl:w-1/5">
        <div class="bg-white rounded-xl shadow-sm p-6 filter-card sticky top-24">
          <div class="mb-6">
            <h3 class="font-semibold text-gray-800 text-lg mb-4">Filters</h3>
            <div class="flex justify-between items-center mb-1">
              <span class="text-gray-600 text-sm">25 cars found</span>
              <button class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Clear All</button>
            </div>
          </div>

          <!-- Price Range -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-800 mb-3">Price Range</h4>
            <div class="mb-4">
              <input type="range" min="0" max="100000" value="50000" class="range-slider" id="priceRange">
              <div class="flex justify-between mt-2 text-sm text-gray-600">
                <span>$0</span>
                <span id="priceValue">$50,000</span>
                <span>$100k+</span>
              </div>
            </div>
          </div>

          <!-- Make -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-800 mb-3">Make</h4>
            <div class="space-y-2 max-h-48 overflow-y-auto">
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Toyota (8)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Honda (5)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Ford (4)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Tesla (3)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">BMW (3)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Mercedes-Benz (2)</span>
              </label>
            </div>
          </div>

          <!-- Body Type -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-800 mb-3">Body Type</h4>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Sedan (10)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">SUV (8)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Truck (4)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Coupe (2)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Hatchback (1)</span>
              </label>
            </div>
          </div>

          <!-- Fuel Type -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-800 mb-3">Fuel Type</h4>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Gasoline (15)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Hybrid (5)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Electric (3)</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                <span class="ml-2 text-gray-700">Diesel (2)</span>
              </label>
            </div>
          </div>

          <!-- Year -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-800 mb-3">Year</h4>
            <div class="grid grid-cols-2 gap-2">
              <select class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                <option>Min Year</option>
                <option>2018</option>
                <option>2019</option>
                <option>2020</option>
                <option>2021</option>
                <option>2022</option>
                <option>2023</option>
              </select>
              <select class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                <option>Max Year</option>
                <option>2023</option>
                <option>2022</option>
                <option>2021</option>
                <option>2020</option>
                <option>2019</option>
                <option>2018</option>
              </select>
            </div>
          </div>

          <!-- Apply Filters Button -->
          <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 px-4 rounded-lg transition-colors font-medium shadow-sm">
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Cars Listing -->
      <div class="w-full lg:w-3/4 xl:w-4/5">
        <!-- Toolbar -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center">
            <span class="text-gray-700 mr-2">Sort by:</span>
            <select class="rounded-lg border-gray-300 text-sm p-2 text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
              <option>Featured</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Newest</option>
              <option>Mileage</option>
            </select>
          </div>

          <div class="flex items-center space-x-2">
            <span class="text-gray-700 mr-1">View:</span>
            <button id="gridViewBtn" class="view-btn active bg-gray-100 p-2 rounded-lg text-gray-700 hover:bg-gray-200 transition-colors" title="Grid View">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
            <button id="listViewBtn" class="view-btn bg-gray-100 p-2 rounded-lg text-gray-700 hover:bg-gray-200 transition-colors" title="List View">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Cars Grid -->
        <div id="carsContainer" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <!-- Car Card 1 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1550355291-bbee04a92027?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=600&q=60" alt="Toyota Camry" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">Toyota Camry</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2022 · 12,450 mi · Gasoline</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Sedan</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Automatic</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">FWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$24,990</p>
                </div>
                <a href="car-details.php?id=1" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>

          <!-- Car Card 2 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=600&q=60" alt="Honda Civic" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">Honda Civic</h3>
                <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-full font-medium">In Transit</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2021 · 18,700 mi · Gasoline</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Sedan</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Automatic</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">FWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$21,500</p>
                </div>
                <a href="car-details.php?id=2" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>

          <!-- Car Card 3 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1609610860511-87ed86580d1a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTV8fHRlc2xhJTIwY2FyfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Tesla Model 3" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">Tesla Model 3</h3>
                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-medium">Sold</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2023 · 2,100 mi · Electric</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Sedan</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Automatic</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">AWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$46,990</p>
                </div>
                <a href="car-details.php?id=3" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>

          <!-- Car Card 4 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1551830820-330a71b99659?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTh8fGZvcmQlMjBtdXN0YW5nfGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="Ford Mustang" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">Ford Mustang</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2022 · 8,900 mi · Gasoline</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Coupe</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Manual</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">RWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$38,500</p>
                </div>
                <a href="car-details.php?id=4" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>

          <!-- Car Card 5 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Ym13fGVufDB8fDB8fHww&auto=format&fit=crop&w=600&q=60" alt="BMW X5" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">BMW X5</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2023 · 3,200 mi · Diesel</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">SUV</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Automatic</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">AWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$63,900</p>
                </div>
                <a href="car-details.php?id=5" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>

          <!-- Car Card 6 -->
          <div class="car-card bg-white shadow-sm overflow-hidden">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1583267746897-2cf415887172?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8YXVkaSUyMGNhcnxlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=600&q=60" alt="Audi A4" class="car-image">
              <div class="absolute top-2 right-2">
                <button class="bg-white/80 hover:bg-white p-1.5 rounded-full transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="p-4">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-semibold text-gray-800">Audi A4</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Available</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">2022 · 7,200 mi · Gasoline</p>
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Sedan</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Automatic</span>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">AWD</span>
              </div>
              <div class="flex justify-between items-end">
                <div>
                  <span class="text-gray-500 text-xs">Price</span>
                  <p class="text-indigo-600 font-semibold text-xl">$42,100</p>
                </div>
                <a href="car-details.php?id=6" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                  View Details
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-10 mb-4 flex justify-center">
          <div class="flex space-x-1">
            <a href="#" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">Previous</a>
            <a href="#" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">1</a>
            <a href="#" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">2</a>
            <a href="#" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">3</a>
            <a href="#" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">Next</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Mobile Filter Drawer -->
  <div id="mobileFilterOverlay" class="mobile-filter-overlay"></div>
  <div id="mobileFilterDrawer" class="mobile-filter-drawer p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="font-semibold text-gray-800 text-lg">Filters</h3>
      <button id="closeFilterBtn" class="text-gray-400 hover:text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Mobile Filters - Same as desktop but optimized for mobile -->
    <!-- Price Range -->
    <div class="mb-6">
      <h4 class="font-medium text-gray-800 mb-3">Price Range</h4>
      <div class="mb-4">
        <input type="range" min="0" max="100000" value="50000" class="range-slider" id="mobilePriceRange">
        <div class="flex justify-between mt-2 text-sm text-gray-600">
          <span>$0</span>
          <span id="mobilePriceValue">$50,000</span>
          <span>$100k+</span>
        </div>
      </div>
    </div>

    <!-- Make -->
    <div class="mb-6">
      <h4 class="font-medium text-gray-800 mb-3">Make</h4>
      <div class="space-y-2 max-h-48 overflow-y-auto">
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Toyota (8)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Honda (5)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Ford (4)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Tesla (3)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">BMW (3)</span>
        </label>
      </div>
    </div>

    <!-- Body Type -->
    <div class="mb-6">
      <h4 class="font-medium text-gray-800 mb-3">Body Type</h4>
      <div class="space-y-2">
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Sedan (10)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">SUV (8)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Truck (4)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Coupe (2)</span>
        </label>
        <label class="flex items-center">
          <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
          <span class="ml-2 text-gray-700">Hatchback (1)</span>
        </label>
      </div>
    </div>

    <!-- Apply Button -->
    <div class="mt-6 pt-4 border-t border-gray-200">
      <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg transition-colors font-medium shadow-sm">
        Apply Filters
      </button>
      <button class="w-full mt-3 bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium">
        Clear All
      </button>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white pt-12 pb-8 mt-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
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
          <div class="flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22 0H2C0.9 0 0 0.9 0 2v20c0 1.1 0.9 2 2 2h20c1.1 0 2-0.9 2-2V2c0-1.1-0.9-2-2-2zm-11 7h8v4h-8V7zm-1 8H3v-4h7v4zm0-8H3V7h7v4zm9 12h-7v-4h7v4z" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.643 4.937c-.835.37-1.732.62-2.675.733.962-.576 1.7-1.49 2.048-2.578-.9.534-1.897.922-2.958 1.13-.85-.904-2.06-1.47-3.4-1.47-2.572 0-4.658 2.086-4.658 4.66 0 .364.042.718.12 1.06-3.873-.195-7.304-2.05-9.602-4.868-.4.69-.63 1.49-.63 2.342 0 1.616.823 3.043 2.072 3.878-.764-.025-1.482-.234-2.11-.583v.06c0 2.257 1.605 4.14 3.737 4.568-.392.106-.803.162-1.227.162-.3 0-.593-.028-.877-.082.593 1.85 2.313 3.198 4.352 3.234-1.595 1.25-3.604 1.995-5.786 1.995-.376 0-.747-.022-1.112-.065 2.062 1.323 4.51 2.093 7.14 2.093 8.57 0 13.255-7.098 13.255-13.254 0-.2-.005-.402-.014-.602.91-.658 1.7-1.477 2.323-2.41z" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 16h-2v-6h2v6zm-1-6.891c-.607 0-1.1-.496-1.1-1.109 0-.612.492-1.109 1.1-1.109s1.1.497 1.1 1.109c0 .613-.493 1.109-1.1 1.109zm8 6.891h-1.998v-2.861c0-1.881-2.002-1.722-2.002 0v2.861h-2v-6h2v1.093c.872-1.616 4-1.736 4 1.548v3.359z" />
              </svg>
            </a>
          </div>
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
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
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
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 26V5a1 1 0 01-1-1V2zm0 0" />
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
            <li class="flex items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-400">
                Mon-Fri: 9:00 AM - 8:00 PM<br>
                Saturday: 9:00 AM - 6:00 PM<br>
                Sunday: Closed
              </span>
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
          <p class="text-xs text-gray-500">We respect your privacy. Unsubscribe at any time.</p>
        </div>
      </div>

      <div class="border-t border-gray-700 mt-10 pt-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm">&copy; 2023 CentralAutogy. All rights reserved.</p>
          <div class="mt-4 md:mt-0">
            <div class="flex space-x-4">
              <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
              <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
              <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Cookie Policy</a>
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

    // Mobile filter drawer
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');
    const mobileFilterDrawer = document.getElementById('mobileFilterDrawer');
    const closeFilterBtn = document.getElementById('closeFilterBtn');

    function toggleFilterDrawer() {
      mobileFilterOverlay.classList.toggle('open');
      mobileFilterDrawer.classList.toggle('open');

      if (mobileFilterOverlay.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = 'auto';
      }
    }

    mobileFilterBtn.addEventListener('click', toggleFilterDrawer);
    closeFilterBtn.addEventListener('click', toggleFilterDrawer);
    mobileFilterOverlay.addEventListener('click', toggleFilterDrawer);

    // Grid/List view toggle
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const carsContainer = document.getElementById('carsContainer');

    gridViewBtn.addEventListener('click', function() {
      carsContainer.classList.remove('list-view');
      gridViewBtn.classList.add('active');
      listViewBtn.classList.remove('active');

      // Change grid columns for grid view
      carsContainer.classList.remove('grid-cols-1');
      carsContainer.classList.add('grid-cols-1', 'sm:grid-cols-2', 'xl:grid-cols-3');
    });

    listViewBtn.addEventListener('click', function() {
      carsContainer.classList.add('list-view');
      listViewBtn.classList.add('active');
      gridViewBtn.classList.remove('active');

      // Change grid columns for list view
      carsContainer.classList.remove('sm:grid-cols-2', 'xl:grid-cols-3');
      carsContainer.classList.add('grid-cols-1');
    });

    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const mobilePriceRange = document.getElementById('mobilePriceRange');
    const mobilePriceValue = document.getElementById('mobilePriceValue');

    function updatePriceValue(value) {
      const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0
      }).format(value);

      priceValue.textContent = formattedPrice;
      mobilePriceValue.textContent = formattedPrice;
    }

    priceRange.addEventListener('input', function() {
      updatePriceValue(this.value);
      mobilePriceRange.value = this.value;
    });

    mobilePriceRange.addEventListener('input', function() {
      updatePriceValue(this.value);
      priceRange.value = this.value;
    });
  </script>
</body>

</html>