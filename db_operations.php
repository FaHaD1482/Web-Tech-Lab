<?php
$conn = mysqli_connect("localhost", "root", "", "book_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to add a book
function addBook($title, $isbn, $author, $quantity, $category, $image)
{
    global $conn;
    $query = "INSERT INTO books (title, isbn, author, quantity, category, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssiss", $title, $isbn, $author, $quantity, $category, $image);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Function to update a book
function updateBook($id, $title, $author, $isbn, $quantity, $category, $image)
{
    global $conn;
    $query = "UPDATE books SET title=?, author=?, isbn=?, quantity=?, category=?, image=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssissi", $title, $author, $isbn, $quantity, $category, $image, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Function to fetch all books
function getBooks()
{
    global $conn;
    $query = "SELECT * FROM books";
    $result = mysqli_query($conn, $query);
    $books = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    return $books;
}

// Function to fetch available and used tokens
function getTokens()
{
    $tokens = json_decode(file_get_contents("token.json"), true);
    $availableTokens = $tokens[0]["tokens"];
    $usedTokens = [];
    return [
        'availableTokens' => $availableTokens,
        'usedTokens' => $usedTokens
    ];
}
