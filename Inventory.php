<?php

include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

?>

<link rel="stylesheet" href="css/inventory.css">


<div class="custom-modal-overlay"></div>

<div class="custom-ingredients-modal">
    <button class="custom-ingredients-back">Back</button>

    <div class="custom-ingredients-content">
        <!-- Left: Ingredients List -->
        <div class="custom-ingredients-left">
            <form id="addIngredientForm" method="POST">
                <input type="hidden" name="ingredient_id" id="ingredient_id">

                <input class="custom-ingredients-textarea" id="list-ingredient" placeholder="List down the ingredients" maxlength="100" name="list-ingredient" required>

                <!-- Dropdown for Menu Category -->
                <select class="custom-ingredients-dropdown" id="category-ingredient" name="category-ingredient" required>
                    <option value="" selected disabled>Select a Category</option>
                    <option value="Meat">Meat</option>
                    <option value="Drinks">Drinks</option>
                    <option value="Mushrooms">Mushrooms</option>
                    <option value="Chicken">Chicken</option>
                    <option value="Beef">Beef</option>
                </select>

                <!-- Dropdown for Menu Unit -->
                <select class="custom-ingredients-dropdown" id="unit-ingredient" name="unit-ingredient" required>
                    <option value="" selected disabled>Select a Unit</option>
                    <option value="grams">Grams</option>
                    <option value="liters">Liters</option>
                    <option value="pieces">Pieces</option>
                </select>

                <div class="custom-ingredients-buttons">
                    <button class="custom-ingredients-add" id="submit-ingredient" type="submit">ADD</button>
                </div>
            </form>
        </div>

        <!-- Right: Total Ingredients Count -->
        <div class="custom-ingredients-right">
            <p>Total Ingredients List:</p>
            <span class="custom-ingredients-count">0</span>
        </div>
    </div>

    <!-- Ingredients Table -->
    <table class="custom-ingredients-table">
        <thead>
            <tr>
                <th>Ingredient ID</th>
                <th>Ingredients</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="ingredients-list-body"></tbody>
    </table>
</div>



<div class="inventory-container">
    <div class="form-container">
        <form id="inventory-form" method="POST">
            <input type="hidden" name="id_ingredient" id="id_ingredient">
            <div class="form-group">
                <div class="ingredient-list">
                    <select id="ingredient-select" name="ingredient_id" required>
                    </select>
                    <button class="add-list add-ingredients-btn" id="add-ingredients">+</button>
                </div>
                <input type="text" placeholder="Supplier Name" maxlength="100" name="supplier_name" required>
                <input type="text" id="number-only" placeholder="Stock Quantity" maxlength="6" name="stock_quantity" required>
                <input type="text" id="number-only" placeholder="Item Cost" maxlength="6" name="item_cost" required>
                <input type="date" id="date-input" min="2021-01-01" placeholder="Expiration Date" name="expiration_date" required>
                <!-- <select>
                        <option>Category</option>
                    </select> -->
                <button class="add-btn" type="submit">Add to Inventory</button>
            </div>
        </form>
        <div class="total-items">
            <h2>TOTAL ITEMS</h2>
            <span></span>
        </div>
    </div>

    <div class="search-container">
        <button class="waster" id="inventory">Inventory</button>
        <button class="waster" id="invTransaction">Transactions</button>
        <button class="waster" id="wastage">Wastage</button>
        <button class="waster" id="payments">Payments</button>
        <select id="sortOptions">
            <option disabled>Sort of...</option>
            <option value="name_asc">Ingredient Name (A-Z)</option>
            <option value="name_desc">Ingredient Name (Z-A)</option>
            <option value="quantity_asc">Stock Quantity (Low to High)</option>
            <option value="quantity_desc">Stock Quantity (High to Low)</option>
            <option value="expiry_asc">Expiration Date (Soonest to Latest)</option>
            <option value="expiry_desc">Expiration Date (Latest to Soonest)</option>
            <option value="transaction_asc">Transaction Type (A-Z)</option>
            <option value="transaction_desc">Transaction Type (Z-A)</option>
            <option value="date_added_desc">Date Added (Newest First)</option>
            <option value="date_added_asc">Date Added (Oldest First)</option>
        </select>
        <input type="text" placeholder="Search..." id="searchInput">
    </div>

    <table class="inventory-table">
        <thead id="tableHead">
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Stock Quantity</th>
                <th>Expiration Date</th>
                <th>Supplier Name</th>
                <th>Cost</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="inventoryBody">

        </tbody>
    </table>
</div>



<div class="update-inventory-overlay"></div>

<div class="update-inventory-modal">
    <button class="update-inventory-back">Back</button>

    <div class="update-inventory-content">
        <!-- Left: Ingredients List -->
        <div class="update-inventory-left">
            <form id="updateBatchForm" method="POST">
                <input type="hidden" name="batch_id" id="batch_id">
                <input type="hidden" name="actions" id="action">

                <div class="form-group">
                    <div class="ingredient-list">
                        <select id="ingredient-select2" name="item_id" required>
                        </select>
                        <!-- <button class="add-list add-ingredients-btn" id="add-ingredients">+</button> -->
                    </div>
                    <input type="text" placeholder="Supplier Name" maxlength="100" name="suppliers_name" required>
                    <input type="text" id="number-only" placeholder="Stock Quantity" maxlength="6" name="stocks_quantity" required>
                    <input type="text" id="number-only" placeholder="Item Cost" maxlength="6" name="items_cost" required>
                    <input type="date" id="date-input" min="2021-01-01" placeholder="Expiration Date" name="expiration_dates" required>
                    <!-- <select>
                        <option>Category</option>
                    </select> -->
                    <button class="add-btn" type="submit" id="update-batch">Update Inventory</button>
                </div>

                <!-- <div class="update-inventory-buttons">
                    <button class="update-inventory-action" id="submit-ingredient" type="submit">ADD</button>
                </div> -->
            </form>
        </div>

        <!-- Right: Total Ingredients Count -->
        <div class="update-inventory-right">
            <p>Total Ingredients List:</p>
            <span class="update-inventory-count">0</span>
        </div>
    </div>

    <!-- Ingredients Table -->
    <table class="update-inventory-table">
        <thead>
            <tr>
                <th>Batch ID</th>
                <th>Ingredient</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Cost</th>
                <th>Expiration Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="update-inventory-body"></tbody>
    </table>
</div>

<!-- Chian's Footer Section -->
<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        const sortOptions = document.getElementById("sortOptions");
        const table = document.querySelector("#inventoryBody"); // Target the tbody

        // Function to filter table rows based on search
        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll("tr");

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchText) ? "" : "none";
            });
        }

        // Function to sort table rows
        function sortTable() {
            const sortValue = sortOptions.value;
            const rows = Array.from(table.querySelectorAll("tr")); // Get all table rows

            rows.sort((rowA, rowB) => {
                let valueA, valueB;

                switch (sortValue) {
                    case "name_asc":
                    case "name_desc":
                        valueA = rowA.cells[0].textContent.trim().toLowerCase();
                        valueB = rowB.cells[0].textContent.trim().toLowerCase();
                        break;
                    case "quantity_asc":
                    case "quantity_desc":
                        valueA = parseInt(rowA.cells[2].textContent) || 0;
                        valueB = parseInt(rowB.cells[2].textContent) || 0;
                        break;
                    case "expiry_asc":
                    case "expiry_desc":
                        valueA = new Date(rowA.cells[3].textContent);
                        valueB = new Date(rowB.cells[3].textContent);
                        break;
                    case "transaction_asc":
                    case "transaction_desc":
                        valueA = rowA.cells[4].textContent.trim().toLowerCase();
                        valueB = rowB.cells[4].textContent.trim().toLowerCase();
                        break;
                    default:
                        return 0; // No sorting applied
                }

                // Sorting direction
                return sortValue.endsWith("_asc") ? valueA > valueB ? 1 : -1 : valueA < valueB ? 1 : -1;
            });

            // Reorder table rows
            table.innerHTML = "";
            rows.forEach(row => table.appendChild(row));
        }

        // Event Listeners
        searchInput.addEventListener("input", filterTable);
        sortOptions.addEventListener("change", sortTable);
    });


    const numberOnly = document.querySelectorAll('#number-only');

    numberOnly.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters using regex
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    const dateInput = document.getElementById('date-input');
    const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
    dateInput.setAttribute('min', today); // Set today's date as the minimum

    // Edit Button Toggle (Edit -> Save -> Edit)

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('tr').remove();
        });
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            let row = this.closest('tr');
            let cells = row.querySelectorAll('td:not(:last-child)');

            if (this.textContent === "Edit") {
                cells.forEach(cell => {
                    cell.contentEditable = true;
                    cell.style.backgroundColor = "#FFF3CD";
                });
                this.textContent = "Save";
                this.style.background = "#28A745";
            } else {
                cells.forEach(cell => {
                    cell.contentEditable = false;
                    cell.style.backgroundColor = "";
                });
                this.textContent = "Edit";
                this.style.background = "black";
            }
        });
    });

    const inventoryForm = document.getElementById('inventory-form');
    const submitButton = document.querySelector('.add-btn'); // Get the submit button
    const ingredientIdInput = document.getElementById('id_ingredient');

    if (inventoryForm) {
        // Submit event listener
        inventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Check if there's an ingredient ID to determine whether it's update or add
            const ingredientId = ingredientIdInput.value;

            // Update the action, button text, and alert based on the presence of ingredientId
            if (ingredientId) {
                // If ingredient ID is present, this is an update operation
                submitButton.textContent = "Update Inventory";
                inventoryForm.setAttribute('action', 'db_queries/update_queries/update_inventory.php');
                alert("Updating inventory...");
            } else {
                // If no ingredient ID, default action is to add new inventory
                submitButton.textContent = "Add to Inventory";
                inventoryForm.setAttribute('action', 'db_queries/insert_queries/insert_inventory.php');
                alert('Adding new inventory...');
            }

            // Create FormData object for the form submission
            const formData = new FormData(inventoryForm);

            // Use fetch to submit the form data to the correct PHP script
            fetch(inventoryForm.action, { // Dynamically use the form's action attribute
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message); // Display success message
                        fetchAndDisplay('inventory'); // Refresh inventory data (assuming this is a function you've defined)
                        resetInventoryForm(); // Reset form after successful submission
                    } else {
                        alert(data.message); // Display error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred. Please try again.");
                });
        });

        // Function to reset the form after submission
        function resetInventoryForm() {
            // Reset form and button text
            ingredientIdInput.value = "";
            inventoryForm.reset();
            submitButton.textContent = "Add to Inventory"; // Reset button text to default
        }
    }


    // function fetchInventory(ingredient_id = null) {
    //     let url = 'db_queries/select_queries/fetch_inventory.php';
    //     if (ingredient_id) {
    //         url += `?ingredient_id=${ingredient_id}`; // Append ID for single-item fetch
    //     }

    //     fetch(url)
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.success) {
    //                 if (ingredient_id) {
    //                     // Populate the form if an ID was provided
    //                     populateForm(data.data);
    //                 } else {
    //                     // Otherwise, populate the table
    //                     populateTable(data.data);
    //                 }
    //             } else {
    //                 alert("Failed to load inventory: " + data.message);
    //             }
    //         })
    //         .catch(error => console.error("Error:", error));
    // }

    // Function to populate the form when editing
    function populateForm(item) {
        document.querySelector("input[name='id_ingredient']").value = item.ingredient_id;
        document.querySelector("select[name='ingredient_id']").value = item.ingredient_id;
        document.querySelector("input[name='supplier_name']").value = item.suppliers;
        document.querySelector("input[name='stock_quantity']").value = item.total_stock;
        document.querySelector("input[name='item_cost']").value = parseFloat(item.total_cost).toFixed(2);
        document.querySelector("input[name='expiration_date']").value = item.expiration_dates.split(" | ")[0]; // Set the earliest date
    }

    // Function to populate the table
    // function populateTable(inventoryData) {
    //     const inventoryBody = document.getElementById("inventoryBody");
    //     const totalItemSpan = document.querySelector(".total-items span");
    //     inventoryBody.innerHTML = "";

    //     let itemCount = 0;

    //     inventoryData.forEach(item => {
    //         let totalCost = parseFloat(item.total_cost) || 0;
    //         const row = document.createElement("tr");

    //         row.innerHTML = `
    //         <td>${item.ingredient_name}</td>
    //         <td>${item.category || "N/A"}</td>
    //         <td>${item.total_stock}</td>
    //         <td>${item.nearest_expiration_date}</td>
    //         <td>${item.top_supplier}</td>
    //         <td>$${totalCost.toFixed(2)}</td>
    //         <td>
    //             <div class="inv-action">
    //                 <button class="edit-btn" data-id="${item.ingredient_id}" >Update</button> 
    //                 <button class="remove-btn" data-id="${item.ingredient_id}" >Remove</button> 
    //             </div>
    //             </td>
    //             `;
    //         // <button class="remove-btn" onclick="removeItem(${item.ingredient_id})">Remove</button>

    //         inventoryBody.appendChild(row);
    //         itemCount++;
    //     });

    //     totalItemSpan.textContent = itemCount;
    // }



    fetchAndDisplay('inventory');




    function removeFromInventory(id) {
        if (confirm('Are you sure you want to remove this item from inventory?')) {
            fetch(`db_queries/delete_queries/remove_item.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ingredient_id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        fetchAndDisplay('inventory');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const modalInventory = document.querySelector(".update-inventory-modal");
        const closeBtnInventory = document.querySelector(".update-inventory-back");
        const overlayInventory = document.querySelector(".update-inventory-overlay");

        const totalCountInventory = document.querySelector(".update-inventory-count");
        // const textareaInventory = document.querySelector(".update-inventory-textarea");
        const addBtnInventory = document.querySelector(".update-inventory-action");


        // Event listener for dynamically created edit buttons
        document.getElementById("inventoryBody").addEventListener("click", function(event) {
            event.preventDefault();
            if (event.target.classList.contains("edit-btn")) {
                const ingredient_id = event.target.dataset.id;
                // fetchInventory(ingredient_id); 
                openInventoryModal(ingredient_id);
            }

            if (event.target.classList.contains('remove-btn')) {
                const ingredient_id = event.target.dataset.id;

                removeFromInventory(ingredient_id);
            }
        });

        document.getElementById("update-inventory-body").addEventListener('click', function(e) {
            if (e.target.classList.contains('add') || e.target.classList.contains('subtract')) {
                // Get the batch_id and action (add or subtract)
                const batch_id = e.target.dataset.id;
                const action = e.target.dataset.action;

                // Call the function to populate the batch form with the respective action
                populateBatchForm(batch_id, action);
            }
            if (e.target.classList.contains('remove')) {
                const batch_id = e.target.dataset.id;
                const item_id = e.target.dataset.item;
                removeBatch(batch_id, item_id);
            }

        })


        function openInventoryModal(id) {
            modalInventory.classList.add("show");
            overlayInventory.classList.add("show");
            stockBatches(id);
        }
        // Close Modal
        function closeInventoryModal() {
            modalInventory.classList.remove("show");
            overlayInventory.classList.remove("show");
        }

        closeBtnInventory.addEventListener('click', closeInventoryModal);
        overlayInventory.addEventListener('click', closeInventoryModal);

        function stockBatches(item_id) {
            fetch(`db_queries/select_queries/fetch_batches.php?item_id=${item_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const batchesListBody = document.getElementById('update-inventory-body');
                        batchesListBody.innerHTML = ""; // Clear the table before adding new data
                        let count = 0;
                        data.batches.forEach(batch => {
                            const row = document.createElement("tr");

                            row.innerHTML = `
                        <td>${batch.batch_id}</td>
                        <td>${batch.ingredient_name}</td>
                        <td>${batch.supplier_name}</td>
                        <td>${batch.quantity}</td>
                        <td>${batch.cost}</td>
                        <td>${batch.expiration_date}</td>
                        <td>
                            <button class="edit-btn add" data-id="${batch.batch_id}" data-action="add">Add</button>
                            <button class="remove-btn subtract" data-id="${batch.batch_id}" data-action="subtract">Subtract</button>
                            <button class="remove-btn remove" data-id="${batch.batch_id}" data-item=${batch.ingredient_id}>Remove</button>
                        </td>
                    `;

                            batchesListBody.appendChild(row);
                            count++;
                        });
                        if (totalCountInventory) {
                            totalCountInventory.textContent = count;
                        }
                    } else {
                        alert("Failed to load stock batches: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        }

        // Step 1: Populate the form when "Add" or "Subtract" button is clicked
        // Step 1: Populate the form when "Add" or "Subtract" button is clicked
        function populateBatchForm(batch_id, action) {
            fetch(`db_queries/select_queries/fetch_batches.php?batch_id=${batch_id}`)
                .then(response => response.json())
                .then(data => {
                    // Check if batches exist and if data.batch exists
                    if (data.success && data.batch) {
                        // Fill the form fields with batch data
                        document.getElementById('batch_id').value = data.batch.batch_id;
                        document.getElementById('action').value = action;
                        document.getElementById('ingredient-select2').value = data.batch.ingredient_id; // Assuming ingredient_id is selected from a dropdown
                        document.querySelector('input[name="suppliers_name"]').value = data.batch.supplier_name;
                        document.querySelector('input[name="stocks_quantity"]').value = data.batch.quantity;
                        document.querySelector('input[name="items_cost"]').value = data.batch.cost;
                        document.querySelector('input[name="expiration_dates"]').value = data.batch.expiration_date;

                    } else {
                        alert("Failed to load batch details or batch not found.");
                    }
                })
                .catch(error => console.error("Error:", error));
        }

        const batchForm = document.getElementById('updateBatchForm');
        if (batchForm) {
            batchForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent the form from submitting directly, handled manually

                // Create FormData from the form
                const formData = new FormData(this);

                // Add any additional fields or logic (like the action) to the FormData if needed
                formData.append('action', action); // Add action if necessary, otherwise you may want to handle this on the front end too

                fetch('db_queries/update_queries/update_batches.php', {
                        method: 'POST',
                        body: formData // Send the FormData to the server
                    })
                    .then(response => response.json()) // Parse JSON response from the server
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            stockBatches(formData.get('item_id')); // Refresh the batch list
                            batchForm.reset();
                        }
                    })
                    .catch(error => console.error("Error:", error)); // Handle errors
            });
        }

        function removeBatch(batch_id, item_id) {
            if (confirm('Are you sure you want to remove this batch?')) {
                fetch(`db_queries/delete_queries/remove_batch.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            batch_id: batch_id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            stockBatches(item_id);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

    })

    document.addEventListener("DOMContentLoaded", function() {

        const modal = document.querySelector(".custom-ingredients-modal");
        const openBtn = document.querySelector(".add-ingredients-btn");
        const closeBtn = document.querySelector(".custom-ingredients-back");
        const overlay = document.querySelector(".custom-modal-overlay");

        const totalCount = document.querySelector(".custom-ingredients-count");
        const ingredientTextarea = document.querySelector(".custom-ingredients-textarea");
        const addBtn = document.querySelector(".custom-ingredients-add");
        const tableBody = document.querySelector(".custom-ingredients-table tbody");

        openBtn.addEventListener("click", function(e) {
            e.preventDefault();
            modal.classList.add("show");
            overlay.classList.add("show");

            fetchIngredientList(); // Refresh ingredient list

            // Reset form fields
            document.getElementById('ingredient_id').value = "";
            document.getElementById('list-ingredient').value = "";
            document.getElementById('category-ingredient').value = "";
            document.getElementById('unit-ingredient').value = "";

            document.getElementById('submit-ingredient').textContent = "ADD"; // Reset button text
        });


        // Close Modal
        function closeModal() {
            modal.classList.remove("show");
            overlay.classList.remove("show");
        }

        closeBtn.addEventListener("click", closeModal);
        overlay.addEventListener("click", closeModal);

        const addIngredientForm = document.getElementById('addIngredientForm');

        if (addIngredientForm) {
            addIngredientForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const ingredientId = document.getElementById('id_ingredient').value.trim();
                const listIngredient = document.getElementById('list-ingredient').value.trim();
                const unitIngredient = document.getElementById('unit-ingredient').value.trim();
                const categoryIngredient = document.getElementById('category-ingredient').value.trim();

                if (listIngredient === "" || unitIngredient === "" || categoryIngredient === "") {
                    alert("Please fill out all fields");
                    return;
                }
                console.log(ingredientId)
                const formData = new FormData(addIngredientForm);
                let url = ingredientId ? 'db_queries/update_queries/update_ingredient.php' : 'db_queries/insert_queries/insert_ingredient.php';

                fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);

                        if (data.success) {
                            addIngredientForm.reset();
                            document.getElementById('id_ingredient').value = ""; // Clear ingredient ID after update
                            document.getElementById('submit-ingredient').textContent = "ADD"; // Reset button text
                            fetchIngredientList();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert("An error occurred.");
                    });
            });
        }





        function fetchIngredientList() {
            fetch('db_queries/select_queries/fetch_ingredient.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ingredientsListBody = document.getElementById('ingredients-list-body');
                        ingredientsListBody.innerHTML = ""; // Clear table before adding new data
                        let count = 0;
                        data.ingredients.forEach(item => {
                            const row = document.createElement("tr");

                            row.innerHTML = `
                            <td>${item.ingredient_id}</td>
                            <td>${item.ingredient_name}</td>
                            <td>${item.category}</td>
                            <td>${item.unit}</td>
                            <td>${item.date_added}</td>
                            <td>
                                <button class="edit-btn" onclick="editItem(${item.ingredient_id})">Update</button>
                                <button class="remove-btn" onclick="removeItem(${item.ingredient_id})">Remove</button>
                            </td>
                        `;

                            ingredientsListBody.appendChild(row);
                            count++;
                        });
                        totalCount.textContent = count;
                    } else {
                        alert("Failed to load inventory: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        }


        function fetchIngredient() {
            fetch('db_queries/select_queries/fetch_ingredient.php') // Fetch updated ingredients
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ingredientSelect = document.getElementById('ingredient-select');
                        const ingredientSelect2 = document.getElementById('ingredient-select2');
                        ingredientSelect.innerHTML = '<option value="" selected disabled>Select Ingredient</option>'; // Reset dropdown
                        ingredientSelect2.innerHTML = '<option value="" selected disabled>Select Ingredient</option>';

                        data.ingredients.forEach(ingredient => {
                            const option = document.createElement('option');
                            option.value = ingredient.ingredient_id;
                            option.textContent = ingredient.ingredient_name;
                            ingredientSelect.appendChild(option);

                            const option2 = option.cloneNode(true);
                            ingredientSelect2.appendChild(option2);
                        });


                    }
                })
                .catch(error => console.error('Error fetching ingredients:', error));
        }
        fetchIngredient();
    });

    // Function to populate form when editing an ingredient
    function editItem(ingredientId) {
        fetch(`db_queries/select_queries/get_ingredient.php?id=${ingredientId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('ingredient_id').value = data.ingredient.ingredient_id;
                    document.getElementById('list-ingredient').value = data.ingredient.ingredient_name;
                    document.getElementById('category-ingredient').value = data.ingredient.category;
                    document.getElementById('unit-ingredient').value = data.ingredient.unit;
                    document.getElementById('submit-ingredient').textContent = "UPDATE"; // Change button text to update
                }
            })
            .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inventoryBtn = document.getElementById('inventory');
        const invTransactionBtn = document.getElementById('invTransaction');
        const wastageBtn = document.getElementById('wastage');
        const paymentsBtn = document.getElementById('payments');

        inventoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchAndDisplay('inventory');
        })
        invTransactionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchAndDisplay('transactions');
        })
        wastageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchAndDisplay('wastage');
        })
        paymentsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchAndDisplay('payments');
        })


    })



    let lastFetchedData = ''; // For change detection (optional)

    async function fetchAndDisplay(type) {
        const thead = document.getElementById("tableHead");
        const tbody = document.getElementById("inventoryBody");

        let url = '';
        let theadHTML = '';
        let tableProcessor = null;

        switch (type) {
            case 'inventory':
                url = 'db_queries/select_queries/fetch_inventory.php';
                theadHTML = `
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Stock Quantity</th>
                            <th>Expiration Date</th>
                            <th>Supplier Name</th>
                            <th>Cost</th>
                            <th>Action</th>
                        </tr>`;
                tableProcessor = populateInventory;
                break;

            case 'transactions':
                url = `db_queries/select_queries/fetch_transactions.php?type=transactions`;
                theadHTML = `
                        <tr>
                            <th>Ingredient</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Date</th>
                        </tr>`;
                tableProcessor = populateTransactions;
                break;

            case 'wastage':
                url = `db_queries/select_queries/fetch_transactions.php?type=wastage`;
                theadHTML = `
                        <tr>
                            <th>Ingredient</th>
                            <th>Quantity Wasted</th>
                            <th>Unit</th>
                            <th>Wastage Date</th>
                        </tr>`;
                tableProcessor = populateWastage;
                break;

            case 'payments':
                url = `db_queries/select_queries/fetch_transactions.php?type=payments`;
                theadHTML = `
                        <tr>
                            <th>Order ID</th>
                            <th>Amount Paid</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>`;
                tableProcessor = populatePayments;
                break;

            default:
                alert("Invalid data type selected.");
                return;
        }

        thead.innerHTML = theadHTML;

        tbody.innerHTML = `
                    <tr>
                        <td colspan="100%" style="text-align:center; padding: 1em;">Loading...</td>
                    </tr>`;


        try {
            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
                tableProcessor(data.data);
            }  else {
                tbody.innerHTML = `
                                <tr>
                                    <td colspan="100%" style="text-align:center; color:red;">${data.message}</td>
                                </tr>`;
            }
        } catch (err) {
            console.error(err);
            tbody.innerHTML = `
                            <tr>
                                <td colspan="100%" style="text-align:center; color:red;">An error occurred while fetching data.</td>
                            </tr>`;
        }
    }




    function populateInventory(data) {
        const tbody = document.getElementById("inventoryBody");
        tbody.innerHTML = data.length ? "" : `<tr>
                                                    <td colspan="7" style="text-align:center; color:grey;">No inventory data available.</td>
                                                </tr>`;

        let totalItemSpan = document.querySelector('.total-items span');
        totalItemSpan.textContent = "";
        let itemCount = 0;
        data.forEach(item => {
            let totalCost = parseFloat(item.total_cost) || 0;
            const row = document.createElement("tr");
            row.innerHTML = `
            <td>${item.ingredient_name}</td>
            <td>${item.category || "N/A"}</td>
            <td>${item.total_stock}</td>
            <td>${item.nearest_expiration_date}</td>
            <td>${item.top_supplier}</td>
            <td>$${totalCost.toFixed(2)}</td>
            <td>
                <div class="inv-action">
                    <button class="edit-btn" data-id="${item.ingredient_id}">Update</button>
                    <button class="remove-btn" data-id="${item.ingredient_id}">Remove</button>
                </div>
            </td>`;
            tbody.appendChild(row);
            itemCount++;
        });

        totalItemSpan.textContent = itemCount;
    }

    function populateTransactions(data) {
        const tbody = document.getElementById("inventoryBody");
        tbody.innerHTML = data.length ? "" : `<tr>
                                                    <td colspan="7" style="text-align:center; color:grey;">No transaction data available.</td>
                                                </tr>`;

        let totalItemSpan = document.querySelector('.total-items span');
        totalItemSpan.textContent = "";
        let itemCount = 0;
        data.forEach(trx => {
            const row = document.createElement("tr");
            row.innerHTML = `
            <td>${trx.ingredient_name}</td>
            <td>${trx.transaction_type}</td>
            <td>${trx.quantity}</td>
            <td>${trx.unit}</td>
            <td>${trx.transaction_date}</td>`;
            tbody.appendChild(row);
            itemCount++;
        });

        totalItemSpan.textContent = itemCount;
    }

    function populateWastage(data) {
        const tbody = document.getElementById("inventoryBody");
        tbody.innerHTML = data.length ? "" : `<tr>
                                                    <td colspan="7" style="text-align:center; color:grey;">No wastage data available.</td>
                                                </tr>`;

        let totalItemSpan = document.querySelector('.total-items span');
        totalItemSpan.textContent = "";
        let itemCount = 0;
        data.forEach(w => {
            const row = document.createElement("tr");
            row.innerHTML = `
            <td>${w.ingredient_name}</td>
            <td>${w.quantity}</td>
            <td>${w.unit}</td>
            <td>${w.transaction_date}</td>`;
            tbody.appendChild(row);
            itemCount++;
        });
        totalItemSpan.textContent = itemCount;
    }

    function populatePayments(data) {
        const tbody = document.getElementById("inventoryBody");
        tbody.innerHTML = data.length ? "" : `<tr>
                                                    <td colspan="7" style="text-align:center; color:grey;">No payments data available.</td>
                                                </tr>`;

        let totalItemSpan = document.querySelector('.total-items span');
        totalItemSpan.textContent = "";
        let itemCount = 0;
        data.forEach(p => {
            const row = document.createElement("tr");
            row.innerHTML = `
            <td>${p.order_id}</td>
            <td>$${parseFloat(p.amount_paid).toFixed(2)}</td>
            <td>${p.payment_method}</td>
            <td>${p.payment_status}</td>
            <td>${p.payment_date}</td>`;
            tbody.appendChild(row);
            itemCount++;
        });
        totalItemSpan.textContent = itemCount;
    }
</script>


</body>

</html>
