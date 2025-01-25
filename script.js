document.addEventListener('DOMContentLoaded', function () {
    fetchTokens();
    fetchBooks();
    fetchUsedTokens();
    populateBookDropdown();
    fetchTopBooks();
});

// Function to fetch and display available and used tokens
function fetchTokens() {
    fetch('process.php?fetchTokens=1')
        .then(response => response.json())
        .then(data => {
            document.getElementById('available-tokens').innerText = data.availableTokens.join(', ');
        })
        .catch(error => console.error('Error fetching tokens:', error));
}

// Function to fetch and display books in a table
function fetchBooks() {
    fetch('getBooks.php')
        .then(response => response.json())
        .then(books => {
            const bookTableBody = document.querySelector('#book-table tbody');
            bookTableBody.innerHTML = ''; // Clear existing rows

            books.forEach(book => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${book.title}</td>
                    <td>${book.isbn}</td>
                    <td>${book.author}</td>
                    <td>${book.quantity}</td>
                    <td>${book.category}</td>
                    <td><button class="edit-btn" data-id="${book.id}">Edit</button></td>
                `;
                bookTableBody.appendChild(row);
            });

            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const bookId = this.getAttribute('data-id');
                    populateUpdateForm(bookId);
                });
            });
        })
        .catch(error => console.error('Error fetching books:', error));
}

// Function to fetch and display the top 3 books with maximum quantity
function fetchTopBooks() {
    fetch('getTopBooks.php')
        .then(response => response.json())
        .then(books => {
            const cardDiv = document.querySelector('.card-div');
            cardDiv.innerHTML = ''; // Clear existing content

            books.forEach(book => {
                const bookDiv = document.createElement('div');
                bookDiv.className = 'three-div';
                bookDiv.innerHTML = `
                    <img src="${book.image}" alt="${book.title}" class="book-img">
                `;
                cardDiv.appendChild(bookDiv);
            });
        })
        .catch(error => console.error('Error fetching top books:', error));
}

// Function to fetch and display used tokens
function fetchUsedTokens() {
    fetch('getUsedTokens.php')
        .then(response => response.json())
        .then(data => {
            const usedTokensDiv = document.getElementById('used-token-left-div');
            usedTokensDiv.innerHTML = "<h3>Used Tokens</h3>";
            if (data.length > 0) {
                data.forEach(token => {
                    usedTokensDiv.innerHTML += `<p>${token}</p>`;
                });
            } else {
                usedTokensDiv.innerHTML += "<p>No tokens used yet.</p>";
            }
        })
        .catch(error => console.error('Error fetching used tokens:', error));
}

// Function to populate the "Choose a book" dropdown
function populateBookDropdown() {
    fetch('getBooks.php')
        .then(response => response.json())
        .then(books => {
            const bookDropdown = document.getElementById('books');
            bookDropdown.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select a book';
            bookDropdown.appendChild(defaultOption);

            // Add books to the dropdown
            books.forEach(book => {
                const option = document.createElement('option');
                option.value = book.title;
                option.textContent = book.title;
                bookDropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching books for dropdown:', error));
}

// Function to populate the update form with book details
function populateUpdateForm(bookId) {
    fetch(`getBookById.php?id=${bookId}`)
        .then(response => response.json())
        .then(book => {
            document.getElementById('update-id').value = book.id;
            document.getElementById('update-title').value = book.title;
            document.getElementById('update-isbn').value = book.isbn;
            document.getElementById('update-author').value = book.author;
            document.getElementById('update-quantity').value = book.quantity;
            document.getElementById('update-category').value = book.category;
        })
        .catch(error => console.error('Error fetching book details:', error));
}