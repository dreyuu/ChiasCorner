<?php include 'connection.php';

$backgroundImage = 'Capstone Assets/Log-in Form BG.png';
include 'inc/navbar.php';

$order_id = '';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'] ?? null;
} else {
    $order_id = '';
}

?>
<link rel="stylesheet" href="css/menu.css">
<!-- Font Awesome Free (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="custom-menus-modal-overlay"></div>

<div class="main-container">
    <!-- Sidebar -->
    <div class="custom-sidebar">
        <div class="custom-sidebar-logo">
            <img src="Capstone Assets/LogoMain.png" alt="Logo">
        </div>
        <div class="custom-sidebar-menu">
            <button class="custom-menu-item" onclick="fetchMenus('Samgyupsal', '.menu-items')">Samgyupsal</button>
            <button class="custom-menu-item active" onclick="fetchMenus('Chicken Wings', '.menu-items')">Chicken Wings</button>
            <button class="custom-menu-item" onclick="fetchMenus('Sizzling', '.menu-items')">Sizzlings</button>
            <button class="custom-menu-item" onclick="fetchMenus('Drinks', '.menu-items')">Drinks</button>
            <button class="custom-menu-item" onclick="fetchMenus('Others', '.menu-items')">Others</button>

            <div class="admins show-menu-nav">
                <button class="custom-menu-item custom-promo-btn">
                    Promos <span class="custom-promo-icon">+</span>
                </button>

                <!-- Menu Button -->
                <button class="custom-add-menu">Add New Menu</button>
            </div>
        </div>
    </div>


    <!-- Menus Modal Overlay -->


    <div class="menu-container">
        <div class="menu-items-list">
            <input type="hidden" name="order_id" id="order_id" value="<?php echo $order_id; ?>">
            <div class="menu-items">

            </div>
        </div>

        <div class="options-container">
            <div class="sauces-container">
                <h3>Add - Ons</h3>
                <div class="sauce-container">
                    <div class="sauces">
                    </div>
                </div>
            </div>

            <div class="option-controls">
                <div class="order-options">
                    <button class="order-btn" id="dine-in">Dine-In</button>
                    <button class="order-btn" id="take-out">Take-Out</button>
                    <button class="order-btn" id="clear-order">Clear Order</button>
                </div>
                <div class="order-controls">
                    <div class="order-total">ORDER TOTAL: ₱ <span id="total-amount">0.00</span></div>
                    <button class="review-btn" id="placeOrder">Place Order</button>
                    <button class="review-btn" id="updateOrder">Update Order</button>
                    <button class="order-btn" id="cancel">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="list_orders">
    </div>
</div>


<!-- Menus Ingredients Modal -->
<div class="custom-menus-modal">
    <button class="custom-menus-back">Back</button>
    <h2 class="custom-menus-title">Menu Ingredients</h2>

    <div class="custom-menus-content">
        <!-- Left Side: Form Inputs -->
        <div class="custom-menus-left">
            <form id="createMenuForm" method="POST">
                <input type="hidden" name="menu_id" id="menu_id">
                <div class="menu-list">
                    <select class="custom-menus-dropdown" id="menu" name="menu" required>
                    </select>
                    <button class="add-menu add-menu-btn" id="add-ingredients" type="button">+</button>
                </div>
                <select class="custom-menus-dropdown" id="ingredients" name="ingredients" required>
                </select>
                <input type="text" class="custom-menus-input" placeholder="Quantity" name="quantity" id="quantity" required>
                <input type="text" class="custom-menus-input" placeholder="Unit" name="unit" id="unit" required>
                <select class="menus-dropdown" id="ingredient_type" name="ingredient_type" required>
                    <option value="fixed" selected>Fixed</option>
                    <option value="unli">Unli</option>
                </select>

                <!-- <input type="text" class="custom-menus-input" placeholder="Price: ₱ 00000"> -->
                <!-- <input type="file" class="custom-menus-upload"> -->

                <div class="custom-menus-buttons">
                    <button class="custom-menus-add" id="submit-ingredient">ADD</button>
                </div>
            </form>
        </div>

        <!-- Right Side: Total Menus -->
        <div class="custom-menus-right">
            <p>Total Ingredients of menu:</p>
            <span class="custom-menus-count">0</span>
        </div>
    </div>

    <!-- Search & Sort Bar -->
    <!-- <div class="custom-menus-table-header">
        <select class="custom-menus-sort">
            <option value="default">Sort by</option>
            <option value="name">Name</option>
            <option value="price">Price</option>
            <option value="date">Date Added</option>
        </select>
        <input type="text" class="custom-menus-search" placeholder="Search menu...">
    </div> -->


    <!-- Table -->
    <div class="custom-menus-table-container">
        <table class="custom-menus-table">
            <thead>
                <tr>
                    <th>Ingredients ID</th>
                    <th>Category</th>
                    <th>Ingredients</th>
                    <th>Unit</th>
                    <th>Ingredient Type</th>
                    <th>Date Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Table rows will be dynamically added here -->

            </tbody>
        </table>
    </div>
</div>

<!-- Menus Modal -->
<div class="menus-modal" id="menus-modal">
    <button class="menus-back">Back</button>
    <h2 class="menus-title">Menus</h2>

    <div class="menus-content">
        <!-- Left Side: Form Inputs -->
        <div class="menus-left">
            <form id="createMenuListForm" method="POST" enctype="multipart/form-data">
                <!-- <select class="menus-dropdown" id="ingredients" name="ingredients">
                </select> -->
                <input type="hidden" id="edit_menu_id" name="menu_id">
                <input type="text" class="custom-menus-input" id="name" placeholder="Name" name="name" required>
                <select class="menus-dropdown" id="category" name="category" required>
                    <option value="" selected disabled>Select a Category</option>
                    <option value="Samgyupsal">Samgyupsal</option>
                    <option value="Chicken Wings">Chicken Wings</option>
                    <option value="Sizzling">Sizzling</option>
                    <!-- <option value="Condiments & Sauces">Condiments & Sauces</option> -->
                    <option value="Drinks">Drinks</option>
                    <option value="Add-Ons">Add-Ons</option>
                    <option value="Others">Others</option>
                </select>
                <select class="menus-dropdown" id="menu-type" name="menu_type" required>
                    <option value="" selected disabled>Select a Menu Type</option>
                    <option value="Unli">Unli</option>
                    <option value="Short Order">Short Order</option>
                    <option value="Bilao">Bilao</option>
                    <option value="Sizzling">Sizzling</option>
                    <option value="Sauces">Sauces</option>
                    <option value="Others">Others</option>
                </select>
                <input type="text" class="custom-menus-input" placeholder="Price: ₱ 00000" id="price" name="price" required>
                <select class="menus-dropdown" id="availability" name="availability" required>
                    <option value="" disabled>Availability</option>
                    <option value="Available" selected>Available</option>
                    <option value="Not Available">Not Available</option>
                </select>
                <input type="file" class="custom-menus-upload" id="menu_image" name="menu_image" accept="image/" required>

                <div class="custom-menus-buttons">
                    <button class="custom-menus-add" id="submitButton" type="submit">ADD</button>
                </div>
            </form>
        </div>

        <div class="menu-right">
            <p>Total Menu:</p>
            <span class="custom-menus-count">0</span>
        </div>
    </div>

    <div class="menus-table-container">
        <div class="paginated-table">
            <table class="menus-table">
                <thead>
                    <tr>
                        <th>Menu ID</th>
                        <th>Menu</th>
                        <th>Category</th>
                        <th>Menu Type</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Table rows will be dynamically added here -->

                </tbody>
            </table>
            <div class="menu-pagination-container">
                <button id="menu-prevPage">Previous</button>
                <span id="menu-pageInfo"></span>
                <button id="menu-nextPage">Next</button>
            </div>
        </div>
    </div>
</div>


<!-- Promo Modal -->

<div class="custom-promo-modal">
    <div class="custom-promo-modal-content">
        <button class="custom-back-btn">Back</button>
        <h2 class="custom-promo-header">PROMOS</h2><br><br><br>

        <div class="custom-promo-wrapper">
            <div class="custom-promo-box">
                <div class="custom-promo-form">
                    <form id="promoForm">

                        <input type="hidden" id="promoId" placeholder="Promo iD" name="promoId" required>
                        <input type="text" id="promoName" placeholder="Promo Name" name="promoName" required>
                        <select id="discount_type" name="discount_type" class="custom-description-dropdown" required>
                            <option value="" disabled selected>Select Discount Type</option>
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                        </select>
                        <input type="text" id="discount_value" placeholder="Discount Value" name="discount_value" required>
                        <label for="start_date">START DATE:</label>
                        <input type="date" id="start_date" name="start_date" required>
                        <label for="end_date">END DATE:</label>
                        <input type="date" id="end_date" name="end_date" required>
                        <select id="applicable_menu" name="applicable_menu" class="custom-description-dropdown" required>
                            <option value="" disabled selected>Select Applicable Menu</option>
                            <option value="">All</option>
                        </select>
                        <!-- <input type="number" id="promoPrice" placeholder="Price: ₱00000"> -->
                        <!-- <input type="file" id="promoImage" accept="image/*"> -->
                        <button class="custom-add-promo-btn" type="submit" id="promoSubmitBtn">ADD PROMO</button>
                    </form>
                </div>
                <div class="custom-promo-summary">
                    <span class="custom-total-label">Total Promo:</span>
                    <span class="custom-total-count">1</span>
                </div>
            </div>
        </div>


        <div class="custom-menus-table-header">
            <select class="custom-menus-sort" id="promoFilter">
                <option value="default" disabled>Sort by</option>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <table class="custom-promo-table">
            <div class="paginated-table">
                <thead>
                    <tr>
                        <th>Promo Name</th>
                        <th>Discount Type</th>
                        <th>Discount Value</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Applicable Menu</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="promoTableBody">

                </tbody>
        </table>
        <div class="promo-pagination-container">
            <button id="promo-prevPage">Previous</button>
            <span id="promo-pageInfo"></span>
            <button id="promo-nextPage">Next</button>
        </div>
    </div>
</div>
</div>
<div class="alerts">
    <div id="success-alert" class="alert alert-success"></div>
    <div id="error-alert" class="alert alert-danger"></div>
    <div id="warning-alert" class="alert alert-warning"></div>
</div>

<!-- Chian's Footer Section -->

<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>


<script>
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

    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem("jwt_token");
        // console.log("JWT Token: ", token);
        if (!token) {
            // Redirect to login page if token is not present
            // console.log("No token found. Redirecting to login page.");
            window.location.href = "index.php";
            return
        } else {
            const payloadBase64 = token.split('.')[1]; // get the payload part
            const payloadJson = atob(payloadBase64); // decode from base64
            const payload = JSON.parse(payloadJson); // convert to JS object

            const admin = document.querySelector('.admins');
            if (payload.user_type === 'admin') {
                admin.classList.add('show-nav')
            } else {
                admin.classList.remove('show-nav')
            }
        }
    })
    // Promo Modal

    document.addEventListener("DOMContentLoaded", function() {
        const promoBtn = document.querySelector(".custom-promo-btn");
        const promoModal = document.querySelector(".custom-promo-modal");
        const closeModal = document.querySelector(".custom-modal-close");
        const closeBtn = document.querySelector(".custom-close-modal-btn");
        const backBtn = document.querySelector(".custom-back-btn");
        const addPromoBtn = document.querySelector(".custom-add-promo-btn");
        const promoTableBody = document.querySelector(".custom-promo-table tbody");
        const promoCount = document.querySelector(".custom-total-count");
        const body = document.body;

        // document.getElementById("receiptModalBg").style.display = "none";

        let promoList = [];

        // Open Promo Modal

        promoBtn.addEventListener("click", function() {
            promoModal.style.display = "flex";
            fetchPromos("active"); // Load only active promos by default
            setTimeout(() => {
                promoModal.classList.add("show");
                body.classList.add("modal-open");
            }, 10);
        });

        // Close Promo Modal (Clicking "X" Button)

        if (closeModal) closeModal.addEventListener("click", closePromoModal);

        // Close Promo Modal (Clicking "Close" Button)

        if (closeBtn) closeBtn.addEventListener("click", closePromoModal);

        // Hide Promo Modal (Back Button)

        if (backBtn) {
            backBtn.addEventListener("click", function() {
                promoModal.classList.remove("show");
                setTimeout(() => {
                    promoModal.style.display = "none";
                    checkModals();
                }, 300);
            });
        }

        // Close Modal when clicking outside

        window.addEventListener("click", function(event) {
            if (event.target === promoModal) {
                closePromoModal();
            }
        });

        // Function to Close Promo Modal

        function closePromoModal() {
            promoModal.classList.remove("show");
            setTimeout(() => {
                promoModal.style.display = "none";
                checkModals();
            }, 300);
        }

        // Check if any modal is still open before removing blur effect

        function checkModals() {
            const anyModalOpen = document.querySelector(".custom-promo-modal.show");
            if (!anyModalOpen) {
                body.classList.remove("modal-open");
            }
        }



        // Function to clear promo form

        // function clearPromoForm() {
        //     document.getElementById("promoName").value = "";
        //     document.getElementById("promoDescription").value = "";
        //     document.getElementById("promoPrice").value = "";
        //     document.getElementById("promoImage").value = "";
        // }
    });

    // Add new Menu Button Script

    document.addEventListener("DOMContentLoaded", function() {
        const modalOverlay = document.querySelector(".custom-menus-modal-overlay");
        const modal = document.querySelector(".custom-menus-modal");
        const openModalBtn = document.querySelector(".custom-add-menu");
        const closeModalBtn = document.querySelector(".custom-menus-close");
        const backBtn = document.querySelector(".custom-menus-back");

        const addMenusBtn = document.getElementById("add-ingredients");
        // const addMenusBtn = document.getElementById("custom-add-menu");
        const menusBackBtn = document.querySelector(".menus-back");
        const menusModal = document.getElementById('menus-modal');
        const menusTable = document.querySelector('.menus-table')
        // Open Modal

        // if (openModalBtn) {
        //     openModalBtn.addEventListener("click", function() {
        //         modalOverlay.classList.add("show");
        //         modal.classList.add("show");
        //         document.body.classList.add("modal-open"); // Add blur effect
        //     });
        // }

        if (openModalBtn) {
            openModalBtn.addEventListener("click", function() {
                menusModal.classList.add("show");
                menusModal.classList.add("show");
                document.body.classList.add("modal-open");
                loadMenus();
                document.getElementById("submitButton").textContent = "ADD"; // Reset button text
                document.getElementById("edit_menu_id").value = ""; // Clear ID
                modalOverlay.classList.add("show");
                document.body.classList.add("modal-open"); // Add blur effect
            });
        }
        // Close Modal (X button)

        if (closeModalBtn) {
            closeModalBtn.addEventListener("click", closeMenusModal);
        }

        // Back Button (Hide Modal)

        if (backBtn) {
            backBtn.addEventListener("click", closeMenusModal);
        }

        if (menusBackBtn) {
            menusBackBtn.addEventListener("click", function() {
                closeMenusModal();
                menusModal.classList.remove("show");
            });
        }
        // Close Modal when clicking outside

        modalOverlay.addEventListener("click", function(e) {
            if (e.target === modalOverlay) {
                closeMenusModal();
                menusModal.classList.remove("show");
            }
        });

        // Function to Close Modal

        function closeMenusModal() {
            modal.classList.remove("show");
            setTimeout(() => {
                modalOverlay.classList.remove("show");
                checkModals();
            }, 300);
        }

        // Check if any modal is still open before removing blur effect

        function checkModals() {
            const anyModalOpen = document.querySelector(".custom-menus-modal.show");
            const menusModalOpen = document.querySelector(".menus-modal.show");
            if (!anyModalOpen || menusModalOpen) {
                document.body.classList.remove("modal-open");
            }
        }

    });

    function fetchMenuList() {
        fetch('db_queries/select_queries/fetch_menu_list.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const menuLists = document.getElementById('menu');
                    const ingredientList = document.getElementById('ingredients');
                    const applicableMenu = document.getElementById('applicable_menu');

                    // Reset dropdowns
                    menuLists.innerHTML = '<option value="" disabled selected>Select Menu</option>';
                    ingredientList.innerHTML = '<option value="" disabled selected>Select Ingredients</option>';
                    applicableMenu.innerHTML = `<option value="" disabled selected>Select Applicable Menu</option>
                                            <option value="">All</option>`;

                    // Append menu items
                    data.menuList.forEach(menu => {
                        const option1 = document.createElement('option');
                        option1.value = menu.menu_id;
                        option1.textContent = menu.name;
                        menuLists.appendChild(option1);

                        const option2 = option1.cloneNode(true); // Clone before appending
                        applicableMenu.appendChild(option2);
                    });

                    // Append ingredients
                    data.menuIngredientList.forEach(menuIngredient => {
                        const option = document.createElement('option');
                        option.value = menuIngredient.ingredient_id;
                        option.textContent = menuIngredient.ingredient_name;
                        ingredientList.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching ingredients:', error));
    }

    fetchMenuList();

    const createMenu = document.getElementById("createMenuForm");

    if (createMenu) {
        createMenu.addEventListener("submit", function(event) {
            event.preventDefault();

            const menu_id = document.getElementById("menu").value;
            const ingredient_id = document.getElementById("ingredients").value;
            const quantity = document.querySelector("input[name='quantity']").value;
            const unit = document.querySelector("input[name='unit']").value;
            const isUpdate = document.getElementById("submit-ingredient").dataset.update; // Check if update mode
            const ingredient_type = document.getElementById("ingredient_type").value;

            let url = isUpdate ? "db_queries/update_queries/update_ingredient.php" : "db_queries/insert_queries/insert_menu_ingredient.php";

            loader.show()
            fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `menu_id=${menu_id}&ingredient_id=${ingredient_id}&quantity=${quantity}&unit=${unit}&ingredient_type=${ingredient_type}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert(data.message);
                        showAlert('success-alert', data.message);
                        loadMenuIngredients(menu_id); // Refresh ingredient table
                        document.getElementById("submit-ingredient").textContent = "ADD"; // Reset button text
                        delete document.getElementById("submit-ingredient").dataset.update; // Remove update flag
                        createMenu.reset();
                        fetchMenus('Samgyupsal', '.menu-items');
                        fetchMenus('Add-Ons', '.sauces');
                    } else {
                        console.error("Error:", data.error);
                    }
                })
                .catch(error => console.error("Error:", error))
                .finally(() => {
                    loader.hide()
                })
        });
    }


    document.getElementById('menu').addEventListener('change', function(e) {
        e.preventDefault();
        loadMenuIngredients(this.value);
    });

    function loadMenuIngredients(menuId) {
        fetch(`db_queries/select_queries/fetch_menu_ingredient.php?menu_id=${menuId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tableBody = document.querySelector('.custom-menus-table tbody');
                    tableBody.innerHTML = ''; // Clear previous data

                    data.ingredients.forEach(ingredient => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${ingredient.ingredient_id}</td>
                        <td>${ingredient.category}</td>
                        <td>${ingredient.ingredient_name}</td>
                        <td>${ingredient.quantity_required} ${ingredient.unit}</td>
                        <td>${ingredient.date_added}</td>
                        <td>${ingredient.ingredient_type}</td>
                        <td>
                            <button class="edit-btn" id="edit-btn" onclick="openEditModal(${ingredient.menu_id}, ${ingredient.ingredient_id}, ${ingredient.quantity_required},'${ingredient.unit}');">Edit</button>
                            <button class="delete-btn edit-btn" data-id="${ingredient.ingredient_id}">Delete</button>
                        </td>
                    `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(error => console.error('Error fetching menu ingredients:', error));
    }

    function openEditModal(menuId, ingredientId, quantity, unit) {
        document.getElementById('menu_id').value = menuId;
        document.getElementById('menu').value = menuId;
        document.getElementById('ingredients').value = ingredientId;
        document.getElementById('quantity').value = quantity;
        document.getElementById('unit').value = unit;

        document.getElementById('submit-ingredient').textContent = "UPDATE"; // Change button text
        document.getElementById('submit-ingredient').dataset.update = "true"; // Set update flag
    }


    document.getElementById("createMenuListForm").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        let menuId = document.getElementById("edit_menu_id").value;
        let actionUrl = menuId ? "db_queries/update_queries/update_menu.php" : "db_queries/insert_queries/insert_menu.php";

        loader.show()
        fetch(actionUrl, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert(menuId ? "Menu updated successfully!" : "Menu added successfully!");
                    showAlert('success-alert', menuId ? "Menu updated successfully!" : "Menu added successfully!");
                    loadMenus();
                    this.reset();
                    document.getElementById("submitButton").textContent = "ADD"; // Reset button text
                    document.getElementById("edit_menu_id").value = ""; // Clear ID
                    fetchMenus('Samgyupsal', '.menu-items');
                    fetchMenus('Add-Ons', '.sauces');
                } else {
                    // alert(data.error);
                    CustomAlert.alert(data.error, 'error');
                }
            })
            .catch(error => console.error("Error:", error))
            .finally(() => {
                loader.hide();
            })
    });

    // Add event listeners to Edit buttons (delegated event handling)

    document.querySelector(".menus-table").addEventListener("click", function(event) {
        event.preventDefault();

        if (event.target.closest(".edit-menu")) {
            let menu = JSON.parse(event.target.closest(".edit-menu").dataset.id);
            console.log(menu);
            document.getElementById("edit_menu_id").value = menu.menu_id;
            document.getElementById("name").value = menu.name;
            document.getElementById("category").value = menu.category;
            document.getElementById("menu-type").value = menu.menu_type;
            document.getElementById("price").value = menu.price;
            document.getElementById("submitButton").textContent = "Update";

            const isAvailable = document.getElementById("availability");

            if (menu.availability === 1) {
                isAvailable.value = "Available";
            } else {
                isAvailable.value = "Not Available";
            }
            // fetch(`db_queries/select_queries/fetch_to_edit_menu.php?id=${menuId}`)
            //     .then(response => response.json())
            //     .then(menu => {
            //         document.getElementById("edit_menu_id").value = menu.menu_id;
            //         document.getElementById("name").value = menu.name;
            //         document.getElementById("category").value = menu.category;
            //         document.getElementById("menu-type").value = menu.menu_type;
            //         document.getElementById("price").value = menu.price;
            //         document.getElementById("availability").value = menu.availability;
            //         document.getElementById("submitButton").textContent = "Update";
            //         loadMenus();
            //     })
            //     .catch(error => console.error("Error fetching menu details:", error));
        }

        if (event.target.classList.contains("delete-btn")) {
            let menuId = event.target.getAttribute("data-id");

            CustomAlert.confirm("Are you sure you want to delete this menu item?", "warning")
                .then(result => {
                    if (!result) return;

                    loader.show();
                    fetch("db_queries/delete_queries/delete_menu.php", {
                            method: "POST",
                            body: new URLSearchParams({
                                menu_id: menuId
                            }),
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // alert("Menu item deleted successfully!");
                                showAlert('success-alert', "Menu item deleted successfully!");
                                event.target.closest("tr").remove(); // Remove row from the table
                                loadMenus(); // Refresh the table
                            } else {
                                // alert("Error: " + data.error);
                                CustomAlert.alert(data.error, 'error');
                            }
                        })
                        .catch(error => console.error("Error deleting menu item:", error))
                        .finally(() => {
                            loader.hide();
                        });
                });
        }
    });

    let MenuCurrentPage = 1;
    let MenuLimit = 10; // rows per page
    let menuTotalRows = 0;

    async function loadMenus(page = 1) {
        MenuCurrentPage = page;

        try {
            const response = await fetch("db_queries/select_queries/fetch_menu.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    page: MenuCurrentPage,
                    limit: MenuLimit
                })
            });

            // Read the text (tr rows) and get total rows from header
            const menuRowsHtml = await response.text();
            menuTotalRows = parseInt(response.headers.get("X-Total-Rows")) || 0;

            // Update pagination UI and table
            updateMenuPaginationUI();
            document.querySelector(".menus-table tbody").innerHTML = menuRowsHtml;

        } catch (error) {
            console.error("Error fetching menus:", error);
        }
    }

    function updateMenuPaginationUI() {
        const totalPages = Math.ceil(menuTotalRows / MenuLimit);

        document.getElementById("menu-pageInfo").innerText =
            `Page ${MenuCurrentPage} of ${totalPages}`;

        document.getElementById("menu-prevPage").disabled = MenuCurrentPage === 1;
        document.getElementById("menu-nextPage").disabled = MenuCurrentPage === totalPages;
    }

    document.getElementById("menu-prevPage").addEventListener("click", () => {
        if (MenuCurrentPage > 1) {
            loadMenus(MenuCurrentPage - 1);
        }
    });

    document.getElementById("menu-nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(menuTotalRows / MenuLimit);
        if (MenuCurrentPage < totalPages) {
            loadMenus(MenuCurrentPage + 1);
        }
    });

    document.getElementById("promoForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const form = new FormData(this);
        const isUpdate = form.get("promoId") !== "";

        const url = isUpdate ?
            "db_queries/update_queries/update_promo.php" :
            "db_queries/insert_queries/insert_promo.php"; // ← You should have this script already

        loader.show()
        fetch(url, {
                method: "POST",
                body: new URLSearchParams(form)
            })
            .then(res => res.json())
            .then(res => {
                // alert(isUpdate ? "Promo updated!" : "Promo added!");
                showAlert('success-alert', isUpdate ? "Promo updated!" : "Promo added!");
                fetchPromos("active");
                document.getElementById("promoForm").reset();
                document.getElementById("promoSubmitBtn").textContent = "ADD PROMO";
            })
            .catch(error => {
                console.error('Error:', error);
            }).finally(() => {
                loader.hide();
            });
    });


    document.getElementById("promoFilter").addEventListener("change", function() {
        fetchPromos(this.value, PromoCurrentPage); // Fetch based on selected filter
    });

    let PromoCurrentPage = 1;
    let PromoLimit = 10; // rows per page
    let PromoTotalRows = 0;

    async function fetchPromos(status, page = 1) {
        PromoCurrentPage = page;

        try {
            const response = await fetch("db_queries/select_queries/fetch_promos.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    status,
                    page: PromoCurrentPage,
                    limit: PromoLimit
                })
            });

            const data = await response.json();
            if (data.success) {
                PromoTotalRows = data.totalRows;
                updatePromoPaginationUI();

                const promoTableBody = document.getElementById("promoTableBody");
                promoTableBody.innerHTML = "";

                let activeCount = 0;
                data.promos.forEach(promo => {
                    if (promo.status === "active") activeCount++;
                    promoTableBody.innerHTML += `
                    <tr>
                        <td>${promo.name}</td>
                        <td>${promo.discount_type}</td>
                        <td>${promo.discount_value}</td>
                        <td>${promo.start_date}</td>
                        <td>${promo.end_date}</td>
                        <td>${promo.applicable_menu}</td>
                        <td>${promo.status}</td>
                        <td>
                            <div class="action">
                                <button class="edit-btn edit-promo" data-id='${JSON.stringify(promo)}'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="delete-btn edit-btn delete-promo" data-id="${promo.promo_id}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                });

                document.querySelector(".custom-total-count").textContent = activeCount;

            } else {
                console.error("Error fetching promos:", data.message);
            }
        } catch (error) {
            console.error("Error fetching promos:", error);
        }
    }


    function updatePromoPaginationUI() {
        const totalPages = Math.ceil(PromoTotalRows / PromoLimit);

        document.getElementById("promo-pageInfo").innerText =
            `Page ${PromoCurrentPage} of ${totalPages}`;

        document.getElementById("promo-prevPage").disabled = PromoCurrentPage === 1;
        document.getElementById("promo-nextPage").disabled = PromoCurrentPage === totalPages;
    }

    document.getElementById("promo-prevPage").addEventListener("click", () => {
        if (PromoCurrentPage > 1) {
            fetchPromos("active", PromoCurrentPage - 1);
        }
    });

    document.getElementById("promo-nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(PromoTotalRows / PromoLimit);
        if (PromoCurrentPage < totalPages) {
            fetchPromos("active", PromoCurrentPage + 1);
        }
    });

    document.addEventListener("click", function(e) {
        const target = e.target;

        if (target.closest(".edit-promo")) {
            const promo = JSON.parse(target.closest(".edit-promo").dataset.id);

            document.getElementById("promoId").value = promo.promo_id;
            document.getElementById("promoName").value = promo.name;
            document.getElementById("discount_type").value = promo.discount_type;
            document.getElementById("discount_value").value = promo.discount_value;
            document.getElementById("start_date").value = promo.start_date;
            document.getElementById("end_date").value = promo.end_date;
            document.getElementById("applicable_menu").value = promo.applicable_menu_id ?? "";

            document.getElementById("promoSubmitBtn").textContent = "UPDATE PROMO";
        }

        if (target.closest(".delete-promo")) {
            const promoId = target.closest(".delete-promo").dataset.id;
            CustomAlert.confirm("Are you sure you want to delete this promo?", "warning")
                .then(result => {
                    if (!result) return;
                    loader.show()
                    fetch("db_queries/delete_queries/delete_promo.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `promoId=${promoId}`
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === "success") {
                                // alert("Promo deleted.");
                                showAlert('success-alert', 'Promo deleted.');
                                fetchPromos("all", PromoCurrentPage);
                            } else {
                                // alert("Failed to delete promo.");
                                CustomAlert.alert('Failed to delete promo.', 'error');
                            }
                        })
                        .catch(error => console.error('Error:', error))
                        .finally(() => {
                            loader.hide()
                        })
                });

        }
    });



    let orders = {};

    let originalOrders = {}; // Store original order items
    let dineType = '';
    // Buttons Negative, Plus, Reset
    document.addEventListener("DOMContentLoaded", function() {
        fetchMenus('Samgyupsal', '.menu-items');
        fetchMenus('Add-Ons', '.sauces');


        const clickMenu = document.querySelectorAll('.menu-items, .sauces');

        clickMenu.forEach(function(menu) {
            menu.addEventListener("click", function(event) {
                if (event.target.classList.contains("plus-item")) {
                    let itemElement = event.target.closest(".menu-item");
                    if (!itemElement) return;

                    let quantityElement = event.target.previousElementSibling;
                    if (!quantityElement || !quantityElement.classList.contains("item-quantity")) return;

                    let quantity = parseInt(quantityElement.innerText) || 0;
                    quantity += 1;
                    quantityElement.innerText = quantity;

                    // Fix selection for item name and price
                    let menuId = itemElement.querySelector('#menu_id').value;
                    let itemName = itemElement.querySelector("#item-title").innerText.trim();

                    let price = parseFloat(itemElement.querySelector("#item_price").innerText.replace("₱", ""));
                    updateOrder(menuId, itemName, price, quantity);
                    showAlert("success-alert", "Item added to order! ID: " + menuId + ", Quantity: " + quantity); // Show success alert
                }
            });

            menu.addEventListener("click", function(event) {
                if (event.target.classList.contains("minus-item")) {
                    let itemElement = event.target.closest(".menu-item");
                    if (!itemElement) return;

                    let quantityElement = event.target.nextElementSibling;
                    if (!quantityElement || !quantityElement.classList.contains("item-quantity")) return;

                    let quantity = parseInt(quantityElement.innerText) || 0;
                    if (quantity > 0) quantity -= 1;
                    quantityElement.innerText = quantity;

                    let menuId = itemElement.querySelector('#menu_id').value;
                    let itemName = itemElement.querySelector("#item-title").innerText.trim();
                    let price = parseFloat(itemElement.querySelector("#item_price").innerText.replace("₱", ""));

                    updateOrder(menuId, itemName, price, quantity);
                    showAlert("error-alert", "Item removed from order! ID: " + menuId + ", Quantity: " + quantity); // Show error alert
                }
            });
        })

        const dineIn = document.getElementById('dine-in');
        const takeOut = document.getElementById('take-out');

        if (dineIn) {
            dineIn.addEventListener("click", function() {
                dineType = "Dine-In"
                showAlert('success-alert', 'Dine In');
                renderOrderList()
            });
        }
        if (takeOut) {
            takeOut.addEventListener("click", function() {
                dineType = "Take-Out";
                showAlert('success-alert', 'Take Out');
                renderOrderList()
            });
        }

        // Function to update the order list
        function updateOrder(menuId, name, price, quantity) {
            if (quantity > 0) {

                if (!dineType) {
                    dineType = 'Dine-In';
                }

                orders[menuId] = {
                    menu_id: menuId,
                    name: name,
                    quantity: quantity,
                    total_price: price * quantity,
                    dine: dineType
                };
            } else {
                delete orders[menuId]; // Remove item if quantity is 0
            }
            renderOrderList();
            updateTotal();
        }
        renderOrderList();
    });

    document.getElementById('clear-order').addEventListener('click', function() {
        orders = {};
        document.getElementById('total-amount').textContent = '0.00';
        renderOrderList();
        updateItemQuantity();
    })
    // Function to render order receipt
    function renderOrderList() {
        const token = localStorage.getItem("jwt_token")
        if (!token) {
            window.location.href = "index.php"
            return
        }

        const payloadBase64 = token.split('.')[1];
        const payloadJson = atob(payloadBase64);
        const payload = JSON.parse(payloadJson);

        let orderListElement = document.querySelector(".list_orders");
        orderListElement.innerHTML = ""; // Clear previous list

        if (Object.keys(orders).length === 0) {
            orderListElement.innerHTML = "<p class='empty-order'>No items in the order.</p>";
            return;
        }

        let receiptContainer = document.createElement("div");
        receiptContainer.classList.add("receipt");

        let receiptHeader = `
                    <div class="receipt-header">
                        <img src="Capstone Assets/LogoMain.png" alt="Chia's Corner Logo" class="receipt-logo">
                        <h2>CHIA'S CORNER</h2>
                        <p>Langaray St, Dagat-dagatan Caloocan City, Philippines</p>
                        <p>Phone#: 0926 200 4346</p>
                    </div>
                `;

        let receiptBody = `
                    <div class="receipt-body">
                        <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                        <p><strong>Cashier:</strong> ${payload.name}</p> <!-- Placeholder for now -->
                        <div class="receipt-separator"></div>
                        <p><strong>Order Type:</strong> ${dineType}</p>
                        <div class="receipt-separator"></div>
                        <p><strong>Items Ordered:</strong></p>`;

        let total = 0;
        for (let menuId in orders) {
            let item = orders[menuId];
            receiptBody += `
                        <div class="receipt-item">
                            <div class="item-details">
                                <div class="item-names">${item.name}</div>
                                <span>${item.quantity} x ₱${(item.total_price / item.quantity).toFixed(2)}</span>
                            </div>
                        </div>
                    `;
            total += item.total_price;
        }
        let receiptFooter = `
                <div class="receipt-separator"></div>
                <div class="item-details">
                    <p>Total:</p>
                    <span class="item-total">₱${total.toFixed(2)}</span>
                </div>
                <div class="receipt-footer">
                    <p>THANK YOU AND ENJOY!</p>
                </div>
            `;

        receiptContainer.innerHTML = receiptHeader + receiptBody + receiptFooter;
        orderListElement.appendChild(receiptContainer);
    }




    function updateTotal() {
        let total = 0;
        for (let menuId in orders) {
            total += orders[menuId].total_price;
        }
        document.getElementById("total-amount").innerText = total.toFixed(2);
    }

    function fetchMenus(category, menu) {
        fetch(`db_queries/select_queries/fetch_list_menu.php?menu_type=${encodeURIComponent(category)}`)
            .then(response => response.text())
            .then(data => {
                document.querySelector(menu).innerHTML = data;
            })
            .catch(error => console.error("Error loading menu:", error));
    }

    // Function to place order
    function placeOrder() {
        const token = localStorage.getItem("jwt_token");
        if (!token) {
            window.location.href = "index.php";
            return
        }

        const payloadBase64 = token.split('.')[1];
        const payloadJson = atob(payloadBase64);
        const payload = JSON.parse(payloadJson);
        const userId = payload.user_id;

        const orderData = Object.keys(orders).map(menuId => ({
            user_id: userId,
            menu_id: menuId,
            quantity: orders[menuId].quantity,
            total_price: orders[menuId].total_price,
            dine: orders[menuId].dine,
        }));

        if (orderData.length === 0) {
            return;
        }
        const formData = new URLSearchParams();
        formData.append("orders", JSON.stringify(orderData));
        loader.show();
        fetch("db_queries/insert_queries/place_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: formData.toString(),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // alert(`Order Placed! Order ID: ${data.order_id}, Total: ₱${data.total_price}`);
                    showAlert('success-alert', `Order Placed! Order ID: ${data.order_id}, Total: ₱${data.total_price}`);
                    orders = {}; // Clear orders after placing
                    renderOrderList();
                    updateTotal();
                    updateItemQuantity();
                    setTimeout(() => {
                        location.href = 'orderlist.php';
                    }, 1000);
                } else {
                    CustomAlert.alert(`Error: ${data.message}`, 'error');
                }
            })
            .catch(error => console.error("Error placing order:", error))
            .finally(() => {
                loader.hide();
            });
    }

    const placeOrders = document.getElementById("placeOrder");
    if (placeOrders) {
        placeOrders.addEventListener("click", function(e) {
            e.preventDefault();
            placeOrder();
        });
    }

    function updateItemQuantity() {
        const menuItems = document.querySelectorAll('.menu-item'); // Select all menu items
        menuItems.forEach(item => {
            const quantityElement = item.querySelector('.item-quantity'); // Find the quantity element within each menu item
            if (quantityElement) {
                quantityElement.innerText = 0; // Set the quantity to 0
            }
        });
    }


    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('order_id');

    document.addEventListener("DOMContentLoaded", function() {
        const orderIdInput = document.getElementById("order_id"); // Get the order_id input field
        const placeOrder = document.getElementById('placeOrder'); // Get the place order button
        const updateOrder = document.getElementById('updateOrder'); // Get the update order button
        const cancelOrder = document.getElementById('cancel'); // Get the cancel order button

        // Show place order by default
        placeOrder.style.display = "block";
        updateOrder.style.display = "none";
        cancelOrder.style.display = "none";
        // Fetch order ID from URL and populate order_id input field

        // If there is an orderId, set the input field and handle UI logic
        if (orderId) {
            if (orderIdInput) {
                orderIdInput.value = orderId; // Set the order_id in the input field
            }
            fetchOrderDetails(orderId);
            placeOrder.style.display = "none";
            updateOrder.style.display = "block";
            cancelOrder.style.display = "block";
        }

        // Event listener for the update order button
        if (updateOrder) {
            updateOrder.addEventListener("click", function() {
                saveOrder(); // Call the function to save the order
                setTimeout(() => {
                    location.href = 'orderlist.php';
                }, 1000);
            });
        }

        // Event listener for the cancel order button
        if (cancelOrder) {
            cancelOrder.addEventListener("click", function(e) {
                e.preventDefault(); // Prevent the default action
                CustomAlert.confirm("Are you sure you want to cancel this order?", "warning")
                    .then(result => {
                        if (!result) return;

                        const url = window.location.href; // Get the current URL
                        const newUrl = url.split('?')[0]; // Remove the query string (the part after '?')
                        window.history.replaceState({}, document.title, newUrl); // Update the URL without reloading the page
                        orders = {}; // Clear the orders
                        originalOrders = {}; // Clear the original orders
                        renderOrderList(); // Render the order list
                        updateTotal(); // Update the total amount

                        orderIdInput.value = ""; // Clear the order_id input field
                        placeOrder.style.display = "block";
                        updateOrder.style.display = "none";
                        cancelOrder.style.display = "none";

                        location.href = 'orderlist.php'; // Redirect to menu page
                    });
            });
        }
    });

    // Fetch Existing Order Details
    function fetchOrderDetails(orderId) {
        fetch(`db_queries/select_queries/get_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    orders = {}; // Reset modified orders
                    originalOrders = {}; // Reset original orders

                    data.items.forEach(item => {
                        orders[item.menu_id] = {
                            menu_id: item.menu_id,
                            name: item.name,
                            quantity: item.quantity,
                            total_price: item.price * item.quantity,
                            dine: data.dineType
                        };
                        // Store original state
                        originalOrders[item.menu_id] = {
                            ...orders[item.menu_id]
                        };
                    });

                    renderOrderList();
                    updateTotal();
                } else {
                    console.error("Failed to fetch order details.");
                }
            })
            .catch(error => console.error("Error:", error));
    }


    // Save Updated Order (Insert, Update, Delete)
    function saveOrder() {
        const token = localStorage.getItem("jwt_token");
        if (!token) {
            window.location.href = "index.php";
            return
        }
        const payloadBase64 = token.split('.')[1];
        const payloadJson = atob(payloadBase64);
        const payloads = JSON.parse(payloadJson);

        let orderId = document.getElementById("order_id").value;
        let updatedItems = Object.values(orders);
        let removedItems = [];

        // Identify removed items
        Object.keys(originalOrders).forEach(menuId => {
            if (!orders[menuId]) {
                removedItems.push(menuId);
            }
        });

        const user_id = payloads.user_id;

        let payload = {
            user_id: user_id,
            order_id: orderId,
            updatedItems: updatedItems,
            removedItems: removedItems
        };

        // console.log("Sending Data:", JSON.stringify(payload));
        loader.show()
        fetch("db_queries/update_queries/update_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                // console.log("Server Response:", data);
                if (data.status === "success") { // Changed from `data.success`
                    // alert("Order updated successfully!");
                    showAlert('success-alert', 'Order updated successfully!')
                    const orderIdInput = document.getElementById("order_id"); // Get the order_id input field
                    const placeOrder = document.getElementById('placeOrder'); // Get the place order button
                    const updateOrder = document.getElementById('updateOrder'); // Get the update order button
                    const cancelOrder = document.getElementById('cancel'); // Get the cancel order button

                    const url = window.location.href; // Get the current URL
                    const newUrl = url.split('?')[0]; // Remove the query string (the part after '?')
                    window.history.replaceState({}, document.title, newUrl); // Update the URL without reloading the page
                    orders = {}; // Clear the orders
                    originalOrders = {}; // Clear the original orders
                    renderOrderList(); // Render the order list
                    updateTotal(); // Update the total amount

                    orderIdInput.value = ""; // Clear the order_id input field
                    placeOrder.style.display = "block";
                    updateOrder.style.display = "none";
                    cancelOrder.style.display = "none";

                } else {
                    console.error("Failed to update order:", data.message);
                    CustomAlert.alert("Error: " + data.message, 'alert');
                }
            })
            .catch(error => {
                console.error("Error:", error);
                CustomAlert.alert("Failed to update order. See console for details.", 'error');
            })
            .finally(() => {
                loader.hide()
            })
    }

    function fetchThisMenu() {
        fetchMenus('Samgyupsal', '.menu-items');
        fetchMenus('Add-Ons', '.sauces');
    }
    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('menu-channel', 'modify-menu', () => loadMenus(MenuCurrentPage), 200);
    pusherManager.bind('menu-channel', 'modify-menu', fetchThisMenu, 200);
    pusherManager.bind('promo-channel', 'modify-promo', () => fetchPromos("active", PromoCurrentPage), 200);
</script>
</body>

</html>
