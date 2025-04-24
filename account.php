<?php

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

?>

<link rel="stylesheet" href="css/account.css">

<div class="container">
    <div class="form-container">
        <h2>Account Management</h2>
        <form id="addUserForm" method="POST">
            <input type="hidden" name="user_id" id="user_id" placeholder="user_id">
            <input type="text" name="name" id="name" placeholder="Full Name" required>
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="email" name="email" id="email" placeholder="Email Address" required>
            <select name="user_type" id="user_type" required>
                <option value="admin">Admin</option>
                <option value="employee" selected>Employee</option>
            </select>
            <button type="submit" id="add-user">SUBMIT</button>
            <button type="submit" id="cancel">Cancel</button>
        </form>
    </div>

    <div class="table-container">
        <div class="search-container">
            <select id="sortAccount">
                <option>Sort by...</option>
                <option>Name</option>
                <option>User Type</option>
            </select>
            <input type="text" id="search" placeholder="Search...">
        </div>

        <table class="user-Table">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">

            </tbody>
        </table>
    </div>
</div>

<div class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</div>

<script>
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        const token = localStorage.getItem("jwt_token");

        if (!token) {
            alert('Restricted Access, Amin only')
            return
        }

        const addUserBtn = document.getElementById('add-user');
        const userId = document.getElementById('user_id').value;
        let url = ''; // URL for the request

        // Verify userId value in the form
        console.log('User ID:', userId);

        if (userId) {
            url = 'db_queries/update_queries/update_user.php'; // Use this URL for updates
            console.log('update');
        } else {
            url = 'db_queries/insert_queries/insert_user.php'; // Use this URL for inserts
            console.log('insert');
        }

        const formData = new FormData(this); // Collect form data
        // Convert the FormData to a plain object for easier handling
        const formObject = {};
        formData.forEach((value, key) => {
            formObject[key] = value;
        });

        // Log the formObject to verify data being sent
        // console.log(formObject);

        fetch(url, {
                method: 'POST',
                headers: {

                    'Content-Type': 'application/json', // Send JSON content type
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify(formObject) // Send form data as JSON
            })
            .then(response => response.json()) // Parse the JSON response
            .then(data => {
                alert(data.message);
                if (data.success) {
                    this.reset(); // Clear the form after successful submission
                    fetchUser(); // Update the user list or display
                    document.getElementById('user_id').value = ''; // Clear user_id field
                    addUserBtn.textContent = 'Submit'; // Reset button text
                }
            })
            .catch(error => {
                console.log(`Error: ${error.message}`);
                console.error('Error:', error);
            });
    });


    const cancel = document.getElementById('cancel');

    if (cancel) {
        cancel.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('user_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('email').value = '';
            document.getElementById('add-user').textContent = 'SUBMIT'; // Reset button text
        })
    }

    let previousUserData = [];

    async function fetchUser() {
        try {
            const response = await fetch('db_queries/select_queries/fetch_user.php');
            const data = await response.json();

            if (data.success) {
                if (!isEqual(previousUserData, data.data)) {
                    previousUserData = data.data; // Update stored data
                    updateUserTable(data.data); // Update UI only if data changed
                }
            } else {
                alert("Failed to load user data: " + data.message);
            }

        } catch (error) {
            console.error("Error fetching user data:", error);
        }
    }

    function updateUserTable(users) {
        const userTableBody = document.getElementById("userTableBody");
        userTableBody.innerHTML = ""; // Clear existing rows

        users.forEach(item => {
            const row = document.createElement("tr");

            row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.username}</td>
            <td>${item.email}</td>
            <td>${item.user_type}</td>
            <td>${item.date_created}</td>
            <td>
                <div class="actions">
                    <button class="action-btn edit-btn edit-account" data-id="${item.user_id}">Update</button>
                    <button class="action-btn remove-btn remove-account" data-id="${item.user_id}">Remove</button>
                </div>
            </td>
        `;

            userTableBody.appendChild(row);
        });
    }

    // Deep comparison for object arrays
    function isEqual(arr1, arr2) {
        return JSON.stringify(arr1) === JSON.stringify(arr2);
    }

    // Optional: Auto-refresh every 3 seconds
    setInterval(fetchUser, 5000);

    // Initial fetch
    fetchUser();


    document.addEventListener('DOMContentLoaded', function() {
        const addUserBtn = document.getElementById('add-user');
        document.getElementById('userTableBody').addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-account')) {
                const userId = e.target.dataset.id;
                addUserBtn.textContent = "UPDATE";
                // call populate table
                populateAccountForm(userId);
            }

            if (e.target.classList.contains('remove-account')) {
                const userId = e.target.dataset.id;

                if (confirm("Are you sure you want to remove this account?")) {
                    // Send a request to the server to remove the account
                    fetch(`db_queries/delete_queries/remove_user.php?id=${userId}`, {
                            method: "DELETE", // Or use POST depending on your API
                            headers: {
                                "Content-Type": "application/json"
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the row from the table after successful deletion
                                fetchUser();
                            } else {
                                alert("Failed to remove the account: " + data.message);
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            }
        })

        function populateAccountForm(userId) {
            fetch('db_queries/select_queries/fetch_user.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        userId: userId // Change user_id to userId to match PHP parameter
                    }),
                    headers: {
                        'Content-Type': 'application/json', // Set the correct content type
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Accessing user data and populating the form
                        const user = data.data; // Since you're returning only one user, no need to use [0]
                        document.getElementById('user_id').value = user.user_id;
                        document.getElementById('name').value = user.name;
                        document.getElementById('username').value = user.username;
                        document.getElementById('email').value = user.email;
                        document.getElementById('user_type').value = user.user_type;
                    } else {
                        alert("Failed to load user data: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        }


        const input = document.getElementById("search");
        const table = document.querySelector(".user-Table");
        const sortAccount = document.getElementById('sortAccount');
        const tableBody = document.getElementById('userTableBody');
        // Function to search the table
        function searchTable() {
            const filter = input.value.toLowerCase();
            const rows = table.querySelectorAll("tr");

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(filter) ? "" : "none";
            })
        }

        function sortAccountTable() {
            const sortValue = document.getElementById('sortAccount').value; // Get the selected sort value
            const table = document.querySelector(".user-Table");
            const tableBody = table.querySelector("tbody"); // Get the tbody element
            const rows = Array.from(tableBody.querySelectorAll("tr")); // Get all rows in tbody

            // Sort rows based on the selected sort value
            rows.sort((rowA, rowB) => {
                let valueA, valueB;

                // Extract values based on the selected sort option
                switch (sortValue) {
                    case 'Name':
                        valueA = rowA.cells[0].innerText.toLowerCase(); // Name is in the first column
                        valueB = rowB.cells[0].innerText.toLowerCase();
                        break;
                    case 'User Type':
                        valueA = rowA.cells[3].innerText.toLowerCase(); // User type is in the fourth column
                        valueB = rowB.cells[3].innerText.toLowerCase();
                        break;
                    default:
                        return 0; // No sorting if the option is "Sort by..."
                }

                // Compare values for sorting (ascending order)
                if (valueA < valueB) return -1; // Ascending order
                if (valueA > valueB) return 1; // Descending order
                return 0; // If values are equal, no change
            });

            // Re-append the sorted rows without clearing the table first
            rows.forEach(row => tableBody.appendChild(row));
        }


        sortAccount.addEventListener('change', sortAccountTable);
        input.addEventListener("input", searchTable);
    });
</script>

</body>

</html>
