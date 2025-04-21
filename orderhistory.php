<?php
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';


?>
<link rel="stylesheet" href="css/orderlist.css">

<div class="wrapper">
    <!-- Sort & Search Bar -->
    <div class="container-search">
        <div class="search-container">
            <select id="sortSelect">
                <option value="0">Sort by Order ID</option>
                <option value="3">Sort by Total Price</option>
                <option value="5">Sort by Payment Method (Cash/Gcash)</option>
                <option value="6">Sort by Date & Time</option>
            </select>
            <input type="text" id="search" placeholder="Search..." onkeyup="filterTable()">
        </div>
    </div>

    <!-- Orders Table -->

    <div class="table-container">
        <table id="orderTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Items Ordered</th>
                    <th>Total Price</th>
                    <th>Discount Amount</th>
                    <th>Paid Amount</th>
                    <th>Change Given</th>
                    <th>Payment Status</th>
                    <th>Cashier</th>
                    <th>Archived Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <!-- Orders will be loaded dynamically by JS -->

            </tbody>
        </table>
    </div>
</div>


<!-- Receipt Modal for Checkout -->

<!-- <div id="exo-checkout-overlay" class="exo-modal-overlay"></div> -->

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
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchOrders();

        document.querySelector('#orderTable tbody').addEventListener('click', function(e) {
            if (e.target.classList.contains('view-btn')) {
                const orderId = e.target.getAttribute('data-order-id');
                ShowReceipt(orderId);
            }
        });
    });

    // Fetch orders from the server
    function fetchOrders() {
        fetch('db_queries/select_queries/fetch_order_history.php')
            .then(response => response.json())
            .then(data => {
                const orderTableBody = document.getElementById('orderTableBody');
                orderTableBody.innerHTML = ''; // Clear existing rows

                if (data.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="10">No archived orders found</td>';
                    orderTableBody.appendChild(row);
                } else {
                    data.forEach(order => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${order.order_id}</td>
                        <td class="order-list">${order.items_ordered}</td>
                        <td>₱${parseFloat(order.total_price).toFixed(2)}</td>
                        <td>₱${parseFloat(order.discount_amount).toFixed(2)}</td>
                        <td>₱${parseFloat(order.paid_amount).toFixed(2)}</td>
                        <td>₱${parseFloat(order.change_given).toFixed(2)}</td>
                        <td>${order.payment_status}</td>
                        <td>${order.cashier || 'N/A'}</td>
                        <td>${new Date(order.archived_date).toLocaleString()}</td>
                        <td>
                            ${order.payment_status.toLowerCase() === 'paid' 
                                ? `<button class="action-button view-btn" data-order-id="${order.order_id}">View Receipt</button>` 
                                : ''}
                        </td>
                    `;
                        orderTableBody.appendChild(row);
                    });
                }
            })
            .catch(error => console.error('Error fetching orders:', error));
    }

    function ShowReceipt(orderId) {

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


    // Sorting function
    function sortTable(columnIndex) {
        let table = document.getElementById("orderTable");
        let rows = Array.from(table.rows).slice(1); // Exclude header row
        let isNumeric = columnIndex === 0 || columnIndex === 3 || columnIndex === 6; // Order ID, Total Price, Date & Time
        let isAlphabetic = columnIndex === 5; // Payment Method column

        let sortedRows = rows.sort((a, b) => {
            let valA = a.cells[columnIndex].innerText.trim();
            let valB = b.cells[columnIndex].innerText.trim();

            if (isNumeric) {
                valA = parseFloat(valA.replace('$', '')) || 0;
                valB = parseFloat(valB.replace('$', '')) || 0;
                return valA - valB;
            } else if (isAlphabetic) {
                return valA.localeCompare(valB);
            }
            return valA > valB ? 1 : -1;
        });

        let sortedAsc = table.getAttribute("data-sort") === "asc";
        if (sortedAsc) sortedRows.reverse();
        table.setAttribute("data-sort", sortedAsc ? "desc" : "asc");

        for (let row of sortedRows) {
            table.tBodies[0].appendChild(row);
        }
    }


    // Dropdown Sort
    document.getElementById("sortSelect").addEventListener("change", function() {
        sortTable(parseInt(this.value));
    });

    // Search Filter
    function filterTable() {
        let searchInput = document.getElementById("search").value.toLowerCase();
        let rows = document.querySelectorAll("#orderTable tbody tr");

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(searchInput) ? "" : "none";
        });
    }
</script>

</body>

</html>
