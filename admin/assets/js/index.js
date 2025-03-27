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