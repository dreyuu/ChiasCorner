<?php

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

?>

<link rel="stylesheet" href="css/account.css">

<div class="container">
    <div class="form-container">
        <form id="addUserForm" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <select name="user_type" required>
                <option value="admin">Admin</option>
                <option value="waiter">Waiter</option>
                <option value="cashier">Cashier</option>
            </select>
            <button type="submit" id="add-user">SUBMIT</button>
        </form>
    </div>

    <div class="table-container">
        <div class="search-container">
            <select>
                <option>Sort by...</option>
                <option>Name</option>
                <option>User Type</option>
            </select>
            <input type="text" id="search" placeholder="Search..." onkeyup="searchTable()">
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
        e.preventDefault();

        const formData = new FormData(this);

        fetch('db_queries/insert_queries/insert_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    this.reset(); // Clear the form after successful submission
                    fetchUser();
                }
            })
            .catch(error => console.error('Error:', error));
    });




    function removeRow(button) {
        let row = button.parentElement.parentElement;
        row.remove();
    }

    function editRow(button) {
        let row = button.parentElement.parentElement;
        let isEditing = button.innerText === "Save";

        let cells = row.querySelectorAll("td:not(:last-child)");
        cells.forEach(cell => {
            cell.contentEditable = !isEditing;
        });

        if (isEditing) {
            button.innerText = "Edit";
        } else {
            button.innerText = "Save";
        }
    }

    function searchTable() {
        let input = document.getElementById("search").value.toLowerCase();
        let table = document.getElementById("userTable");
        let rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) {
            let rowText = rows[i].innerText.toLowerCase();
            if (rowText.includes(input)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }

    function fetchUser() {
        fetch('db_queries/select_queries/fetch_user.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userTableBody = document.getElementById("userTableBody");
                    userTableBody.innerHTML = ""; // Clear table before adding new data

                    let itemCount = 0;

                    data.data.forEach(item => {

                        const row = document.createElement("tr");

                        row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.username}</td>
                        <td>${item.email}</td>
                        <td>${item.user_type}</td>
                        <td>${item.date_created}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editItem(${item.ingredient_id})">Edit</button>
                            <button class="action-btn remove-btn" onclick="removeItem(${item.ingredient_id})">Remove</button>
                        </td>
                    `;

                        userTableBody.appendChild(row);
                    });

                } else {
                    alert("Failed to load inventory: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
    }
    fetchUser();
</script>

</body>

</html>
