<?php
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';


?>
<link rel="stylesheet" href="css/orderlist.css">

<!-- Font Awesome Free (CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
        <div class="paginated-table">
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
            <div class="pagination-container">
                <button id="prevPage">Previous</button>
                <span id="pageInfo"></span>
                <button id="nextPage">Next</button>
            </div>
        </div>
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
            if (e.target.closest('.view-btn')) {
                const orderId = JSON.parse(e.target.closest('.view-btn').dataset.orderId);
                ShowReceipt(orderId);
            }
        });
    });

    // let previousOrderHistory = [];
    let currentPage = 1;
    let limit = 20; // rows per page
    let totalRows = 0;
    // Fetch orders from the server
    async function fetchOrders(page = 1) {
        currentPage = page
        try {
            const response = await fetch('db_queries/select_queries/fetch_order_history.php', {
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
                // if (!isEqual(previousOrderHistory, data.orders)) {
                //     previousOrderList = data.orders;
                //     displayOrderHistory(data.orders);
                // }
                totalRows = data.totalRows;
                displayOrderHistory(data.orders);
                updatePaginationUI();
            } else {
                CustomAlert.alert('Error fetching order history: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error fetching order history:', error);
        }
    }
    // Deep comparison for object arrays
    function isEqual(arr1, arr2) {
        return JSON.stringify(arr1) === JSON.stringify(arr2);
    }
    // setInterval(fetchOrders, 5000);

    function displayOrderHistory(orderHistory) {
        const orderTableBody = document.getElementById('orderTableBody');
        orderTableBody.innerHTML = ''; // Clear existing rows

        if (orderHistory.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="10">No archived orders found</td>';
            orderTableBody.appendChild(row);
        } else {
            orderHistory.forEach(order => {
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
                                ? `<button class="action-button view-btn" data-order-id="${order.order_id}"><i class="fa-solid fa-eye"></i></button>`
                                : ''}
                        </td>
                    `;
                orderTableBody.appendChild(row);
            });
        }
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
                    CustomAlert.alert(data.error, 'error');
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
                    <img src="Capstone Assets/newLogo.png" alt="Chia's Corner Logo" class="exo-receipt-logo">
                    <h2>SamgyupKaya</h2>
                    <p>Padas street, Cor Langaray St, Caloocan, 1400 Metro Manila</p>
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
                        <div class="row">
                            <span class="left">${item.name}</span>
                            <span class="right">${item.quantity} x ₱${parseFloat(item.price).toFixed(2)}</span>
                        </div>
                `;
                });

                let receiptFooter = `
                <div class="exo-receipt-separator"></div>
                <div class="row bold">
                    <span class="left">Grand Total:</span>
                    <span class="right"><strong>₱${grandTotal.toFixed(2)}</strong></span>
                </div>
                <div class="row">
                    <span class="left">Discount:</span>
                    <span class="right">-₱${discount.toFixed(2)}</span>
                </div>
                <div class="exo-receipt-separator"></div>
                <div class="row">
                    <span class="left">Paid Amount:</span>
                    <span class="right">₱${paidAmount.toFixed(2)}</span>
                </div>
                <div class="row">
                    <span class="left">Change:</span>
                    <span class="right">₱${change.toFixed(2)}</span>
                </div>
                <div class="row">
                    <span class="left">VAT (12%):</span>
                    <span class="right">₱${vatAmount.toFixed(2)}</span>
                </div>
                <div class="row">
                    <span class="left">VATable Sales:</span>
                    <span class="right">₱${vatableSales.toFixed(2)}</span>
                </div>

                <div class="row">
                    <span class="left">VAT Exempt Sales:</span>
                    <span class="right">₱${vatExempt.toFixed(2)}</span>
                </div>
                <div class="row">
                    <span class="left">Zero-rated Sales:</span>
                    <span class="right">₱${zeroRated.toFixed(2)}</span>
                </div>

                <div class="exo-receipt-footer">
                    <span>This serves as your OFFICIAL RECEIPT</span>
                    <span>Thank you and enjoy!</span>
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
    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('orders-channel', 'modify-order', () => fetchOrders(currentPage), 200);
</script>

</body>

</html>
