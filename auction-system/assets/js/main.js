/**
 * Main JavaScript - Auction System
 * Fungsi-fungsi umum, AJAX, dan timer
 */

// ==================== GLOBAL VARIABLES ====================
const APP_URL = window.location.origin + '/auction-system';
const timers = {};

// ==================== ALERT/MESSAGE HANDLING ====================

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alerts-container');
    if (!alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type}">
            <span class="alert-close" onclick="document.getElementById('${alertId}').remove();">&times;</span>
            ${message}
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto hide setelah 5 detik
    if (type !== 'error') {
        setTimeout(() => {
            const el = document.getElementById(alertId);
            if (el) el.remove();
        }, 5000);
    }
}

/**
 * Hide loading spinner
 */
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="loading-spinner"></div>';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading(element) {
    if (element) {
        element.innerHTML = '';
    }
}

// ==================== AJAX HELPERS ====================

/**
 * Send AJAX request
 */
function sendAjax(method, url, data = null) {
    return new Promise((resolve, reject) => {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => resolve(data))
            .catch(error => {
                console.error('Error:', error);
                reject(error);
            });
    });
}

// ==================== BID FUNCTIONALITY ====================

/**
 * Place bid dengan AJAX
 */
async function placeBid(productId, bidAmount) {
    const btn = document.querySelector('[data-product-id="' + productId + '"]');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner"></span> Memproses...';
    }

    try {
        const response = await sendAjax('POST', APP_URL + '/api/place-bid.php', {
            product_id: productId,
            bid_amount: bidAmount
        });

        if (response.success) {
            showAlert('success', 'Penawaran Anda berhasil diajukan!');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('error', response.message || 'Gagal mengajukan penawaran');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Ajukan Penawaran';
            }
        }
    } catch (error) {
        showAlert('error', 'Terjadi kesalahan: ' + error.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Ajukan Penawaran';
        }
    }
}

// ==================== COUNTDOWN TIMER ====================

/**
 * Format detik ke format readable
 */
function formatTime(seconds) {
    if (seconds <= 0) {
        return 'Waktu habis';
    }

    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    let result = '';
    if (days > 0) result += days + 'h ';
    if (hours > 0) result += hours + 'j ';
    if (minutes > 0) result += minutes + 'm ';
    result += secs + 'd';

    return result;
}

/**
 * Update countdown timer
 */
function updateCountdown(productId, endTime) {
    const timerElement = document.getElementById('timer-' + productId);
    if (!timerElement) return;

    function tick() {
        const now = new Date().getTime();
        const end = new Date(endTime).getTime();
        const remaining = Math.floor((end - now) / 1000);

        if (remaining <= 0) {
            timerElement.textContent = 'Lelang Berakhir';
            timerElement.className = 'timer-expired';
            disableBidForm(productId);
            clearInterval(timers[productId]);
            return;
        }

        timerElement.textContent = formatTime(remaining);

        // Change color based on remaining time
        if (remaining < 3600) { // Less than 1 hour
            timerElement.className = 'timer-expiring';
        } else {
            timerElement.className = 'timer-active';
        }
    }

    tick();
    timers[productId] = setInterval(tick, 1000);
}

/**
 * Disable bid form
 */
function disableBidForm(productId) {
    const form = document.getElementById('bid-form-' + productId);
    if (form) {
        const inputs = form.querySelectorAll('input, button');
        inputs.forEach(input => {
            input.disabled = true;
        });
    }
}

// ==================== FORM VALIDATION ====================

/**
 * Validasi form bid
 */
function validateBidForm(productId) {
    const bidInput = document.getElementById('bid-amount-' + productId);
    const currentPrice = parseFloat(document.getElementById('current-price-' + productId).value);

    if (!bidInput) return false;

    const bidAmount = parseFloat(bidInput.value);

    if (isNaN(bidAmount) || bidAmount <= 0) {
        showAlert('error', 'Jumlah penawaran harus angka positif');
        return false;
    }

    if (bidAmount <= currentPrice) {
        showAlert('error', 'Penawaran harus lebih tinggi dari Rp ' + formatCurrency(currentPrice));
        return false;
    }

    return true;
}

/**
 * Format currency untuk display
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

// ==================== FORM SUBMISSION ====================

/**
 * Handle form submit untuk delete dengan confirm
 */
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus?') {
    return confirm(message);
}

/**
 * Clear form
 */
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

// ==================== FILE UPLOAD ====================

/**
 * Preview gambar sebelum upload
 */
function previewImage(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 100%; max-height: 300px;">';
            };
            reader.readAsDataURL(file);
        }
    });
}

// ==================== SEARCH & FILTER ====================

/**
 * Handle search form
 */
function handleSearch(event) {
    if (event) {
        event.preventDefault();
    }

    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');

    if (searchInput && statusFilter) {
        const search = searchInput.value;
        const status = statusFilter.value;

        const url = new URL(window.location.href);
        url.searchParams.set('search', search);
        url.searchParams.set('status', status);

        window.location.href = url.toString();
    }
}

// ==================== MODAL ====================

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

/**
 * Close modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Close modal when clicking outside
 */
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
});

// ==================== INITIALIZATION ====================

/**
 * Initialize timers on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all countdown timers
    const timerElements = document.querySelectorAll('[data-timer]');
    timerElements.forEach(element => {
        const productId = element.dataset.productId;
        const endTime = element.dataset.endTime;
        if (productId && endTime) {
            updateCountdown(productId, endTime);
        }
    });

    // Handle bid form submission
    const bidForms = document.querySelectorAll('[data-bid-form]');
    bidForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const bidInput = document.getElementById('bid-amount-' + productId);

            if (validateBidForm(productId)) {
                placeBid(productId, parseFloat(bidInput.value));
            }
        });
    });

    // Initialize image preview
    const imageInputs = document.querySelectorAll('[data-preview]');
    imageInputs.forEach(input => {
        const previewId = input.dataset.preview;
        previewImage(input.id, previewId);
    });
});

// ==================== SIDEBAR TOGGLE ====================

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}