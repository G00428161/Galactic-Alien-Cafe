// js/main.js

// Load menu items (HTML from child PHP)
function loadMenuItems() {
    const menuGrid = document.querySelector('.menu-grid');
    if (!menuGrid) return;

    // Optional: show loading message
    menuGrid.innerHTML = '<p>Loading menu items...</p>';

    fetch('get_menu_items.php')
        .then(response => response.text())
        .then(html => {
            menuGrid.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading menu items:', error);
            menuGrid.innerHTML = '<p>Failed to load menu items.</p>';
        });
}

// Load cart summary (JSON from child PHP)
function loadCartSummary() {
    const cartSummaryDiv = document.getElementById('cart-summary-ajax');
    if (!cartSummaryDiv) return;

    cartSummaryDiv.innerHTML = '<p>Loading cart summary...</p>';

    fetch('cart_summary.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // data.html contains HTML from PHP
                // data.total is a number
                const totalHtml =
                    '<p><strong>Total (AJAX): €' +
                    Number(data.total).toFixed(2) +
                    '</strong></p>';

                cartSummaryDiv.innerHTML = data.html + totalHtml;
            } else {
                cartSummaryDiv.innerHTML = '<p>Could not load cart summary.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading cart summary:', error);
            cartSummaryDiv.innerHTML = '<p>Failed to load cart summary.</p>';
        });
}

// Run after page loads
document.addEventListener('DOMContentLoaded', function () {
    loadMenuItems();

    const refreshBtn = document.getElementById('refreshCartBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            loadCartSummary();
        });
    }
});
