$(document).ready( function () {
    $('#myTable').DataTable({
        paging: true,
        searching: true,
        ordering: true
    });
  });

  
  
  function validateForm() {
    const name = document.getElementById('clientName').value;
    const email = document.getElementById('clientEmail').value;
    const number = document.getElementById('clientNumber').value;
    const image = document.getElementById('clientImage').files[0];
  
    if (!name || !email || !number || !image) {
        let errorMessage = 'Please fill in all fields!';
        if (!image) {
            errorMessage = 'Please select a profile image!';
        }
  
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: errorMessage
        });
        return false;
    }
  
    // Validate image size and type
    if (image) {
        const fileSize = image.size / (1024 * 1024); // Convert to MB
        const fileType = image.type.split('/')[1].toLowerCase();
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
  
        if (fileSize > 5) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Image size should not exceed 5MB'
            });
            return false;
        }
  
        if (!allowedTypes.includes(fileType)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Please upload a valid image file (JPG, JPEG, PNG, or GIF)'
            });
            return false;
        }
    }
  
    // Close modal after successful validation
    const modal = bootstrap.Modal.getInstance(document.getElementById('addClientModal'));
    modal.hide();
    return true;
  }
  
  function confirmDelete(event) {
    event.preventDefault();
    const userId = event.target.querySelector('input[name="id"]').value;

    Swal.fire({
        title: 'Delete Confirmation',
        text: "Would you like to proceed with deletion?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Send OTP',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send request to generate and email OTP
            fetch('send_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Only show OTP modal if OTP was successfully sent
                    showOtpModal(userId);  // This will now happen only after OTP is successfully sent
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to send OTP'
                    });
                }
            });
        }
    });
}
  
  function showOtpModal(id) {
      // Set the user ID to the hidden input in the OTP form
      document.getElementById("user_id").value = id;
  
      // Show the OTP modal
      var otpModal = new bootstrap.Modal(document.getElementById('verifyOtpModal'));
      otpModal.show();
  }
  
  // Handle status messages
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  const message = urlParams.get('message');
  
  if (status && message) {
    Swal.fire({
        icon: status,
        title: status.charAt(0).toUpperCase() + status.slice(1),
        text: message
    }).then(() => {
        const newUrl = window.location.href.split('?')[0];
        history.replaceState(null, '', newUrl);
    });
  }
  
  function logout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "http://localhost/Task1-Neuralcore/index.php";
        } else {
            console.log("Logout canceled");
        }
    });
  }
  
  
  $('#otpForm').submit(function(e) {
      e.preventDefault();
      var otp = $('#otp').val();
      var userId = $('#user_id').val();
  
      $.ajax({
          type: 'POST',
          url: 'verify_otp.php',
          data: { otp: otp, user_id: userId },
          success: function(response) {
              var result = JSON.parse(response);
              if (result.success) {
                  Swal.fire('Success', 'The record has been hidden', 'success');
                  $('#verifyOtpModal').modal('hide');
                  // Optionally, remove the user row from the table or reload the page
              } else {
                  Swal.fire('Error', result.message, 'error');
              }
          }
      });
  });
  
