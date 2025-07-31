// form-validation.js
// Client-side validation for the CSV upload form.

document.addEventListener('DOMContentLoaded', function() {
    // Get the form and file input elements
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('csv_file');
    const fileErrorDiv = document.getElementById('fileError');

    // Maximum file size in bytes (5MB)
    const MAX_FILE_SIZE = 5 * 1024 * 1024;

    // Function to display error messages
    function showError(message) {
        fileErrorDiv.textContent = message;
        fileErrorDiv.style.display = 'block';
        // Scroll to the error message
        fileErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Function to clear error messages
    function clearError() {
        fileErrorDiv.textContent = '';
        fileErrorDiv.style.display = 'none';
    }

    // Add event listener to the file input for real-time validation
    fileInput.addEventListener('change', function() {
        clearError();
        const file = fileInput.files[0];

        if (file) {
            // 1. Check file type
            const fileName = file.name.toLowerCase();
            const fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1);

            if (fileExtension !== 'csv') {
                showError('Please select a file with a .csv extension.');
                fileInput.value = ''; // Clear the input
                return;
            }

            // 2. Check file size
            if (file.size > MAX_FILE_SIZE) {
                showError('File size exceeds the 5MB limit. Please choose a smaller file.');
                fileInput.value = ''; // Clear the input
                return;
            }

            // If all checks pass, you could optionally display file info
            // console.log(`File selected: ${file.name}, Size: ${file.size} bytes`);
        }
    });

    // Add event listener to the form for submission validation
    form.addEventListener('submit', function(event) {
        clearError();
        const file = fileInput.files[0];

        // Re-check if a file is selected (in case user deletes it after selecting)
        if (!file) {
            showError('Please select a CSV file to upload.');
            event.preventDefault(); // Prevent form submission
            return;
        }

        // Re-check type and size on submit (belt and braces)
        const fileName = file.name.toLowerCase();
        const fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1);

        if (fileExtension !== 'csv') {
            showError('Please select a file with a .csv extension.');
            event.preventDefault();
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            showError('File size exceeds the 5MB limit. Please choose a smaller file.');
            event.preventDefault();
            return;
        }

        // If validation passes, the form will submit normally.
        // You could add a loading indicator here if needed.
        // console.log("Form validation passed, submitting...");
    });

});