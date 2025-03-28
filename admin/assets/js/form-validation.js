// Vehicle form validation
document.addEventListener('DOMContentLoaded', function() {
  const quickAddForm = document.getElementById('quickAddForm');
  const addCarForm = document.getElementById('addCarForm');
  
  if (quickAddForm) {
    quickAddForm.addEventListener('submit', function(e) {
      if (!validateVehicleForm(this)) {
        e.preventDefault();
      }
    });
  }
  
  if (addCarForm) {
    addCarForm.addEventListener('submit', function(e) {
      if (!validateVehicleForm(this)) {
        e.preventDefault();
      }
    });
    
    // For the modal form, connect to the save button as well
    const saveVehicleBtn = document.getElementById('saveVehicleBtn');
    if (saveVehicleBtn) {
      saveVehicleBtn.addEventListener('click', function() {
        if (validateVehicleForm(addCarForm)) {
          addCarForm.submit();
        }
      });
    }
  }
  
  // Form validation function
  function validateVehicleForm(form) {
    let isValid = true;
    clearErrors(form);
    
    // Define required fields depending on the form
    const isQuickForm = form.id === 'quickAddForm';
    const requiredFields = isQuickForm ? 
      ['make', 'model', 'year', 'status'] : 
      ['make', 'model', 'year', 'body_style', 'mileage', 'fuel_type', 'transmission', 'status'];
    
    // Check required fields
    requiredFields.forEach(fieldName => {
      const field = form.elements[fieldName];
      if (field && (field.value === '' || field.value === '0')) {
        showError(field, 'This field is required');
        isValid = false;
      }
    });
    
    // Year validation
    const yearField = form.elements['year'];
    if (yearField && yearField.value !== '') {
      const year = parseInt(yearField.value);
      const currentYear = new Date().getFullYear();
      if (isNaN(year) || year < 1900 || year > currentYear + 1) {
        showError(yearField, `Year must be between 1900 and ${currentYear + 1}`);
        isValid = false;
      }
    }
    
    // Mileage validation
    const mileageField = form.elements['mileage'];
    if (mileageField && mileageField.value !== '') {
      const mileage = mileageField.value.replace(/,/g, '');
      if (isNaN(mileage) || parseInt(mileage) < 0) {
        showError(mileageField, 'Please enter a valid mileage');
        isValid = false;
      }
    }
    
    // Price validation (if in the form)
    const priceField = form.elements['price'];
    if (priceField && priceField.value !== '') {
      const price = priceField.value.replace(/,/g, '');
      if (isNaN(price) || parseFloat(price) <= 0) {
        showError(priceField, 'Please enter a valid price');
        isValid = false;
      }
    }
    
    // VIN validation (if in the form)
    const vinField = form.elements['vin'];
    if (vinField && vinField.value !== '') {
      // Simple VIN validation (more complex validation can be added)
      const vinRegex = /^[A-HJ-NPR-Z0-9]{17}$/i;
      if (!vinRegex.test(vinField.value)) {
        showError(vinField, 'Please enter a valid 17-character VIN');
        isValid = false;
      }
    }
    
    return isValid;
  }
  
  // Function to show error message
  function showError(field, message) {
    // Clear any existing error
    clearError(field);
    
    // Create error element
    const errorElement = document.createElement('div');
    errorElement.className = 'text-red-500 text-xs mt-1';
    errorElement.innerText = message;
    
    // Add error class to field
    field.classList.add('border-red-500');
    
    // Add error message after the field
    field.parentNode.appendChild(errorElement);
  }
  
  // Function to clear error for a single field
  function clearError(field) {
    field.classList.remove('border-red-500');
    
    // Remove any existing error messages
    const errorElement = field.parentNode.querySelector('.text-red-500');
    if (errorElement) {
      errorElement.remove();
    }
  }
  
  // Function to clear all errors in a form
  function clearErrors(form) {
    // Remove all error messages
    const errorElements = form.querySelectorAll('.text-red-500');
    errorElements.forEach(element => {
      element.remove();
    });
    
    // Remove all error classes from fields
    const fields = form.querySelectorAll('.border-red-500');
    fields.forEach(field => {
      field.classList.remove('border-red-500');
    });
  }
  
  // Auto-format inputs
  const priceInputs = document.querySelectorAll('input[name="price"]');
  if (priceInputs.length > 0) {
    priceInputs.forEach(input => {
      input.addEventListener('input', function() {
        // Remove non-numeric characters
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
          value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Format with commas for thousands
        if (value !== '') {
          const formattedValue = formatNumberWithCommas(value);
          this.value = formattedValue;
        }
      });
    });
  }
  
  const mileageInputs = document.querySelectorAll('input[name="mileage"]');
  if (mileageInputs.length > 0) {
    mileageInputs.forEach(input => {
      input.addEventListener('input', function() {
        // Remove non-numeric characters
        let value = this.value.replace(/[^0-9]/g, '');
        
        // Format with commas for thousands
        if (value !== '') {
          this.value = formatNumberWithCommas(value);
        }
      });
    });
  }
  
  // Helper function to format numbers with commas
  function formatNumberWithCommas(number) {
    // Handle decimal parts
    const parts = number.toString().split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
  }
});