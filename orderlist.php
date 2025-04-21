<?php
include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

try {

    // Query to get orders and order items
    $sql = "SELECT o.order_id, 
                    GROUP_CONCAT(m.name SEPARATOR ', ') AS order_list
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            GROUP BY o.order_id
            ORDER BY o.order_id DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>
<link rel="stylesheet" href="css/orderlist.css">


<!-- Orders Table -->
<div class="wrapper">
    <div class="table-container">
        <table id="ordersTable">
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Order ID</th>
                    <th>Order List</th>
                    <th>Total Price</th>
                    <th>Payment Status</th>
                    <th>Paid Amount</th>
                    <th>Discount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Orders will be loaded dynamically by JS -->
            </tbody>
        </table>
    </div>
</div>


<!-- Background Blur Overlay for Checout -->

<div id="exo-checkout-overlay" class="exo-modal-overlay"></div>

<!-- Checkout Modal -->
<div id="exo-checkout-modal" class="exo-modal">
    <div class="exo-modal-header">
        <div class="exo-modal-title">CHECKOUT</div>
        <!-- <button class="exo-close-btn" onclick="exoCloseCheckoutModal()">✖</button> -->
    </div>

    <div class="exo-modal-body">
        <h2 class="modal-section-title">Checkout Order</h2>

        <div id="orderDetails">
            <!-- Order details will be loaded here dynamically -->
        </div>

        <!-- Payment Inputs -->
        <div class="payment-section">
            <label for="amountPaid">Amount Paid:</label>
            <input type="number" id="amountPaid" class="input-field" placeholder="Enter amount paid">

            <label for="paymentMethod">Payment Method:</label>
            <select id="paymentMethod" class="input-field">
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="exo-modal-buttons">
            <button class="exo-action-btn exo-cancel-btn" onclick="exoCloseCheckoutModal()">Cancel</button>
            <button class="exo-action-btn exo-confirm-btn" id="confirm-payment">Confirm</button>
        </div>
    </div>
</div>


<!-- Receipt Modal for Checkout -->

<div class="exo-receipt-modal-bg" id="exo-receipt-modal-bg" onclick="exoCloseReceipt()">
    <div class="exo-receipt-modal">
        <div class="exo-receipt-paper">

        </div>
    </div>
</div>

<!-- Chian's Footer Section -->
<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchOrders();

        // Use event delegation to handle dynamically added buttons (cancel and check out)
        document.querySelector("#ordersTable tbody").addEventListener("click", function(event) {
            if (event.target.classList.contains("cancel-btn")) {
                let orderId = event.target.getAttribute("data-order-id");

                if (confirm("Are you sure you want to cancel this order?")) {
                    fetch("db_queries/delete_queries/cancel_order.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "order_id=" + orderId
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.status === "success") {
                                fetchOrders(); // Refresh the orders list dynamically
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            }

            // Check if the clicked button is for "Check Out"
            if (event.target.classList.contains("remove-btn")) {
                let orderId = event.target.getAttribute("data-order-id");
                // Open the checkout modal and load order details
                exoOpenCheckoutModal(orderId);
                // console.log("Check Out button clicked");
            }

            // Check if the clicked button is for "Add Item"
            if (event.target.classList.contains("add-item-btn")) {
                let orderId = event.target.getAttribute("data-order-id");
                window.location.href = `menu.php?order_id=${orderId}`;
            }
        });

        const confirmPaymentButton = document.getElementById("confirm-payment");
        confirmPaymentButton.addEventListener("click", function(e) {
            e.preventDefault();
            exoShowReceipt();
        });

    });
    // Function to Open Checkout Modal
    function exoOpenCheckoutModal(orderId) {
        let modal = document.getElementById("exo-checkout-modal");
        let overlay = document.getElementById("exo-checkout-overlay");

        loadOrderDetails(orderId); // Load order details when opening the modal
        modal.style.display = "block";
        overlay.style.display = "block";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
    }

    // Function to Close Checkout Modal
    function exoCloseCheckoutModal() {
        let modal = document.getElementById("exo-checkout-modal");
        let overlay = document.getElementById("exo-checkout-overlay");

        modal.classList.remove("show");

        setTimeout(() => {
            modal.style.display = "none";
            overlay.style.display = "none";
        }, 300);
    }

    // Function to Show Receipt and Hide Checkout Modal
    function ShowReceipt(orderId) {
        exoCloseCheckoutModal();

        setTimeout(() => {
            let receiptModalBg = document.getElementById("exo-receipt-modal-bg");
            let receiptModal = document.querySelector(".exo-receipt-modal");

            fetchReceipt(orderId);
            receiptModalBg.style.display = "block";
            setTimeout(() => {
                receiptModal.classList.add("show");
            }, 10);
        }, 300);
    }

    // Function to Close Receipt on Click
    function exoCloseReceipt() {
        let receiptModalBg = document.getElementById("exo-receipt-modal-bg");
        let receiptModal = document.querySelector(".exo-receipt-modal");

        receiptModal.classList.remove("show");

        setTimeout(() => {
            receiptModalBg.style.display = "none";
        }, 300);
    }

    // Function to fetch orders from the server
    function fetchOrders() {
        fetch('db_queries/select_queries/fetch_order_list.php')
            .then(response => response.json())
            .then(data => {
                if (data.orders) {
                    displayOrders(data.orders);
                } else if (data.error) {
                    alert('Error fetching orders: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching orders.');
            });
    }

    // Function to display orders in the table
    function displayOrders(orders) {
        const ordersTableBody = document.querySelector('#ordersTable tbody');
        ordersTableBody.innerHTML = ''; // Clear any existing rows

        if (orders.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="8">No pending orders</td>';
            ordersTableBody.appendChild(row);
        } else {
            let queueNumber = 1001;
            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${str_pad(queueNumber++, 4, '0', 'STR_PAD_LEFT')}</td>
                    <td>${order.order_id}</td>
                    <td class="order-list">${order.order_list}</td>
                    <td>$${parseFloat(order.total_price).toFixed(2)}</td>
                    <td>${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</td>
                    <td>$${parseFloat(order.paid_amount).toFixed(2)}</td>
                    <td>$${parseFloat(order.discount_amount).toFixed(2)}</td>
                    <td>
                        <div class="action-layout">
                            <button class="action-button remove-btn" data-order-id="${order.order_id}">Check Out</button>
                            <button class="action-button view-btn add-item-btn" data-order-id="${order.order_id}">Add Item</button>
                            <button class="action-button view-btn cancel-btn" data-order-id="${order.order_id}">Cancel Order</button>
                        </div>
                    </td>
                `;

                ordersTableBody.appendChild(row);
            });
        }
    }

    // Helper function to pad numbers with leading zeros
    function str_pad(input, length, pad_string, pad_type) {
        input = input.toString();
        if (input.length >= length) return input;
        const pad_length = length - input.length;
        const pad = pad_string.repeat(pad_length);
        return pad_type === 'STR_PAD_LEFT' ? pad + input : input + pad;
    }


    let globalIngredients = [];

    // Load orders
    function loadOrderDetails(orderId) {
        fetch(`db_queries/select_queries/fetch_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const order = data.order;
                const items = data.items;
                const ingredients = data.ingredients;
                const orderDetailsDiv = document.getElementById("orderDetails");
                orderDetailsDiv.innerHTML = "";

                globalIngredients = [];
                // Store the ingredients in the global variable
                globalIngredients = ingredients;

                let orderHtml = `
            <div class="order-summary">
                <input type="hidden" id="hiddenOrderId" value="${order.order_id}">
                <p><strong>Order ID:</strong> ${order.order_id}</p>
                <p id="totalPrice"><strong>Total Price:</strong> ₱${parseFloat(order.total_price).toFixed(2)}</p>
                <p><strong>Payment Status:</strong> ${order.payment_status}</p>
            </div>
        `;

                // Ordered Items
                orderHtml += "<h3 class='section-title'>Ordered Items</h3><ul class='item-list'>";
                items.forEach(item => {
                    orderHtml += `<li>${item.name} (x${item.quantity}) - ₱${parseFloat(item.price).toFixed(2)}</li>`;
                });
                // orderHtml += "</ul>";

                // // Unlimited Ingredients (with Input Fields)
                // if (ingredients.length > 0) {
                //     orderHtml += "<h3 class='section-title'>Unlimited Ingredients</h3><ul class='ingredient-list'>";
                //     ingredients.forEach(ingredient => {
                //         if (ingredient.ingredient_type === 'unli') {
                //             orderHtml += `
                //             <li class='ingredient-item'>
                //                 <input type="hidden" value="${ingredient.ingredient_id}" id="ingredient_id">
                //                 <span>${ingredient.ingredient_name}</span> 
                //                 <input type='number' class='input-field ingredient-quantity' 
                //                     data-ingredient-id="${ingredient.ingredient_id}" 
                //                     value="${ingredient.quantity_required || ''}" 
                //                     placeholder="Enter quantity">
                //             </li>
                //         `;
                //         }
                //     });
                //     orderHtml += "</ul>";
                // }


                orderDetailsDiv.innerHTML = orderHtml;
                document.getElementById("exo-checkout-modal").style.display = "block";
            })
            .catch(error => console.error("Error loading order details:", error));
    }


    function exoShowReceipt(ingredients) {
        let orderId = document.getElementById('hiddenOrderId').value;
        let amountPaid = parseFloat(document.getElementById("amountPaid").value);
        let paymentMethod = document.getElementById("paymentMethod").value;

        // Extract numeric total price correctly
        let totalPriceText = document.getElementById("totalPrice").textContent;
        let totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));

        // let consumedIngredients = [];

        // // Loop through all globalIngredients (both fixed and unli)
        // globalIngredients.forEach(ingredient => {
        //     let quantityConsumed;

        //     // If the ingredient type is 'unli', get the quantity from the input field
        //     if (ingredient.ingredient_type === 'unli') {
        //         let input = document.querySelector(`.ingredient-quantity[data-ingredient-id="${ingredient.ingredient_id}"]`);

        //         if (input) {
        //             let inputValue = parseFloat(input.value);
        //             if (isNaN(inputValue) || inputValue <= 0) {
        //                 alert('Please enter a valid quantity for unlimited ingredients.');
        //                 return; // Stop further execution if input is invalid
        //             }
        //             quantityConsumed = inputValue;
        //         }
        //     } else if (ingredient.ingredient_type === 'fixed') {
        //         // For 'fixed' ingredients, use the predefined quantity_required
        //         quantityConsumed = ingredient.quantity_required;
        //     }

        //     // Only push to consumedIngredients if quantityConsumed is a valid number and greater than 0
        //     if (!isNaN(quantityConsumed) && quantityConsumed > 0) {
        //         consumedIngredients.push({
        //             ingredient_id: ingredient.ingredient_id,
        //             quantity: quantityConsumed
        //         });
        //     }
        // });

        // Validate if at least one ingredient was added
        // if (consumedIngredients.length === 0) {
        //     alert("There is no consumed ingredients.");
        //     return;
        // }

        // Validate amountPaid
        if (isNaN(amountPaid) || amountPaid <= 0) {
            alert("Please enter a valid amount paid.");
            return;
        }

        // Validate if amount paid is enough
        if (isNaN(totalPrice) || amountPaid < totalPrice) {
            alert(`Insufficient payment. Total price is ₱${totalPrice.toFixed(2)}.`);
            return;
        }

        // Send Data to Server
        fetch("db_queries/insert_queries/process_checkout.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    order_id: orderId,
                    amount_paid: amountPaid,
                    payment_method: paymentMethod,
                    // consumed_ingredients: consumedIngredients
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Checkout Successful!");
                    fetchOrders();
                    ShowReceipt(orderId);
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error("Error processing checkout:", error));
    }



    function fetchReceipt(orderId) {
        fetch(`db_queries/select_queries/fetch_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                let receiptList = document.querySelector('.exo-receipt-paper');
                receiptList.innerHTML = "";

                if (data.items.length === 0) {
                    receiptList.innerHTML = "<p class='empty-order'>No items in the order.</p>";
                    return;
                }

                let order = data.order;
                let items = data.items;

                let subtotal = parseFloat(order.total_price);
                let discount = parseFloat(order.discount_amount) || 0;
                let totalAfterDiscount = subtotal - discount;
                let paidAmount = parseFloat(order.paid_amount) || 0;
                let grandTotal = totalAfterDiscount;
                let change = paidAmount - grandTotal;

                // Correct VAT calculations
                let vatableSales = subtotal / 1.12; // Back-calculate VATable sales
                let vatAmount = subtotal - vatableSales; // 12% of VATable sales
                let vatExempt = 0.00; // Static for now
                let zeroRated = 0.00; // Static for now

                let receiptContainer = document.createElement("div");
                receiptContainer.classList.add("receipt");

                let receiptHeader = `
                <div class="exo-receipt-header">
                    <img src="Capstone Assets/LogoMain.png" alt="Chia's Corner Logo" class="exo-receipt-logo">
                    <h2>CHIA'S CORNER</h2>
                    <p>Langaray St, Dagat-dagatan Caloocan City, Philippines</p>
                    <p>Phone#: 0926 200 4346</p>
                </div>
                <div class="exo-receipt-separator"></div>
            `;

                let receiptBody = `
                <div class="exo-receipt-body">
                    <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                    <p><strong>Cashier:</strong> ${order.cashier_name || "N/A"}</p>
                    <div class="exo-receipt-separator"></div>
                    <p><strong>Order Type:</strong> ${order.dine || "N/A"}</p>
                    <div class="exo-receipt-separator"></div>
                    <p><strong>Items Ordered:</strong></p>
            `;

                items.forEach(item => {
                    receiptBody += `
                    <div class="receipt-item">
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <span>${item.quantity} x ₱${parseFloat(item.price).toFixed(2)}</span>
                        </div>
                    </div>
                `;
                });

                let receiptFooter = `
                <div class="exo-receipt-separator"></div>
                <div class="item-details exo-receipt-total">
                    <p><strong>Grand Total:</strong></p>
                    <span class="item-total"><strong>₱${grandTotal.toFixed(2)}</strong></span>
                </div>
                <div class="item-details">
                    <p>Discount:</p>
                    <span class="item-total">-₱${discount.toFixed(2)}</span>
                </div>
                <div class="item-details">
                    <p>VAT (12%):</p>
                    <span class="item-total">₱${vatAmount.toFixed(2)}</span>
                </div>
                <div class="item-details">
                    <p>VATable Sales:</p>
                    <span class="item-total">₱${vatableSales.toFixed(2)}</span>
                </div>
                
                <div class="item-details">
                    <p>VAT Exempt Sales:</p>
                    <span class="item-total">₱${vatExempt.toFixed(2)}</span>
                </div>
                <div class="item-details">
                    <p>Zero-rated Sales:</p>
                    <span class="item-total">₱${zeroRated.toFixed(2)}</span>
                </div>
                <div class="exo-receipt-separator"></div>
                <div class="item-details">
                    <p>Paid Amount:</p>
                    <span class="item-total">₱${paidAmount.toFixed(2)}</span>
                </div>
                <div class="item-details">
                    <p>Change:</p>
                    <span class="item-total">₱${change.toFixed(2)}</span>
                </div>
                <div class="exo-receipt-footer">
                    <p>This serves as your OFFICIAL RECEIPT</p>
                    <p>Thank you and enjoy!</p>
                </div>
            `;

                receiptContainer.innerHTML = receiptHeader + receiptBody + receiptFooter;
                receiptList.appendChild(receiptContainer);
            })
            .catch(error => console.error("Error loading order details:", error));
    }
</script>

</body>

</html>
