<?php
// session_start();
?>
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
        <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors">Inventory</a>
        <a href="about.php" class="text-gray-600 hover:text-indigo-600 transition-colors">About Us</a>
        <a href="contact.php" class="text-gray-600 hover:text-indigo-600 transition-colors">Contact</a>
      </div>

      <!-- CTA Buttons -->
      <div class="flex items-center space-x-3">
        <a href="saved-vehicles.php" class="hidden sm:block text-gray-600 hover:text-indigo-600 transition-colors">
          <div class="flex items-center space-x-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
            </svg>
            <span>Saved</span>
          </div>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- User dropdown (desktop) -->
        <div class="hidden sm:block relative">
          <button id="userDropdownBtn" class="flex items-center space-x-1 bg-white text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-lg transition-colors border border-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Profile</a>
            <a href="saved-vehicles.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Saved Vehicles</a>
            <a href="test-drives.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">My Test Drives</a>
            <a href="financing.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Financing</a>
            <div class="border-t border-gray-100 my-1"></div>
            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
          </div>
        </div>
        <?php else: ?>
        <!-- Login button (desktop) -->
        <a href="login.php" class="hidden sm:block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm">
          Login
        </a>
        <?php endif; ?>

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
        <a href="index.php" class="text-indigo-600 font-medium py-2">Home</a>
        <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Inventory</a>
        <a href="about.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">About Us</a>
        <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Financing</a>
        <a href="contact.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2">Contact</a>
        <a href="saved-vehicles.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
          Saved Cars
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- User account section for mobile -->
        <div class="border-t border-gray-200 pt-2 mt-2">
          <p class="text-sm font-medium text-gray-500 mb-1">Account</p>
          <a href="profile.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2 block">Profile</a>
          <a href="test-drives.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2 block">My Test Drives</a>
          <a href="financing.php" class="text-gray-600 hover:text-indigo-600 transition-colors py-2 block">Financing</a>
          <a href="logout.php" class="text-red-600 hover:text-red-800 transition-colors py-2 block">Logout (<?php echo htmlspecialchars($_SESSION['first_name']); ?>)</a>
        </div>
        <?php else: ?>
        <!-- Login button for mobile -->
        <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors text-center">
          Login
        </a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<script>
  // Mobile menu toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');

  mobileMenuBtn.addEventListener('click', function() {
    mobileMenu.classList.toggle('hidden');
  });

  // User dropdown toggle (desktop)
  const userDropdownBtn = document.getElementById('userDropdownBtn');
  const userDropdown = document.getElementById('userDropdown');
  
  if (userDropdownBtn) {
    userDropdownBtn.addEventListener('click', function() {
      userDropdown.classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      if (!userDropdownBtn.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
      }
    });
  }
</script>