<?php
require 'db_operations.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $bookId = $_GET['id'];

    $query = "SELECT * FROM books WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $bookId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $book = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($book) {
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'Book not found']);
    }
} else {
    echo json_encode(['error' => 'Book ID not provided']);
}
?>