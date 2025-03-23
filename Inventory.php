<?php

include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

$sql = $connect->prepare('SELECT ingredient_id, ingredient_name FROM ingredients');
$sql->execute();
$list = $sql->fetchAll(PDO::FETCH_ASSOC);

$ingredientList = [];
if ($list) {
    $ingredientList = $list;
}
?>

<link rel="stylesheet" href="css/inventory.css">

<div class="inventory-container">
    <div class="form-container">
        <form id="inventory-form" method="POST">
            <div class="form-group">
                <div class="ingredient-list">
                    <select id="ingredient-select" name="ingredient_id" required>
                        <option value="" disabled>Select Ingredient</option>
                        <?php
                        foreach ($ingredientList as $ingredient) {
                            echo "<option value='" . $ingredient['ingredient_id'] . "'>" . $ingredient['ingredient_name'] . "</option>";
                        }
                        ?>
                    </select>
                    <button class="add-list" id="add-ingredients">+</button>
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
        <button class="waster">Wastage</button>
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
        <thead>
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
<!-- Chian's Footer Section -->
<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
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

    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(inventoryForm);

            fetch('db_queries/insert_queries/insert_inventory.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        fetchInventory();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        })
    }

    function fetchInventory() {
        fetch('db_queries/select_queries/fetch_inventory.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const inventoryBody = document.getElementById("inventoryBody");
                    const totalItemSpan = document.querySelector(".total-items span");
                    inventoryBody.innerHTML = ""; // Clear table before adding new data

                    let itemCount = 0;

                    data.data.forEach(item => {
                        // Ensure total_cost is a number
                        let totalCost = parseFloat(item.total_cost) || 0;

                        const row = document.createElement("tr");

                        row.innerHTML = `
                        <td>${item.ingredient_name}</td>
                        <td>${item.category || "N/A"}</td>
                        <td>${item.total_stock}</td>
                        <td>${item.expiration_dates}</td>
                        <td>${item.suppliers}</td>
                        <td>$${totalCost.toFixed(2)}</td>
                        <td>
                            <button class="edit-btn" onclick="editItem(${item.ingredient_id})">Edit</button>
                            <button class="remove-btn" onclick="removeItem(${item.ingredient_id})">Remove</button>
                        </td>
                    `;

                        inventoryBody.appendChild(row);
                        itemCount++;
                    });

                    totalItemSpan.textContent = itemCount;
                } else {
                    alert("Failed to load inventory: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
    }
    fetchInventory();
</script>


</body>

</html>
