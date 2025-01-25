<?php
require 'db_operations.php';

header('Content-Type: application/json');

$books = getBooks();

echo json_encode($books);
?>