<?php
$conn = mysqli_connect("localhost", "root", "", "book_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to handle file upload (if needed)
function handleFileUpload($file) {
    $targetDir = "images/";
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "File already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 2MB)
    if ($file["size"] > 2000000) {
        echo "File is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
        echo "Only JPG, JPEG, and PNG files are allowed.";
        $uploadOk = 0;
    }

    // If all checks pass, upload the file
    if ($uploadOk == 1) {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            echo "Error uploading file.";
            return null;
        }
    } else {
        return null;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $title = trim($_POST['bookTitle']);
    $isbn = trim($_POST['isbnNo']);
    $author = trim($_POST['author']);
    $quantity = trim($_POST['quantity']);
    $category = trim($_POST['category']);
    $image = "";

    // Handle file upload (if an image is provided)
    if (isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] == 0) {
        $image = handleFileUpload($_FILES['bookImage']);
        if ($image === null) {
            die("Error uploading image.");
        }
    }

    // Validate inputs
    if (empty($title) || empty($isbn) || empty($author) || empty($quantity) || empty($category)) {
        echo "<p style='color: red;'>Please fill all required fields.</p>";
    } else {
        $query = "INSERT INTO books (title, isbn, author, quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssiss", $title, $isbn, $author, $quantity, $category, $image);

        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>Book added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error adding book: " . mysqli_error($conn) . "</p>";
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>