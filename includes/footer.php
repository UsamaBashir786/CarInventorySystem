<?php
// Include helper functions if not already included
if (!function_exists('get_setting')) {
  require_once 'includes/helpers.php';
}
?>
<!-- Footer -->
<footer class="bg-gray-800 text-white pt-12 pb-8">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Company Info -->
      <div>
        <div class="flex items-center space-x-2 mb-4">
          <?php 
          // Modify logo rendering to use footer_logo asset if available
          $footer_logo = get_asset_url('footer_logo');
          if ($footer_logo) {
            echo sprintf(
              '<img src="%s" alt="Footer Logo" class="h-7 w-7 text-indigo-400">',
              htmlspecialchars($footer_logo)
            );
          } else {
            // Fallback to SVG logo
            echo render_logo('h-7 w-7 text-indigo-400', 'indigo');
          }
          ?>
          <h3 class="text-lg font-bold"><?php echo get_setting('site_name', 'CentralAutogy'); ?></h3>
        </div>
        <p class="text-gray-400 mb-4"><?php echo get_setting('site_tagline', 'Your one-stop destination for finding the perfect vehicle. We provide a wide selection of high-quality cars at competitive prices.'); ?></p>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-2">
          <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
          <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
          <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
          <li><a href="https://www.agrsoft.com/" class="text-gray-400 hover:text-white transition-colors">Visit AGR SOFT</a></li>
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
            <span class="text-gray-400"><?php echo get_setting('contact_address', '123 Central Avenue, Autogy City, CA 90210'); ?></span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 7V3z" />
            </svg>
            <span class="text-gray-400"><?php echo get_setting('contact_phone', '(800) 123-4567'); ?></span>
          </li>
          <li class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
            <span class="text-gray-400"><?php echo get_setting('contact_email', 'info@centralautogy.com'); ?></span>
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
        <p class="text-gray-400 text-sm"><?php echo get_setting('footer_text', '© ' . date("Y") . ' CentralAutogy. All rights reserved.'); ?></p>
        <div class="mt-4 md:mt-0">
          <div class="flex space-x-4">
            <a href="social_links.php" class="text-gray-400 hover:text-white text-sm transition-colors">
              <?php 
              $facebook_url = get_setting('facebook_url', '#');
              $twitter_url = get_setting('twitter_url', '#');
              $instagram_url = get_setting('instagram_url', '#');
              $linkedin_url = get_setting('linkedin_url', '#');
              ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/>
              </svg>
              Facebook
            </a>
            <a href="<?php echo htmlspecialchars($twitter_url); ?>" class="text-gray-400 hover:text-white text-sm transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.399 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
              </svg>
              Twitter
            </a>
            <a href="<?php echo htmlspecialchars($instagram_url); ?>" class="text-gray-400 hover:text-white text-sm transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.148 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.148-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.281.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.948-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
              Instagram
            </a>
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