import "./bootstrap";
// Global JavaScript functions

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format currency
function formatCurrency(amount) {
    return "৳" + formatNumber(parseFloat(amount).toFixed(2));
}

// Show loading spinner
function showLoading(elementId) {
    document.getElementById(elementId).innerHTML =
        '<div class="loading"></div>';
}

// Hide element
function hideElement(elementId) {
    document.getElementById(elementId).style.display = "none";
}

// Show element
function showElement(elementId) {
    document.getElementById(elementId).style.display = "block";
}

// CSRF Token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Set default headers for fetch
window.fetchWithCsrf = (url, options = {}) => {
    return fetch(url, {
        ...options,
        headers: {
            ...options.headers,
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
        },
    });
};

// Auto-hide alerts after 5 seconds
document.addEventListener("DOMContentLoaded", function () {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Confirm before delete actions
function confirmDelete(message = "Are you sure you want to delete this?") {
    return confirm(message);
}

// Handle table row clicks (if needed)
document.addEventListener("DOMContentLoaded", function () {
    const tableRows = document.querySelectorAll("tr[data-href]");
    tableRows.forEach((row) => {
        row.addEventListener("click", function () {
            window.location.href = this.dataset.href;
        });
        row.style.cursor = "pointer";
    });
});
