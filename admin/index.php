<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/output.css" rel="stylesheet">
  <title>CentralAutogy - Car Inventory Management</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body,
    html {
      font-family: 'Poppins', sans-serif;
      height: 100%;
      overflow: hidden;
    }

    .sidebar {
      height: calc(100vh - 64px);
      transition: all 0.3s;
    }

    .dashboard-card {
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s;
    }

    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .sidebar-item {
      border-radius: 10px;
      transition: all 0.2s;
    }

    .sidebar-item:hover {
      transform: translateX(5px);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background: #94a3b8;
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #64748b;
    }

    .table-row {
      transition: all 0.2s;
    }

    .table-row:hover {
      background-color: #f8fafc;
    }

    /* Animation for modal */
    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideIn {
      from {
        transform: translateY(-20px);
      }

      to {
        transform: translateY(0);
      }
    }

    .modal-animation {
      animation: fadeIn 0.3s, slideIn 0.3s;
    }

    /* Custom file input */
    .file-drop-area {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 25px;
      border: 2px dashed #cbd5e1;
      border-radius: 12px;
      transition: 0.3s;
      background-color: #f8fafc;
    }

    .file-drop-area:hover,
    .file-drop-area.is-active {
      background-color: #f1f5f9;
      border-color: #94a3b8;
    }

    .fake-btn {
      flex-shrink: 0;
      background-color: #f1f5f9;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 8px 15px;
      margin-right: 10px;
      font-size: 14px;
      font-weight: 500;
      color: #334155;
      transition: 0.3s;
    }

    input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 100%;
      opacity: 0;
      cursor: pointer;
    }

    .file-msg {
      color: #64748b;
      font-size: 14px;
      font-weight: 500;
      line-height: 1.4;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-gradient-to-r from-indigo-700 to-purple-700 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
          </svg>
          <h1 class="text-2xl font-bold">CentralAutogy</h1>
        </div>
        <span class="hidden md:inline-block text-sm bg-white bg-opacity-20 text-black px-3 py-1 rounded-full">Admin Dashboard</span>
      </div>
      <div class="flex items-center space-x-6">
        <div class="relative hidden md:block">
          <div class="flex items-center space-x-2">
            <div class="h-8 w-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
              </svg>
            </div>
            <span class="font-medium">Welcome, Admin</span>
          </div>
        </div>
        <div class="flex items-center space-x-1">
          <a href="#" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
            </svg>
          </a>
          <a href="#" class="p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
          </a>
          <button id="logoutBtn" class="flex items-center space-x-1 bg-white bg-opacity-10 hover:bg-opacity-20 text-black px-3 py-1.5 rounded-full text-sm font-medium transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V7.414l-5-5H3zm7 5a1 1 0 00-1 1v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 14.586V9a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="flex h-[calc(100vh-64px)]">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg sidebar hidden md:block overflow-y-auto">
      <nav class="p-4">
        <div class="mb-6">
          <div class="flex items-center px-4 py-2.5">
            <div class="w-full relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input type="text" placeholder="Search..." class="pl-10 w-full rounded-lg border border-gray-200 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div>
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main Menu</p>
            <ul class="mt-2 space-y-1">
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" />
                    <path d="M3 10a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" />
                    <path d="M3 16a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" />
                  </svg>
                  <span>Dashboard</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
                  </svg>
                  <span>Car Inventory</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zm8 0a3 3 0 11-6 0 3 3 0 016 0zm-4.07 11c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                  </svg>
                  <span>Customers</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                  </svg>
                  <span>Orders</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                  </svg>
                  <span>Reports</span>
                </a>
              </li>
            </ul>
          </div>

          <div>
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
            <ul class="mt-2 space-y-1">
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                  </svg>
                  <span>Settings</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 rounded-lg sidebar-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                  </svg>
                  <span>Help & Support</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </aside>

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
        <h1 class="text-2xl font-bold text-gray-800">Vehicle Management Dashboard</h1>
        <p class="text-gray-600">Monitor and manage your car inventory</p>
      </div>

      <!-- Dashboard Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="dashboard-card bg-white p-6">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm font-medium">Total Vehicles</p>
              <h3 class="text-3xl font-bold text-gray-800 mt-1">152</h3>
            </div>
            <div class="bg-indigo-100 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-500 text-sm font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
              12%
            </span>
            <span class="text-gray-500 text-sm ml-1">from last month</span>
          </div>
        </div>

        <div class="dashboard-card bg-white p-6">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm font-medium">Available</p>
              <h3 class="text-3xl font-bold text-gray-800 mt-1">98</h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-red-500 text-sm font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
              3%
            </span>
            <span class="text-gray-500 text-sm ml-1">from last month</span>
          </div>
        </div>

        <div class="dashboard-card bg-white p-6">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm font-medium">Sold</p>
              <h3 class="text-3xl font-bold text-gray-800 mt-1">42</h3>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-500 text-sm font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
              22%
            </span>
            <span class="text-gray-500 text-sm ml-1">from last month</span>
          </div>
        </div>

        <div class="dashboard-card bg-white p-6">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm font-medium">In Transit</p>
              <h3 class="text-3xl font-bold text-gray-800 mt-1">12</h3>
            </div>
            <div class="bg-amber-100 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 flex items-center">
            <span class="text-green-500 text-sm font-medium flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
              8%
            </span>
            <span class="text-gray-500 text-sm ml-1">from last month</span>
          </div>
        </div>
      </div>

      <!-- Recent Inventory & Add New Car -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Car Inventory Table -->
        <div class="dashboard-card bg-white p-6 w-full lg:w-3/4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800">Recent Inventory</h2>
            <button id="addNewCarBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Add New Vehicle
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tl-lg">Car Name</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Year/Make</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Mileage</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Fuel Type</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Toyota Camry</td>
                  <td class="px-4 py-3 text-gray-800">2022 Toyota</td>
                  <td class="px-4 py-3 text-gray-800">12,450</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Honda Civic</td>
                  <td class="px-4 py-3 text-gray-800">2021 Honda</td>
                  <td class="px-4 py-3 text-gray-800">18,700</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">In Transit</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Tesla Model 3</td>
                  <td class="px-4 py-3 text-gray-800">2023 Tesla</td>
                  <td class="px-4 py-3 text-gray-800">2,100</td>
                  <td class="px-4 py-3 text-gray-800">Electric</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Sold</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">Ford Mustang</td>
                  <td class="px-4 py-3 text-gray-800">2022 Ford</td>
                  <td class="px-4 py-3 text-gray-800">8,900</td>
                  <td class="px-4 py-3 text-gray-800">Gasoline</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="border-b border-gray-100 table-row">
                  <td class="px-4 py-3 text-gray-800">BMW X5</td>
                  <td class="px-4 py-3 text-gray-800">2023 BMW</td>
                  <td class="px-4 py-3 text-gray-800">3,200</td>
                  <td class="px-4 py-3 text-gray-800">Diesel</td>
                  <td class="px-4 py-3">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Available</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex space-x-2">
                      <button class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-between items-center">
            <div>
              <span class="text-sm text-gray-600">Showing 5 of 152 vehicles</span>
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

        <!-- Add New Car Form -->
        <div class="dashboard-card bg-white p-6 w-full lg:w-1/4">
          <h2 class="text-xl font-bold text-gray-800 mb-6">Quick Add Vehicle</h2>
          <form id="quickAddForm" class="space-y-4">
            <div>
              <label for="carName" class="block text-sm font-medium text-gray-700 mb-1">Car Name</label>
              <input type="text" id="carName" name="carName" placeholder="e.g. Toyota Camry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            </div>
            <div>
              <label for="carBody" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
              <select id="carBody" name="carBody" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Body Type</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="hatchback">Hatchback</option>
                <option value="coupe">Coupe</option>
                <option value="truck">Truck</option>
                <option value="van">Van</option>
              </select>
            </div>
            <div>
              <label for="mileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
              <input type="text" id="mileage" name="mileage" placeholder="e.g. 15,000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            </div>
            <div>
              <label for="fuelType" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
              <select id="fuelType" name="fuelType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Fuel Type</option>
                <option value="gasoline">Gasoline</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
              </select>
            </div>
            <div>
              <label for="yearMake" class="block text-sm font-medium text-gray-700 mb-1">Year/Make</label>
              <input type="text" id="yearMake" name="yearMake" placeholder="e.g. 2023 Toyota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
            </div>
            <div>
              <label for="transmission" class="block text-sm font-medium text-gray-700 mb-1">Transmission</label>
              <select id="transmission" name="transmission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Transmission</option>
                <option value="automatic">Automatic</option>
                <option value="manual">Manual</option>
                <option value="cvt">CVT</option>
              </select>
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                <option value="">Select Status</option>
                <option value="available">Available</option>
                <option value="inTransit">In Transit</option>
                <option value="sold">Sold</option>
              </select>
            </div>
            <div class="pt-2">
              <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-2.5 px-4 rounded-lg transition duration-300 font-medium text-sm shadow-md">
                Add Vehicle
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>

  <!-- Add New Car Modal -->
  <div id="addCarModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-xl w-full max-w-3xl mx-auto shadow-2xl modal-animation">
        <div class="flex justify-between items-center border-b p-6">
          <div class="flex items-center">
            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm7 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H14a1 1 0 001-1v-3h-5v-1h9V8h-1a1 1 0 00-1-1h-6a1 1 0 00-1 1v7.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V5a1 1 0 00-1-1H3z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Add New Vehicle</h3>
          </div>
          <button id="closeModalBtn" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-all p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto">
          <form id="addCarForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="modalCarName" class="block text-sm font-medium text-gray-700 mb-1">Car Name</label>
              <input type="text" id="modalCarName" name="modalCarName" placeholder="e.g. Toyota Camry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalCarBody" class="block text-sm font-medium text-gray-700 mb-1">Body Type</label>
              <select id="modalCarBody" name="modalCarBody" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Body Type</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="hatchback">Hatchback</option>
                <option value="coupe">Coupe</option>
                <option value="truck">Truck</option>
                <option value="van">Van</option>
              </select>
            </div>
            <div>
              <label for="modalMileage" class="block text-sm font-medium text-gray-700 mb-1">Mileage</label>
              <input type="text" id="modalMileage" name="modalMileage" placeholder="e.g. 15,000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalFuelType" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
              <select id="modalFuelType" name="modalFuelType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Fuel Type</option>
                <option value="gasoline">Gasoline</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
              </select>
            </div>
            <div>
              <label for="modalYearMake" class="block text-sm font-medium text-gray-700 mb-1">Year/Make</label>
              <input type="text" id="modalYearMake" name="modalYearMake" placeholder="e.g. 2023 Toyota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalTransmission" class="block text-sm font-medium text-gray-700 mb-1">Transmission</label>
              <select id="modalTransmission" name="modalTransmission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Transmission</option>
                <option value="automatic">Automatic</option>
                <option value="manual">Manual</option>
                <option value="cvt">CVT</option>
              </select>
            </div>
            <div>
              <label for="modalDrive" class="block text-sm font-medium text-gray-700 mb-1">Drive Type</label>
              <select id="modalDrive" name="modalDrive" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Drive Type</option>
                <option value="fwd">FWD</option>
                <option value="rwd">RWD</option>
                <option value="awd">AWD</option>
                <option value="4wd">4WD</option>
              </select>
            </div>
            <div>
              <label for="modalExteriorColor" class="block text-sm font-medium text-gray-700 mb-1">Exterior Color</label>
              <input type="text" id="modalExteriorColor" name="modalExteriorColor" placeholder="e.g. White" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalInteriorColor" class="block text-sm font-medium text-gray-700 mb-1">Interior Color</label>
              <input type="text" id="modalInteriorColor" name="modalInteriorColor" placeholder="e.g. Black" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
              <label for="modalStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="modalStatus" name="modalStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="">Select Status</option>
                <option value="available">Available</option>
                <option value="inTransit">In Transit</option>
                <option value="sold">Sold</option>
              </select>
            </div>
            <div>
              <label for="modalNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea id="modalNotes" name="modalNotes" rows="2" placeholder="Additional information about the vehicle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2focus:ring-indigo-500 text-sm resize-none"></textarea>
            </div>

            <div class="col-span-1 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Images</label>
              <div class="file-drop-area">
                <input type="file" id="modalImages" name="modalImages" multiple class="file-input" onChange="updateFileNames()">
                <div class="flex flex-col items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-600 mb-1 font-medium">Drag & drop vehicle images here</p>
                  <p class="text-xs text-gray-500">or click to browse files</p>
                </div>
                <div id="fileNames" class="mt-3 text-gray-600 text-xs space-y-1"></div>
              </div>
            </div>
          </form>
        </div>
        <div class="flex justify-end border-t p-6 space-x-3">
          <button id="cancelBtn" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
          </button>
          <button id="saveVehicleBtn" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-300 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
            Save Vehicle
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // File input enhancement
    const fileInput = document.getElementById('modalImages');
    const fileDropArea = document.querySelector('.file-drop-area');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      fileDropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      fileDropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      fileDropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
      fileDropArea.classList.add('is-active');
    }

    function unhighlight() {
      fileDropArea.classList.remove('is-active');
    }

    fileDropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      fileInput.files = files;
      updateFileNames();
    }

    // Display file names when files are selected
    function updateFileNames() {
      const input = document.getElementById('modalImages');
      const fileNames = document.getElementById('fileNames');
      fileNames.innerHTML = '';

      if (input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
          const fileName = document.createElement('div');
          fileName.innerHTML = `<span class="text-indigo-500">•</span> ${input.files[i].name}`;
          fileNames.appendChild(fileName);
        }
      }
    }

    // Modal functionality
    function toggleModal() {
      const modal = document.getElementById('addCarModal');
      modal.classList.toggle('hidden');

      if (!modal.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = 'auto';
      }
    }

    document.getElementById('addNewCarBtn').addEventListener('click', function() {
      toggleModal();
    });

    document.getElementById('closeModalBtn').addEventListener('click', function() {
      toggleModal();
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
      toggleModal();
    });

    // Close modal when clicking outside the modal content
    document.getElementById('addCarModal').addEventListener('click', function(e) {
      if (e.target === this) {
        toggleModal();
      }
    });

    // Allow ESC key to close the modal
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !document.getElementById('addCarModal').classList.contains('hidden')) {
        toggleModal();
      }
    });

    // Mobile menu toggle
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
      const sidebar = document.querySelector('aside');
      sidebar.classList.toggle('hidden');
    });

    // Quick add form submission
    document.getElementById('quickAddForm').addEventListener('submit', function(e) {
      e.preventDefault();

      // Show success toast
      showToast('Vehicle added successfully!', 'success');
    });

    // Modal form submission
    document.getElementById('saveVehicleBtn').addEventListener('click', function() {
      // Show success toast
      showToast('Vehicle added successfully!', 'success');
      toggleModal();
    });

    // Toast notification
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

    // Logout functionality
    document.getElementById('logoutBtn').addEventListener('click', function() {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'login.php';
      }
    });
  </script>
</body>

</html>