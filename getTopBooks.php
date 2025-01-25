<?php
require 'db_operations.php';

header('Content-Type: application/json');

// Fetch the top 3 books with the maximum quantity
$query = "SELECT * FROM books ORDER BY quantity DESC LIMIT 3";
$result = mysqli_query($conn, $query);

$books = [];
while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

echo json_encode($books);

mysqli_close($conn);
?>