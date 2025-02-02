<?php
session_start();
require 'vendor/autoload.php';
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Add Client
        if ($action === 'add') {
          $name = mysqli_real_escape_string($conn, $_POST['name']);
          $email = mysqli_real_escape_string($conn, $_POST['email']);
          $number = mysqli_real_escape_string($conn, $_POST['number']);
      
          // Validate email
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              header("Location: homepage.php?status=error&message=Invalid email format");
              exit;
          }
      
          // Check if email already exists
          $checkQuery = "SELECT * FROM users_info WHERE email = '$email'";
          $checkResult = mysqli_query($conn, $checkQuery);
      
          if (mysqli_num_rows($checkResult) > 0) {
              header("Location: homepage.php?status=error&message=Email already exists");
              exit;
          }
      
          // Handle image upload
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
              $image = $_FILES['image'];
              $imageName = basename($image['name']);
              $imageTmpName = $image['tmp_name'];
              $imageSize = $image['size'];
              $imageType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
      
              // Validate image type and size
              $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
              if (!in_array($imageType, $allowedTypes)) {
                  header("Location: homepage.php?status=error&message=Invalid image type. Allowed types: JPG, JPEG, PNG, GIF");
                  exit;
              }
              if ($imageSize > 5 * 1024 * 1024) { // 5MB limit
                  header("Location: homepage.php?status=error&message=Image size exceeds 5MB");
                  exit;
              }
      
              // Move uploaded image to a folder
              $uploadDir = 'uploads/';
              if (!is_dir($uploadDir)) {
                  mkdir($uploadDir, 0755, true);
              }
              $imagePath = $uploadDir . uniqid() . '.' . $imageType;
              move_uploaded_file($imageTmpName, $imagePath);
          } else {
              $imagePath = null; // No image uploaded
          }
      
          // Insert into database
          $query = "INSERT INTO users_info (name, email, number, image_path) VALUES ('$name', '$email', '$number', '$imagePath')";
      
          if (mysqli_query($conn, $query)) {
              header("Location: homepage.php?status=success&message=Client added successfully");
          } else {
              header("Location: homepage.php?status=error&message=" . urlencode(mysqli_error($conn)));
          }
      }

        // Edit Client
        elseif ($action === 'edit') {
            $id = mysqli_real_escape_string($conn, $_POST['id']);
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $number = mysqli_real_escape_string($conn, $_POST['number']);
      
          // Validate email
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              header("Location: homepage.php?status=error&message=Invalid email format");
              exit;
          }
      
          // Check if email already exists (excluding the current client)
          $checkQuery = "SELECT * FROM users_info WHERE email = '$email' AND id != $id";
          $checkResult = mysqli_query($conn, $checkQuery);
      
          if (mysqli_num_rows($checkResult) > 0) {
              header("Location: homepage.php?status=error&message=Email already exists");
              exit;
          }
      
          // Handle image upload
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $imageName = basename($image['name']);
            $imageTmpName = $image['tmp_name'];
            $imageSize = $image['size'];
            $imageType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
      
               // Validate image type and size
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageType, $allowedTypes)) {
                header("Location: homepage.php?status=error&message=Invalid image type. Allowed types: JPG, JPEG, PNG, GIF");
                exit;
            }
            if ($imageSize > 5 * 1024 * 1024) { // 5MB limit
                header("Location: homepage.php?status=error&message=Image size exceeds 5MB");
                exit;
            }
      
              /// Move uploaded image to a folder
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imagePath = $uploadDir . uniqid() . '.' . $imageType;
            move_uploaded_file($imageTmpName, $imagePath);
      
               // Delete old image if it exists
            $oldImageQuery = "SELECT image_path FROM users_info WHERE id = $id";
            $oldImageResult = mysqli_query($conn, $oldImageQuery);
            if ($oldImageRow = mysqli_fetch_assoc($oldImageResult)) {
                if ($oldImageRow['image_path'] && file_exists($oldImageRow['image_path'])) {
                    unlink($oldImageRow['image_path']);
                }
            }
            } else {
            // Keep the existing image if no new image is uploaded
            $imageQuery = "SELECT image_path FROM users_info WHERE id = $id";
            $imageResult = mysqli_query($conn, $imageQuery);
            $imageRow = mysqli_fetch_assoc($imageResult);
            $imagePath = $imageRow['image_path'];
            }

      
          // Update database
            $query = "UPDATE users_info SET name='$name', email='$email', number='$number', image_path='$imagePath' WHERE id=$id";

            if (mysqli_query($conn, $query)) {
                header("Location: homepage.php?status=success&message=Client updated successfully");
            } else {
                header("Location: homepage.php?status=error&message=" . urlencode(mysqli_error($conn)));
            }
        }

        // Delete Client
        elseif ($action === 'delete') {
            $id = mysqli_real_escape_string($conn, $_POST['id']);
            $query = "DELETE FROM users_info WHERE id=$id";

            if (mysqli_query($conn, $query)) {
                header("Location: homepage.php?status=success&message=Client deleted successfully");
            } else {
                header("Location: homepage.php?status=error&message=" . urlencode(mysqli_error($conn)));
            }
        } else {
            header("Location: homepage.php?status=error&message=Invalid action");
        }
    } else {
        header("Location: homepage.php?status=error&message=Action not specified");
    }
}

// Handle fetching client data for editing (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM users_info WHERE id=$id";
    $result = mysqli_query($conn, $query);
    $client = mysqli_fetch_assoc($result);

    if (!$client) {
        header("Location: homepage.php?status=error&message=Client not found");
        exit;
    }

    // Display the edit form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Client</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5 pt-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Edit Client</h5>
                </div>
                <div class="card-body">
                <form action="Clients.php" method="POST" onsubmit="return confirmEdit()" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $client['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $client['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="number" class="form-label">Number</label>
                        <input type="tel" class="form-control" id="number" name="number" value="<?php echo $client['number']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($client['image_path']): ?>
                            <img src="<?php echo $client['image_path']; ?>" alt="Current Image" style="max-width: 100px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>

                </div>
            </div>
        </div>

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Function to validate the edit form
            function validateForm() {
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const number = document.getElementById('number').value;

                if (!name || !email || !number) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all fields!'
                    });
                    return false;
                }
                return true;
            }

            // Function to validate and confirm edit form submission
            async function confirmEdit() {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save the changes to this client?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save changes!',
                cancelButtonText: 'Cancel'
            });
            return result.isConfirmed; // Ensures form submission only when confirmed
        }



    <form action="Clients.php" method="POST" onsubmit="return confirmEdit()">
            </script>
        </body>
        </html>
        <?php
        exit;
}

mysqli_close($conn);