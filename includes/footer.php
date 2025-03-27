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
          <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
          <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Browse Inventory</a></li>
          <li><a href="financing.php" class="text-gray-400 hover:text-white transition-colors">Financing Options</a></li>
          <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
          <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
          <li><a href="privacy-policy.php" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
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
            <span class="text-gray-400">123 Central Avenue, Autogy City, CA 90210</span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
            </svg>
            <span class="text-gray-400">(800) 123-4567</span>
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
        <form class="mb-2" action="subscribe.php" method="post">
          <div class="flex">
            <input type="email" name="email" placeholder="Your email address" class="px-4 py-2 w-full rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-800">
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
        <p class="text-gray-400 text-sm">&copy; <?php echo date("Y"); ?> CentralAutogy. All rights reserved.</p>
        <div class="mt-4 md:mt-0">
          <div class="flex space-x-4">
            <a href="terms.php" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
            <a href="privacy-policy.php" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
            <a href="cookie-policy.php" class="text-gray-400 hover:text-white text-sm transition-colors">Cookie Policy</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Optional: Back to top button, visible when scrolled down -->
<button id="backToTopBtn" class="fixed bottom-8 right-8 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-full shadow-lg opacity-0 transition-opacity duration-300 z-50">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
  </svg>
</button>

<script>
  // Back to top button functionality
  const backToTopBtn = document.getElementById('backToTopBtn');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      backToTopBtn.classList.remove('opacity-0');
      backToTopBtn.classList.add('opacity-100');
    } else {
      backToTopBtn.classList.remove('opacity-100');
      backToTopBtn.classList.add('opacity-0');
    }
  });

  backToTopBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
</script>