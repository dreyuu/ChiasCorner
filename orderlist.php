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
                    body: "order_id=" + encodeURIComponent(orderIdToVoid)
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
                } else {
                    CustomAlert.alert(data.error, 'error');
                }
            })
            .catch(error => console.error("Error processing checkout:", error))
            .finally(() => {
                loader.hide()
            });
    }



    function fetchReceipt(orderId) {
        fetch(`db_queries/select_queries/fetch_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // alert(data.error);
                    showAlert('error-alert', data.error)
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
                    <p><strong>OR Number:</strong> ${order.order_id}</p>
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
                // <div class="item-details">
                //     <p>VAT (12%):</p>
                //     <span class="item-total">₱${vatAmount.toFixed(2)}</span>
                // </div>
                // <div class="item-details">
                //     <p>VATable Sales:</p>
                //     <span class="item-total">₱${vatableSales.toFixed(2)}</span>
                // </div>

                // <div class="item-details">
                //     <p>VAT Exempt Sales:</p>
                //     <span class="item-total">₱${vatExempt.toFixed(2)}</span>
                // </div>
                // <div class="item-details">
                //     <p>Zero-rated Sales:</p>
                //     <span class="item-total">₱${zeroRated.toFixed(2)}</span>
                // </div>
                receiptContainer.innerHTML = receiptHeader + receiptBody + receiptFooter;
                receiptList.appendChild(receiptContainer);
                printKitchenSlip(data);
            })
            .catch(error => console.error("Error loading order details:", error));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const receiptPaper = document.querySelector('.exo-receipt-paper');
        const printReceipts = document.querySelector('.print-receipt');
        const downloadReceipts = document.querySelector('.download-receipt');

        async function printBoth() {
            let receiptContent = document.querySelector('.exo-receipt-paper').outerHTML;
            let kitchenSlipContent = document.querySelector('.kitchen-slip').outerHTML;

            await convertHtmlToPdfAndSendToPrintNode(receiptContent);
            await new Promise(resolve => setTimeout(resolve, 5000));
            await convertHtmlToPdfAndSendToPrintNode(kitchenSlipContent);
        }
        printReceipts.addEventListener('click', function(e) {
            e.preventDefault();
            printBoth();
            // let receiptContent = document.querySelector('.exo-receipt-paper').outerHTML;

            // // Detect if the device is mobile
            // if (isMobileDevice()) {
            //     // console.log("Mobile device detected. Sending to PrintNode.");
            //     convertHtmlToPdfAndSendToPrintNode(receiptContent);
            // } else {
            //     // console.log("Desktop device detected. Printing directly.");
            //     // printDirectly(receiptContent);
            //     convertHtmlToPdfAndSendToPrintNode(receiptContent);
            // }
        });

        downloadReceipts.addEventListener('click', function(e) {
            e.preventDefault();
            downloadReceiptAsPDF();
        });
    });

    // Function to detect mobile device
    function isMobileDevice() {
        return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    }


    function printKitchenSlip(orderData) {
        let div = document.createElement("div");
        div.classList.add("kitchen-slip");

        div.style.padding = "10px";
        div.style.fontFamily = "Arial, sans-serif";
        div.style.width = "100%";
        div.style.fontSize = "14px";

        div.innerHTML = `
        <h2 style="text-align:center; margin-bottom: 10px; font-size: 18px;">
            KITCHEN ORDER SLIP
        </h2>
        <p><strong>Order #:</strong> ${orderData.order.order_id}</p>
        <p><strong>Time:</strong> ${new Date().toLocaleString()}</p>
        <p><strong>Type:</strong> ${orderData.order.dine}</p>
        <hr style="margin: 10px 0;">
        <p><strong>ITEMS:</strong></p>
    `;

        orderData.items.forEach(item => {
            div.innerHTML += `
            <p style="font-size: 16px; margin: 5px 0;">
                <strong>${item.quantity} × ${item.name}</strong>
            </p>
        `;
        });
        document.querySelector(".kitchen-slip").appendChild(div);
    }

    // Function to convert HTML to PDF and send to PrintNode (Mobile)
    async function convertHtmlToPdfAndSendToPrintNode(htmlContent) {
        const {
            jsPDF
        } = window.jspdf;

        // Create a temporary container
        let temp = document.createElement("div");
        temp.style.position = "fixed";
        temp.style.left = "-9999px";
        temp.innerHTML = htmlContent;
        document.body.appendChild(temp);

        // Measure the content
        const heightPx = temp.scrollHeight;
        const heightMM = heightPx * 0.264583;

        const doc = new jsPDF({
            orientation: "portrait",
            unit: "mm",
            format: [80, heightMM]
        });

        await html2canvas(temp, {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            doc.addImage(imgData, "PNG", 0, 0, 80, heightMM);

            const pdfBase64 = doc.output("datauristring").split(",")[1];
            sendToPrintNode(pdfBase64);
        });

        document.body.removeChild(temp); // cleanup
    }



    // Function to print directly (Desktop)
    function printDirectly(receiptContent) {
        // Detect if the printer is 58mm (default for your Xprinter)
        const is58mm = true; // Set this to true because we are using a 58mm thermal printer

        let printStyles = `
                <style>
                    @media print {
                        body {
                            margin: 0;
                            width: ${is58mm ? '58mm' : '80mm'};
                            font-family: "Courier New", monospace;
                            padding: 0;
                        }

                        .exo-receipt-paper {
                            width: ${is58mm ? '58mm' : '80mm'};
                            padding: 5px;
                            box-sizing: border-box;
                            font-size: ${is58mm ? '12px' : '14px'};
                        }

                        .exo-receipt-header,
                        .exo-receipt-footer {
                            text-align: center;
                        }

                        .exo-receipt-header img,
                        .exo-receipt-logo {
                            width: ${is58mm ? '40px' : '60px'};
                            height: auto;
                        }

                        .exo-receipt-header h2 {
                            margin: 0;
                            font-size: ${is58mm ? '14px' : '16px'};
                        }

                        .exo-receipt-header p,
                        .exo-receipt-body p,
                        .exo-receipt-footer p {
                            font-size: ${is58mm ? '12px' : '14px'};
                            margin: 0;
                        }

                        .exo-receipt-separator {
                            border-top: 1px dashed #000;
                            margin: 5px 0;
                        }

                        .item-details {
                            display: flex;
                            justify-content: space-between;
                            font-size: ${is58mm ? '12px' : '14px'};
                        }

                        .item-name {
                            font-size: ${is58mm ? '12px' : '14px'};
                            font-weight: bold;
                        }

                        .item-total {
                            font-weight: bold;
                        }

                        .exo-receipt-total {
                            font-size: ${is58mm ? '14px' : '16px'};
                            font-weight: bold;
                        }
                    }

                    @page {
                        size: ${is58mm ? '58mm' : '80mm'} auto;
                        margin: 0;
                    }
                </style>
            `;

        let printWindow = window.open('', '', 'width=320,height=800');
        printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Receipt</title>
                        ${printStyles}
                    </head>
                    <body>
                        ${receiptContent}
                    </body>
                </html>
            `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }


    // Function to send PDF to PrintNode (Mobile)
    function sendToPrintNode(pdfBase64) {
        try {
            loader.show()

            fetch('https://api.printnode.com/printjobs', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Basic ' + btoa('O2zIWVEQaRsITtE8r5EYBSVeCKadKqq0YpxRibkDSP4:'), // Replace with your actual API key
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        printerId: 74971038, // Replace with your actual printer ID
                        title: "Customer Receipt",
                        contentType: "pdf_base64", // Correct content type
                        content: pdfBase64,
                        source: "Chia's POS"
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
