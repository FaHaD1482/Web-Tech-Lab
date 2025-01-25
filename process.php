<?php
require 'db_operations.php';

// Function to update used tokens
function updateUsedTokens($token) {
    $usedTokensFile = "used_tokens.json";
    $usedTokens = [];

    // Load existing used tokens
    if (file_exists($usedTokensFile)) {
        $usedTokens = json_decode(file_get_contents($usedTokensFile), true);
    }

    // Add the new token to the used tokens list
    if (!in_array($token, $usedTokens)) {
        $usedTokens[] = $token;
        file_put_contents($usedTokensFile, json_encode($usedTokens));
    }
}

// Function to update book quantity in the database
function updateBookQuantity($bookName, $isbn, $change) {
    global $conn;
    $query = "UPDATE books SET quantity = quantity + ? WHERE title = ? AND isbn = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iss", $change, $bookName, $isbn);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Handle borrow book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $name = trim($_POST['stdName']);
    $studentID = trim($_POST['stdID']);
    $bookName = trim($_POST['books']);
    $isbn = trim($_POST['isbn']);
    $borrowDate = trim($_POST['borrowDate']);
    $returnDate = trim($_POST['returnDate']);
    $token = trim($_POST['token']);
    $fees = trim($_POST['fees']);

    // Fetch book details from the database using book name and ISBN
    $query = "SELECT title, isbn, quantity FROM books WHERE title = ? AND isbn = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $bookName, $isbn);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $book = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$book) {
        echo "<p style='color: red;'>Invalid book selected. The book name and ISBN do not match.</p>";
        exit;
    }

    $bookTitle = $book['title'];
    $bookQuantity = $book['quantity'];

    $borrowDuration = (strtotime($returnDate) - strtotime($borrowDate)) / (60 * 60 * 24);

    // Validate inputs
    if (empty($name) || empty($studentID) || empty($bookName) || empty($isbn) || empty($borrowDate) || empty($returnDate) || empty($fees)) {
        echo "<p style='color: red;'>Please fill all required inputs.</p>";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        echo "<p style='color: red;'>Student Full Name cannot contain numbers or special characters.</p>";
    } elseif (!preg_match("/^\d{2}-\d{5}-\d{1}$/", $studentID)) {
        echo "<p style='color: red;'>Student ID must be in the format xx-xxxxx-x, where x is a digit.</p>";
    } elseif (strtotime($returnDate) <= strtotime($borrowDate)) {
        echo "<p style='color: red;'>Return Date cannot be earlier than Borrow Date.</p>";
    } elseif ($bookQuantity <= 0) {
        echo "<p style='color: red;'>The book \"$bookTitle\" is out of stock.</p>";
    } else {
        // Check if borrow duration is greater than 7 days
        if ($borrowDuration > 7) {
            // Token is required for borrow duration > 7 days
            $tokensArray = json_decode(file_get_contents("token.json"), true);
            $validTokens = isset($tokensArray[0]["tokens"]) ? $tokensArray[0]["tokens"] : [];

            // Check if token is valid
            $found = in_array((int)$token, $validTokens);

            if (empty($token)) {
                echo "<p style='color: red;'>Token is required for borrow duration greater than 7 days.</p>";
            } elseif (!$found) {
                echo "<p style='color: red;'>Invalid token. Please use a valid token to borrow a book.</p>";
            } else {
                // Token is valid, proceed with borrowing
                updateBookQuantity($bookName, $isbn, -1);
                updateUsedTokens($token);
                echo "<p style='color: green;'>Form submitted successfully!</p>";
                echo "<p>Book Borrowed: $bookTitle</p>";
                echo "<p>Borrowed by: $name</p>";
                echo "<p>Borrowed Date: $borrowDate</p>";
                echo "<p>Return Date: $returnDate</p>";
            }
        } else {
            // Token is optional for borrow duration <= 7 days
            updateBookQuantity($bookName, $isbn, -1);
            echo "<p style='color: green;'>Form submitted successfully!</p>";
            echo "<p>Book Borrowed: $bookTitle</p>";
            echo "<p>Borrowed by: $name</p>";
            echo "<p>Borrowed Date: $borrowDate</p>";
            echo "<p>Return Date: $returnDate</p>";
        }
    }
}

// Handle add book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addBook'])) {
    $title = $_POST['bookTitle'];
    $isbn = $_POST['isbnNo'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];
    $image = $_POST['image'];

    addBook($title, $isbn, $author, $quantity, $category, $image);
    echo "Book added successfully!";
}

// Handle update book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateBook'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];

    // Update book in the database
    $query = "UPDATE books SET title=?, isbn=?, author=?, quantity=?, category=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssisi", $title, $isbn, $author, $quantity, $category, $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<p style='color: green;'>Book updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating book.</p>";
    }

    mysqli_stmt_close($stmt);
}

// Handle token fetch request
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fetchTokens'])) {
    $tokens = getTokens();
    echo json_encode($tokens);
    exit;
}
?>