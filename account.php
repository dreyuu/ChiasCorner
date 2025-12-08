<?php

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

?>
<link rel="stylesheet" href="css/account.css">

<!-- Font Awesome Free (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="container">
    <div class="form-container">
        <h2>Account Management</h2>
        <form id="addUserForm" method="POST">
            <input type="hidden" name="user_id" id="user_id" placeholder="user_id">
            <input type="text" name="name" id="name" placeholder="Full Name" required>
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password">
            <input type="email" name="email" id="email" placeholder="Email Address" required>
            <select name="user_type" id="user_type" required>
                <option value="admin">Admin</option>
                <option value="employee" selected>Employee</option>
                <option value="dev" hidden>developer</option>
            </select>
            <select name="status" id="status" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit" id="add-user">SUBMIT</button>
            <button type="submit" id="cancel">Cancel</button>
        </form>
    </div>

    <div class="table-container">
        <div class="search-container-section">
            <div class="search-left">
                <select id="sortAccount">
                    <option>Sort by...</option>
                    <option>Name</option>
                    <option>User Type</option>
                </select>
                <input type="text" id="search" placeholder="Search...">
            </div>
            <div class="search-right">
                <button id="open-generate-pin-modal"><i class="fa-solid fa-key"></i></button>
                <button id="open-database-modal"><i class="fa-solid fa-database"></i></button>
            </div>
        </div>

        <div class="paginate-user-table">
            <table class="user-Table">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Status</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">

                </tbody>
            </table>
            <div class="pagination-container">
                <button id="prevPage">Previous</button>
                <span id="pageInfo"></span>
                <button id="nextPage">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- <div class="backup-container">
    <button class="export-btn" id="export-btn">⬇ Export Database Backup</button>

    <form id="restoreForm" enctype="multipart/form-data">
        <div class="restore-container">
            <label for="backupFile" class="file-label">
                <span id="file-label-text">📁 Choose SQL file</span>
                <input type="file" id="backupFile" name="backupFile" accept=".sql" hidden />
            </label>
            <button type="submit" class="restore-btn">🔁 Restore Database</button>
        </div>
    </form>
</div> -->


<div class="database-modal">
    <div class="database-overlay"></div>
    <div class="database-modal-content">
        <span class="close-database-modal">&times;</span>
        <h2>Database Backup & Restore</h2>
        <p>Backup and restore your database to ensure data safety and integrity.</p>
        <div class="export-section">
            <button class="export-btn" id="export-btn">⬇ Export Database Backup</button>

            <form id="restoreForm" enctype="multipart/form-data">
                <div class="restore-container">
                    <label for="backupFile" class="file-label">
                        <span id="file-label-text">📁 Choose SQL file</span>
                        <input type="file" id="backupFile" name="backupFile" accept=".sql" hidden />
                    </label>
                    <button type="submit" class="restore-btn">🔁 Restore Database</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="pin-modal">
    <div class="pin-overlay"></div>
    <div class="pin-modal-content">
        <span class="close-pin-modal">&times;</span>
        <h2>Generate PIN</h2>
        <p>Your new PIN is:</p>
        <div class="generated-pin" id="generated-pin">1234</div>
        <button class="copy-pin-btn" id="copy-pin-btn"><i class="fa-regular fa-copy"></i></button>
        <div class="generate-pin-btn" id="generate-pin-btn">Generate New Pin</div>
    </div>

</div>



<div class="alerts">
    <div id="success-alert" class="alert alert-success"></div>
    <div id="error-alert" class="alert alert-danger"></div>
    <div id="warning-alert" class="alert alert-warning"></div>
</div>


<div class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openDatabaseModalBtn = document.getElementById('open-database-modal');
        const databaseModal = document.querySelector('.database-modal');
        const closeDatabaseModalBtn = document.querySelector('.close-database-modal');

        openDatabaseModalBtn.addEventListener('click', () => {
            databaseModal.classList.add('is-visible');
        });

        closeDatabaseModalBtn.addEventListener('click', () => {
            databaseModal.classList.remove('is-visible');
        });

        // Optional: Close the modal when clicking outside the content
        databaseModal.addEventListener('click', (event) => {
            if (event.target === databaseModal.querySelector('.database-overlay')) {
                databaseModal.classList.remove('is-visible');
            }
        });

        const openPinModalBtn = document.getElementById('open-generate-pin-modal');
        const pinModal = document.querySelector('.pin-modal');
        const closePinModalBtn = document.querySelector('.close-pin-modal');
        const generatedPinElement = document.getElementById('generated-pin');
        const copyPinBtn = document.getElementById('copy-pin-btn');
        const generatePinBtn = document.getElementById('generate-pin-btn');
        let currentPin = '';

        function generateNewPin() {
            currentPin = Math.floor(1000 + Math.random() * 9000).toString();
            generatedPinElement.textContent = currentPin;
        }
        generatePinBtn.addEventListener('click', function(e) {
            e.preventDefault();

            CustomAlert.confirm("Are you sure you want to generate a new PIN?", "warning")
                .then(result => {
                    if (!result) return;
                    GeneratePin();
                });
        });

        copyPinBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(currentPin).then(() => {
                // alert('PIN copied to clipboard!');
                showAlert('success-alert', 'PIN copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy PIN: ', err);
            });
        });
        // Generate an initial PIN when the modal is opened
        // openPinModalBtn.addEventListener('click', generateNewPin);

        openPinModalBtn.addEventListener('click', () => {
            pinModal.classList.add('is-visible');
        });
        closePinModalBtn.addEventListener('click', () => {
            pinModal.classList.remove('is-visible');
        });
        pinModal.addEventListener('click', (event) => {
            if (event.target === pinModal.querySelector('.pin-overlay')) {
                pinModal.classList.remove('is-visible');
            }
        });


        async function GeneratePin() {
            try {
                const token = localStorage.getItem("jwt_token");
                if (!token) {
                    window.location.href = 'index.php';
                    return;
                }

                // Decode JWT
                const base64 = token.split('.')[1];
                const json = atob(base64);
                const payload = JSON.parse(json);

                const userID = payload.user_id;

                // Send POST request to PHP
                const response = await fetch('db_queries/insert_queries/generate_pin.php', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        user_id: userID
                    })
                });

                const data = await response.json();

                if (data.success) {
                    generatedPinElement.textContent = data.pin;
                    currentPin = data.pin;
                } else {
                    console.error("Failed to generate PIN:", data.message);
                }
            } catch (error) {
                console.error("Error generating PIN:", error);
            }
        }

        async function loadPin() {
            try {
                const token = localStorage.getItem("jwt_token");
                if (!token) {
                    window.location.href = 'index.php';
                    return;
                }

                // Decode token
                const base64 = token.split('.')[1];
                const json = atob(base64);
                const payload = JSON.parse(json);

                const userID = payload.user_id;

                // Fetch existing PIN
                const response = await fetch('db_queries/select_queries/load_pin.php', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        user_id: userID
                    })
                });

                const data = await response.json();

                if (data.success) {
                    generatedPinElement.textContent = data.pin ? data.pin : "No PIN generated.";
                    currentPin = data.pin ? data.pin : null;
                } else {
                    generatedPinElement.textContent = "Error loading PIN";
                    console.error("PIN Load Error:", data.message);
                }
            } catch (error) {
                console.error("Error loading PIN:", error);
            }
        }
        loadPin();

    })

    function showAlert(alertId, message) {
        let alertBox = document.getElementById(alertId);
        if (alertBox) {
            alertBox.innerText = message;
            alertBox.style.visibility = "visible";
            alertBox.style.opacity = "1";
            alertBox.style.top = "0";

            clearTimeout(alertBox.hideTimeout);

            alertBox.hideTimeout = setTimeout(() => {
                alertBox.style.opacity = "0";
                alertBox.style.top = "-70px";

                setTimeout(() => {
                    alertBox.style.visibility = "hidden";
                    alertBox.innerText = "";
                }, 300); // Delay to allow fade-out
            }, 3000); // Display duration
        }
    }

    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        const token = localStorage.getItem("jwt_token");

        if (!token) {
            // alert('Restricted Access, Amin only')
            showAlert('warning-alert', 'Restricted Access, Amin only')
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
        loader.show();
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
                // alert(data.message);
                showAlert(`${data.status}-alert`, data.message)
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
            }).finally(() => {
                loader.hide();
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

    // let previousUserData = [];

    let currentPage = 1;
    let limit = 10; // rows per page
    let totalRows = 0;

    async function fetchUser(page = 1) {
        currentPage = page
        try {
            const response = await fetch('db_queries/select_queries/fetch_user.php', {
                method: "POST",
                headers: {
                    "Content-Type": "application.json"
                },
                body: JSON.stringify({
                    page,
                    limit
                })
            });
            const data = await response.json();

            if (data.success) {
                // if (!isEqual(previousUserData, data.data)) {
                //     previousUserData = data.data; // Update stored data
                //     updateUserTable(data.data); // Update UI only if data changed
                // }
                totalRows = data.totalRows
                updateUserTable(data.data); // Update UI only if data changed
                updatePaginationUI();
            } else {
                // alert("Failed to load user data: " + data.message);
                CustomAlert.alert("Failed to load user data: " + data.message, "error");
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
            <td>
                <span class="${item.status}">
                    ${item.status}
                </span>
            </td>
            <td>${item.date_created}</td>
            <td>
                <div class="actions">
                    <button class="action-btn edit-btn edit-account" data-user='${JSON.stringify(item)}'>
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="action-btn remove-btn remove-account" data-id="${item.user_id}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

            userTableBody.appendChild(row);
        });
    }

    function updatePaginationUI() {
        const totalPages = Math.ceil(totalRows / limit);

        document.getElementById("pageInfo").innerText =
            `Page ${currentPage} of ${totalPages}`;

        document.getElementById("prevPage").disabled = currentPage === 1;
        document.getElementById("nextPage").disabled = currentPage === totalPages;
    }

    document.getElementById("prevPage").addEventListener("click", () => {
        if (currentPage > 1) {
            fetchUser(currentPage - 1);
        }
    });

    document.getElementById("nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(totalRows / limit);
        if (currentPage < totalPages) {
            fetchUser(currentPage + 1);
        }
    });


    // Deep comparison for object arrays
    function isEqual(arr1, arr2) {
        return JSON.stringify(arr1) === JSON.stringify(arr2);
    }

    // Optional: Auto-refresh every 3 seconds
    // setInterval(fetchUser, 5000);

    // Initial fetch
    fetchUser();


    document.addEventListener('DOMContentLoaded', function() {
        const addUserBtn = document.getElementById('add-user');

        document.getElementById('userTableBody').addEventListener('click', function(e) {
            if (e.target.closest('.edit-btn')) {
                const userData = JSON.parse(e.target.closest('.edit-btn').dataset.user); // Get user data directly from the button
                addUserBtn.textContent = "UPDATE"; // Change button text to "UPDATE"
                populateAccountForm(userData); // Populate form with the user data
            }

            if (e.target.closest('.remove-btn')) {
                const userId = e.target.closest('.remove-btn').dataset.id;

                const formData = new FormData();
                formData.append('user_id', userId);

                CustomAlert.confirm("Are you sure you want to remove this account?", "warning")
                    .then(result => {
                        if (!result) return;
                        loader.show();
                        fetch('db_queries/delete_queries/remove_user.php', {
                                method: "POST",
                                body: formData,
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {

                                    showAlert(`${data.status}-alert`, data.message)
                                    fetchUser(); // Refresh user list
                                    document.getElementById('user_id').value = '';
                                    document.getElementById('name').value = '';
                                    document.getElementById('username').value = '';
                                    document.getElementById('password').value = '';
                                    document.getElementById('email').value = '';
                                    document.getElementById('add-user').textContent = 'SUBMIT'; // Reset button text
                                } else {
                                    CustomAlert.alert("Failed to remove the account: " + data.message, "error");
                                }
                            })
                            .catch(error => console.error("Error:", error))
                            .finally(() => {
                                loader.hide();
                            });
                    });
            }
        });

        function populateAccountForm(data) {

            try {
                if (!data || !data.user_id) {
                    CustomAlert.alert("Invalid user data provided", "error");
                    return
                }

                document.getElementById('user_id').value = data.user_id;
                document.getElementById('name').value = data.name;
                document.getElementById('username').value = data.username;
                document.getElementById('email').value = data.email;
                document.getElementById('user_type').value = data.user_type;
                document.getElementById('status').value = data.status;
            } catch (error) {
                CustomAlert.alert("Error populating form: " + error.message, "error");
            }

            // fetch('db_queries/select_queries/fetch_user.php', {
            //         method: 'POST',
            //         body: JSON.stringify({
            //             userId: userId // Change user_id to userId to match PHP parameter
            //         }),
            //         headers: {
            //             'Content-Type': 'application/json', // Set the correct content type
            //         }
            //     })
            //     .then(response => response.json())
            //     .then(data => {
            //         if (data.success) {
            //             // Accessing user data and populating the form
            //             const user = data.data; // Since you're returning only one user, no need to use [0]
            //             document.getElementById('user_id').value = user.user_id;
            //             document.getElementById('name').value = user.name;
            //             document.getElementById('username').value = user.username;
            //             document.getElementById('email').value = user.email;
            //             document.getElementById('user_type').value = user.user_type;
            //             document.getElementById('status').value = user.status;
            //         } else {
            //             // alert("Failed to load user data: " + data.message);
            //             CustomAlert.alert("Failed to load user data: " + data.message, "error");
            //         }
            //     })
            //     .catch(error => console.error("Error:", error));
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

    const exportBtn = document.getElementById('export-btn');

    exportBtn.addEventListener('click', function() {
        const token = localStorage.getItem("jwt_token");

        if (!token) {
            // alert('Restricted Access, Amin only')
            showAlert('warning-alert', 'Restricted Access, Amin only')
            return
        }

        CustomAlert.confirm("Are you sure you want to export the database backup?", "warning")
            .then(result => {
                if (!result) return;

                loader.show();

                fetch('inc/export_backup.php', {
                        method: 'POST',
                        headers: {
                            "Authorization": `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Create a link to download the file
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'chias_corner.sql'; // Set the desired file name
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                        console.error('Error during backup:', error);
                    })
                    .finally(() => {
                        loader.hide();
                    });

            });

    });

    // Update label when a file is selected
    document.getElementById('backupFile').addEventListener('change', function() {
        const labelText = document.getElementById('file-label-text');
        labelText.textContent = this.files.length > 0 ?
            `✅ ${this.files[0].name}` :
            '📁 Choose SQL file';
    });

    // Handle form submit
    document.getElementById('restoreForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const token = localStorage.getItem("jwt_token");

        if (!token) {
            // alert('Restricted Access, Amin only')
            showAlert('warning-alert', 'Restricted Access, Amin only')
            return
        }

        const fileInput = document.getElementById('backupFile');
        if (!fileInput.files.length) {
            // alert('Please select a backup file first.');
            CustomAlert.alert('Please select a backup file first.', 'warning');
            return;
        }

        if (!CustomAlert.confirm('Restoring the database will overwrite existing data. Are you sure you want to proceed?', 'warning')) {
            return;
        }

        CustomAlert.confirm("Are you sure you want to delete this?", "warning")
            .then(result => {
                if (!result) return;

                const formData = new FormData(this);

                showAlert('success-alert', '⏳ Restoring database, please wait...')
                loader.show();
                fetch('inc/restore_backup.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert('success-alert', `✅ ${data.message}`);
                        } else {
                            showAlert('error-alert', `❌ ${data.message}`);
                        }
                    })
                    .catch(err => {
                        showAlert('error-alert', `⚠️ Error: ${err.message}`);
                    })
                    .finally(() => {
                        // Reset the form and label
                        this.reset();
                        document.getElementById('file-label-text').textContent = '📁 Choose SQL file';
                        loader.hide();
                    });
            });
    });

    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('users-channel', 'modify-user', () => fetchUser(currentPage), 200);
</script>

</body>

</html>
