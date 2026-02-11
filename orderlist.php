<?php
include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';


?>
<link rel="stylesheet" href="css/orderlist.css">
<!-- Font Awesome Free (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.3/purify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.0/jspdf.umd.min.js"></script>




<!-- Orders Table -->
<div class="wrapper">
    <div class="table-container">
        <div class="paginated-table">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <!-- <th>Queue</th> -->
                        <th>Order ID</th>
                        <th>Dine Type</th>
                        <th>Order List</th>
                        <th>Total Price</th>
                        <th>Discount</th>
                        <th>Paid Amount</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Orders will be loaded dynamically by JS -->
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

            <label for="paymentMethod" style="display: none;">Payment Method:</label>
            <select id="paymentMethod" class="input-field" style="display: none;">
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
    <div class="exo-receipt-modal" onclick="event.stopPropagation()">
        <div class="exo-receipt-paper">

        </div>
        <div class="exo-receipt-separator"></div>
        <div class="exo-modal-buttons">
            <button class="exo-action-btn exo-confirm-btn print-receipt" id="print-receipt">Print Receipt</button>
            <button class="exo-action-btn exo-confirm-btn download-receipt" id="download-receipt">Download PDF</button>
        </div>
    </div>
</div>

<div class="kitchen-slip" style="display: none;">
    <!-- kitchen slip content -->
</div>

<div class="void-modal">
    <div class="void-overlay"></div>
    <div class="void-modal-content">
        <h2>Provide Code to Void Order</h2>
        <div class="void-input">
            <input type="password" id="voidCodeInput" class="input-field" placeholder="Enter void code">
            <input type="checkbox" id="toggleVoidCode" onclick="voidCodeInput.type = this.checked ? 'text' : 'password'">
            <label for="toggleVoidCode" class="password-toggle-label"></label>
        </div>
        <div class="void-modal-buttons">
            <button class="exo-action-btns exo-confirm-btns" id="voidConfirmBtn">Confirm</button>
            <button class="exo-action-btns exo-cancel-btns" id="voidCancelBtn">Cancel</button>
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

<!-- <script src="js/qz-tray.js"></script> -->

<script>
    const token = localStorage.getItem("jwt_token");

    if (!token) {
        // alert('Restricted Access, Amin only')
        showAlert('warning-alert', 'Restricted Access, Amin only')
        location.href = 'index.php'
    }

    // Decode token
    const base64 = token.split('.')[1];
    const json = atob(base64);
    const payload = JSON.parse(json);

    const ownerID = payload.user_id;

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



    document.addEventListener("DOMContentLoaded", function() {
        fetchOrders();
        const voidModal = document.querySelector('.void-modal');
        const voidCodeInput = document.getElementById('voidCodeInput');

        const voidConfirmBtn = document.getElementById('voidConfirmBtn');
        const voidCancelBtn = document.getElementById('voidCancelBtn');
        const voidOverlay = document.querySelector('.void-overlay');

        let orderIdToVoid = null;

        const closeVoidModal = () => {
            voidModal.classList.remove('is-visible');
            voidCodeInput.value = ''; // Clear the input when closing
        }

        // 1. Cancel/Close button
        voidCancelBtn.addEventListener('click', closeVoidModal);
        // 2. Close on overlay click
        voidOverlay.addEventListener('click', closeVoidModal);

        voidConfirmBtn.addEventListener('click', async function() {
            const voidCode = voidCodeInput.value.trim();

            if (voidCode === '') {
                showAlert('warning-alert', 'Please enter a void code.');
                return;
            }

            try {
                loader.show();

                // 1. Verify admin PIN
                const pinResponse = await fetch("db_queries/select_queries/verify_pin.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        pin: voidCode
                    })
                });

                const pinData = await pinResponse.json();

                if (!pinData.success) {
                    showAlert('error-alert', 'Invalid admin code.');
                    return;
                }

                // 2. PIN is correct → cancel order
                const cancelResponse = await fetch("db_queries/delete_queries/cancel_order.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "order_id=" + encodeURIComponent(orderIdToVoid) +
                        "owner_id=" + encodeURIComponent(ownerID)

                });

                const cancelData = await cancelResponse.json();

                showAlert(cancelData.status === "success" ? 'success-alert' : 'error-alert', cancelData.message);

                if (cancelData.status === "success") {
                    fetchOrders(); // refresh order list
                }

            } catch (error) {
                console.error("Error during voiding order:", error);

            } finally {
                closeVoidModal();
                loader.hide();
            }
        });


        // Use event delegation to handle dynamically added buttons (cancel and check out)
        document.querySelector("#ordersTable tbody").addEventListener("click", function(event) {
            if (event.target.closest(".cancel-btn")) {
                orderIdToVoid = event.target.closest('.cancel-btn').getAttribute("data-order-id");

                voidModal.classList.add('is-visible')
                voidCodeInput.value = '';
                voidCodeInput.focus();
                // CustomAlert.confirm("Are you sure you want to cancel this order?", "warning")
                //     .then(result => {
                //         if (!result) return;
                //         loader.show()
                //         fetch("db_queries/delete_queries/cancel_order.php", {
                //                 method: "POST",
                //                 headers: {
                //                     "Content-Type": "application/x-www-form-urlencoded"
                //                 },
                //                 body: "order_id=" + orderId
                //             })
                //             .then(response => response.json())
                //             .then(data => {
                //                 // alert(data.message);
                //                 showAlert('success-alert', data.message)
                //                 if (data.status === "success") {
                //                     fetchOrders(); // Refresh the orders list dynamically
                //                 }
                //             })
                //             .catch(error => console.error("Error:", error))
                //             .finally(() => {
                //                 loader.hide()
                //             });
                //     });
            }

            // Check if the clicked button is for "Check Out"
            if (event.target.closest(".remove-btn")) {
                let orderId = event.target.closest('.remove-btn').getAttribute("data-order-id");
                // Open the checkout modal and load order details
                exoOpenCheckoutModal(orderId);
                // console.log("Check Out button clicked");
            }

            // Check if the clicked button is for "Add Item"
            if (event.target.closest(".add-item-btn")) {
                let orderId = event.target.closest('.add-item-btn').getAttribute("data-order-id");
                window.location.href = `Menu.php?order_id=${orderId}`;
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
        document.getElementById('amountPaid').value = '';
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
    // let previousOrderList = [];
    // Function to fetch orders from the server
    let currentPage = 1;
    let limit = 10; // rows per page
    let totalRows = 0;

    async function fetchOrders(page = 1) {
        currentPage = page

        try {
            const response = await fetch('db_queries/select_queries/fetch_order_list.php', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    page,
                    limit
                })
            });
            const data = await response.json();

            if (data.success) {
                // if (!isEqual(previousOrderList, data.orders)) {
                //     previousOrderList = data.orders;
                //     displayOrders(data.orders);
                // }
                totalRows = data.totalRows;
                updatePaginationUI();
                displayOrders(data.orders);
            } else {
                CustomAlert.alert('Error fetching orders: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error fetching orders:', error);
        }
    }
    // Deep comparison for object arrays
    function isEqual(arr1, arr2) {
        return JSON.stringify(arr1) === JSON.stringify(arr2);
    }

    // setInterval(fetchOrders, 5000);
    // Function to display orders in the table
    function displayOrders(orders) {
        const ordersTableBody = document.querySelector('#ordersTable tbody');
        ordersTableBody.innerHTML = '';

        if (orders.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="10">No pending orders</td>';
            ordersTableBody.appendChild(row);
            return;
        }

        orders.forEach(order => {
            const row = document.createElement('tr');

            // Assign color classes based on status
            let statusClass = '';
            if (order.payment_status === 'paid') statusClass = 'status-paid';
            else if (order.payment_status === 'pending') statusClass = 'status-pending';
            else if (order.payment_status === 'cancelled') statusClass = 'status-cancelled';

            // Dine type badge color
            const dineBadge =
                order.dine === 'Dine-In' ?
                `<span class="badge dine-in"><i class="fa-solid fa-utensils"></i> Dine-In</span>` :
                `<span class="badge take-out"><i class="fa-solid fa-box"></i> Take-Out</span>`;
            const paymentStatusBadge =
                order.payment_status === 'paid' ?
                `<span class="badge status-paid"><i class="fa-solid fa-circle-check"></i> Paid</span>` :
                order.payment_status === 'pending' ?
                `<span class="badge status-pending"><i class="fa-solid fa-circle-half-stroke"></i> Pending</span>` :
                `<span class="badge status-cancelled"><i class="fa-solid fa-circle-xmark"></i> Cancelled</span>`;

            row.classList.add(statusClass);
            row.innerHTML = `
        <td>#${order.order_id}</td>
        <td>${dineBadge}</td>
        <td>${order.order_list}</td>
        <td>₱ ${parseFloat(order.total_price).toFixed(2)}</td>
        <td>₱ ${parseFloat(order.discount_amount).toFixed(2)}</td>
        <td>₱ ${parseFloat(order.paid_amount).toFixed(2)}</td>
        <td>${paymentStatusBadge}</td>
        <td>
            <div class="action-layout">
                <button class="action-button remove-btn" data-order-id="${order.order_id}"><i class="fa-solid fa-cart-shopping"></i></button>
                <button class="action-button add-item-btn" data-order-id="${order.order_id}"><i class="fa-solid fa-pen-to-square"></i></button>
                <button class="action-button cancel-btn" data-order-id="${order.order_id}"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </td>
    `;

            ordersTableBody.appendChild(row);
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
            fetchOrders(currentPage - 1);
        }
    });

    document.getElementById("nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(totalRows / limit);
        if (currentPage < totalPages) {
            fetchOrders(currentPage + 1);
        }
    });

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
                    // alert(data.error);
                    showAlert('error-alert', data.error)
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
        let amountPaidInput = document.getElementById("amountPaid");
        let amountPaid = amountPaidInput.value === "" ? 0 : parseFloat(amountPaidInput.value);
        let paymentMethod = document.getElementById("paymentMethod").value;

        // Extract numeric total price correctly
        let totalPriceText = document.getElementById("totalPrice").textContent;
        let totalPrice = parseFloat(totalPriceText.replace(/[^\d.]/g, ''));

        // Debug: Log the value of amountPaid
        // console.log("Amount Paid (after parse):", amountPaid);

        // Validate amountPaid
        if (isNaN(amountPaid) || amountPaid <= 0) {
            console.log("Please enter a valid amount paid.", amountPaid);
            showAlert('warning-alert', "Please enter a valid amount paid.");
            return;
        }

        // Validate if amount paid is enough
        if (isNaN(totalPrice) || amountPaid < totalPrice) {
            // alert(`Insufficient payment. Total price is ₱${totalPrice.toFixed(2)}.`);
            showAlert('warning-alert', `Insufficient payment. Total price is ₱${totalPrice.toFixed(2)}.`)
            return;
        }
        loader.show()
        // Send Data to Server
        fetch("db_queries/insert_queries/process_checkout.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    owner_id: ownerID,
                    order_id: orderId,
                    amount_paid: amountPaid,
                    payment_method: paymentMethod,
                    // consumed_ingredients: consumedIngredients
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert("Checkout Successful!");
                    showAlert('success-alert', "Checkout Successful!")
                    fetchOrders();
                    ShowReceipt(orderId);
                    document.getElementById('amountPaid').value = '';

                    // printSingle(receiptContent);

                    // setTimeout(() => {
                    //     printSingle(kitchenSlipContent);
                    // }, 10000); // delay is important
                    // printBoth();
                } else {
                    CustomAlert.alert(data.error, 'error');
                }
            })
            .catch(error => console.error("Error processing checkout:", error))
            .finally(() => {
                loader.hide()
            });
    }

    function renderReceipts(order, items) {
        let receiptList = document.querySelector('.exo-receipt-paper');
        receiptList.innerHTML = "";

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

        // <img src="Capstone Assets/newLogo.png" class="exo-receipt-logo">
        let receiptHeader = `
                        <div class="exo-receipt-header">
                            <h2>SamgyupKaya</h2>
                            <p>Padas street, Cor Langaray St, Caloocan, 1400 Metro Manila</p>
                            <p>Phone#: 0926 200 4346</p>
                        </div>
                        <div class="exo-receipt-separator"></div>
                    `;

        let receiptBody = `
                        <div class="exo-receipt-body">
                            <p><span class="bold">OR Number:</span> ${order.order_id}</p>
                            <p><span class="bold">Date:</span> ${new Date().toLocaleString()}</p>
                            <p><span class="bold">Cashier:</span> ${order.cashier_name || "N/A"}</p>
                            <div class="exo-receipt-separator"></div>
                            <p><span class="bold">Order Type:</span> ${order.dine || "N/A"}</p>
                            <div class="exo-receipt-separator"></div>
                            <p><span class="bold">Items Ordered:</span></p>
                    `;

        items.forEach(item => {
            receiptBody += `
                            <div class="row">
                                <span class="left">${item.name}</span>
                                <span class="right">${item.quantity} x ${parseFloat(item.price).toFixed(2)}</span>
                            </div>
                        `;
        });


        let receiptFooter = `
                        <div class="exo-receipt-separator"></div>

                        <div class="row">
                            <span class="left">Discount:</span>
                            <span class="right">-${discount.toFixed(2)}</span>
                        </div>
                        <div class="exo-receipt-separator"></div>
                        <div class="row">
                            <span class="left">Paid Amount:</span>
                            <span class="right">${paidAmount.toFixed(2)}</span>
                        </div>
                        <div class="row">
                            <span class="left">Change:</span>
                            <span class="right">${change.toFixed(2)}</span>
                        </div>
                        <div class="row">
                            <span class="left">VAT (12%):</span>
                            <span class="right">${vatAmount.toFixed(2)}</span>
                        </div>
                        <div class="row">
                            <span class="left">VATable Sales:</span>
                            <span class="right">${vatableSales.toFixed(2)}</span>
                        </div>
                        <div class="row">
                            <span class="left">VAT Exempt Sales:</span>
                            <span class="right">${vatExempt.toFixed(2)}</span>
                        </div>
                        <div class="row">
                            <span class="left">Zero-rated Sales:</span>
                            <span class="right">${zeroRated.toFixed(2)}</span>
                        </div>
                        <div class="row bold">
                            <span class="left">Grand Total:</span>
                            <span class="right"><strong>${grandTotal.toFixed(2)}</strong></span>
                        </div>


                        <div class="exo-receipt-footer">
                            <span>This serves as your OFFICIAL RECEIPT</span>
                            <span>Thank you and enjoy!</span>
                            <br>
                            <span> . </span> <br>
                        </div>
                        `;
        receiptContainer.innerHTML = receiptHeader + receiptBody + receiptFooter;
        receiptList.appendChild(receiptContainer);

        sendToPrintNodeRaw(customerRaw);
        // Delay 10 seconds for kitchen slip
        setTimeout(() => {
            sendToPrintNodeRaw(kitchenRaw);
        }, 10000);
    }

    let customerRaw = ""
    let kitchenRaw = ""

    function fetchReceipt(orderId) {
        fetch(`db_queries/select_queries/fetch_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // alert(data.error);
                    showAlert('error-alert', data.error)
                    return;
                }

                if (data.items.length === 0) {
                    receiptList.innerHTML = "<p class='empty-order'>No items in the order.</p>";
                    return;
                }

                let order = data.order;
                let items = data.items;

                customerRaw = buildCustomerReceipt(order, items);
                kitchenRaw = buildKitchenSlip(order, items);

                renderReceipts(order, items);
                // printKitchenSlip(data);
            })
            .catch(error => console.error("Error loading order details:", error));
    }




    document.addEventListener('DOMContentLoaded', function() {
        const printReceipts = document.querySelector('.print-receipt');
        const downloadReceipts = document.querySelector('.download-receipt');

        // This is your updated printBoth function (assuming you are using the Canvas/Image method now)
        async function printBoth() {
            // --- DESKTOP: Print Directly using Canvas Image ---
            if (!customerRaw || !kitchenRaw) {
                showAlert("warning-alert", "No receipt data available to print.");
                return;
            }
            loader.show();
            try {
                // 1. Get Customer Receipt (Outer HTML of the main container)
                // This includes the outer wrapper and all internal content
                // let receiptContent = document.querySelector('.exo-receipt-paper').innerHTML;

                // 2. Get Kitchen Slip (Outer HTML of the main container)
                // This ensures all wrappers and content are captured
                // let kitchenSlipContent = document.querySelector('.kitchen-slip').innerHTML;


                // await convertHtmlToPdfAndSendToPrintNode(receiptContent);
                // await new Promise(resolve => setTimeout(resolve, 3000));
                // await convertHtmlToPdfAndSendToPrintNode(kitchenSlipContent);
                // if (isMobileDevice()) {
                //     // Mobile (PrintNode): Send to PrintNode one after the other
                //     console.log("Mobile detected: Printing through PrintNode...");
                //     await convertHtmlToPdfAndSendToPrintNode(receiptContent);
                //     await new Promise(resolve => setTimeout(resolve, 3000));
                //     await convertHtmlToPdfAndSendToPrintNode(kitchenSlipContent);
                // } else {
                //     // Desktop (Direct Print): Send the image directly
                //     console.log("Desktop detected: Printing directly...");
                //     // printDirectlyToPrinter(receiptContent, kitchenSlipContent);
                //     printSingle(receiptContent);

                //     setTimeout(() => {
                //         printSingle(kitchenSlipContent);
                //     }, 10000); // delay is important


                // }

                sendToPrintNodeRaw(customerRaw);

                // Delay 10 seconds for kitchen slip
                setTimeout(() => {
                    sendToPrintNodeRaw(kitchenRaw);
                }, 10000);
                showAlert("success-alert", "Receipts sent to printer successfully!");

            } catch (e) {
                console.error("Error in printBoth:", e);
                showAlert("error-alert", "Failed to send one or both print jobs.");
            } finally {
                loader.hide();
            }
        }

        printReceipts.addEventListener('click', function(e) {
            e.preventDefault();

            // Optional: Check if mobile or desktop here
            // For now, we use your PrintNode function
            printBoth();
        });

        downloadReceipts.addEventListener('click', function(e) {
            e.preventDefault();
            downloadReceiptAsPDF();
        });
    });


    // const GS = "\x1D";
    // const cut = GS + "V" + "\x00"; // cut paper
    const ESC = "\x1B";

    // ================= CUSTOMER RECEIPT =================
    function buildCustomerReceipt(order, items) {
        let lines = [];
        const width = 32; // number of characters per line (adjust based on your printer)

        lines.push(ESC + "@"); // initialize printer
        lines.push("SamgyupKaya\n");
        lines.push("Langaray St, Dagat-dagatán Caloocan City\n");
        lines.push("Phone#: 0926 200 4346\n");
        lines.push("-".repeat(width) + "\n");

        lines.push(`OR Number: ${order.order_id}\n`);
        lines.push(`Date: ${new Date().toLocaleString()}\n`);
        lines.push(`Cashier: ${order.cashier_name || "N/A"}\n`);
        lines.push(`Order Type: ${order.dine || "N/A"}\n`);
        lines.push("-".repeat(width) + "\n");
        lines.push("Items Ordered:\n");

        // Format items: name left, qty x price right
        items.forEach(item => {
            const name = item.name;
            const qtyPrice = `${item.quantity} x ${parseFloat(item.price).toFixed(2)}`;
            // pad spaces to align right
            const line = name.padEnd(width - qtyPrice.length) + qtyPrice + "\n";
            lines.push(line);
        });

        lines.push("-".repeat(width) + "\n");

        const subtotal = parseFloat(order.total_price);
        const discount = parseFloat(order.discount_amount) || 0;
        const totalAfterDiscount = subtotal - discount;
        const paid = parseFloat(order.paid_amount) || 0;
        const change = paid - totalAfterDiscount;
        const vatableSales = subtotal / 1.12;
        const vatAmount = subtotal - vatableSales;
        const vatExempt = 0.00; // adjust if needed
        const zeroRated = 0.00; // adjust if needed

        lines.push(`Subtotal:`.padEnd(width - subtotal.toFixed(2).length) + subtotal.toFixed(2) + "\n");
        if (discount) lines.push(`Discount:`.padEnd(width - discount.toFixed(2).length) + "-" + discount.toFixed(2) + "\n");
        lines.push(`Paid Amount:`.padEnd(width - paid.toFixed(2).length) + paid.toFixed(2) + "\n");
        lines.push(`Change:`.padEnd(width - change.toFixed(2).length) + change.toFixed(2) + "\n");
        lines.push(`VAT (12%):`.padEnd(width - vatAmount.toFixed(2).length) + vatAmount.toFixed(2) + "\n");
        lines.push(`VATable Sales:`.padEnd(width - vatableSales.toFixed(2).length) + vatableSales.toFixed(2) + "\n");
        lines.push(`VAT Exempt Sales:`.padEnd(width - vatExempt.toFixed(2).length) + vatExempt.toFixed(2) + "\n");
        lines.push(`Zero-rated Sales:`.padEnd(width - zeroRated.toFixed(2).length) + zeroRated.toFixed(2) + "\n");
        lines.push(`Grand Total:`.padEnd(width - totalAfterDiscount.toFixed(2).length) + totalAfterDiscount.toFixed(2) + "\n");

        lines.push("-".repeat(width) + "\n");
        lines.push("This serves as your\n");
        lines.push("Official Receipt\n");
        lines.push("Thank you and enjoy!\n");
        lines.push("\n\n"); // space at the end
        lines.push("-".repeat(width) + "\n");
        lines.push("\n\n"); // space at the end

        // lines.push(cut); // cut paper

        return lines.join("");
    }

    // ================= KITCHEN SLIP =================
    function buildKitchenSlip(order, items) {
        let lines = [];
        lines.push(ESC + "@"); // initialize printer
        lines.push("KITCHEN ORDER SLIP\n");
        lines.push("-----------------------------\n");
        lines.push(`Order #: ${order.order_id}\n`);
        lines.push(`Time: ${new Date().toLocaleString()}\n`);
        lines.push(`Type: ${order.dine || "N/A"}\n`);
        lines.push("-----------------------------\n");
        lines.push("ITEMS:\n");

        items.forEach(item => {
            lines.push(`${item.quantity} × ${item.name}\n`);
        });

        lines.push("-----------------------------\n");
        // lines.push(cut); // cut paper
        return lines.join("");
    }

    // ================= SEND TO PRINTNODE =================
    function sendToPrintNodeRaw(rawText) {
        try {
            loader.show()
            const rawBase64 = btoa(rawText);

            fetch('https://api.printnode.com/printjobs', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Basic ' + btoa('O2zIWVEQaRsITtE8r5EYBSVeCKadKqq0YpxRibkDSP4:'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        printerId: 74971038,
                        title: "Receipt / Kitchen Slip",
                        contentType: "raw_base64",
                        content: rawBase64,
                        source: "SamgyupKaya POS System"
                    })
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Print job sent!", data);
                    if (data && data.error) {
                        console.error("PrintNode error:", data.error);
                        CustomAlert.alert("Error: " + data.error, "error");
                    } else {
                        showAlert("success-alert", "Receipt sent to printer successfully!");
                    }
                })
                .catch(err => {
                    console.error("Error printing:", err);
                    CustomAlert.alert("Failed to send print job. Check console for errors.", "error");
                });
        } catch (error) {
            console.error("Error in sendToPrintNode:", error);
        } finally {
            loader.hide()
        }

    }


    function downloadReceiptAsPDF() {
        try {
            loader.show()

            const element = document.querySelector('.exo-receipt-paper');

            // Get pixel dimensions
            const widthPx = 300; // approx 80mm thermal paper
            const heightPx = element.offsetHeight;

            // Convert px to inches (1 inch = 96 px)
            const pxToInch = px => px / 96;
            const pdfWidth = pxToInch(widthPx); // ~3.125 inches
            const pdfHeight = pxToInch(heightPx); // auto height in inches

            const opt = {
                margin: 0,
                filename: `receipt-${Date.now()}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 1.0
                },
                html2canvas: {
                    scale: 3
                },
                jsPDF: {
                    unit: 'in',
                    format: [pdfWidth, pdfHeight],
                    orientation: 'portrait'
                }
            };

            html2pdf().from(element).set(opt).save();
        } catch (error) {
            console.error("Error downloading receipt as PDF:", error);
        } finally {
            loader.hide()
        }
    }

    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('orders-channel', 'modify-order', () => fetchOrders(currentPage), 200);
</script>

</body>

</html>
