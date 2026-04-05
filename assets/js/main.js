// Auction System - Main JavaScript File

// ====================================
// UTILITY FUNCTIONS
// ====================================

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<strong>${type === 'danger' ? 'Error' : 'Sukses'}!</strong> ${message}`;
    
    const container = document.querySelector('main') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('show');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('show');
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ====================================
// BIDDING SYSTEM
// ====================================

async function placeBid(productId) {
    const bidAmount = document.getElementById(`bidAmount-${productId}`);
    const bidBtn = document.getElementById(`bidBtn-${productId}`);

    if (!bidAmount || !bidAmount.value) {
        showAlert('Masukkan jumlah bid', 'danger');
        return;
    }

    const amount = parseFloat(bidAmount.value);
    if (isNaN(amount) || amount <= 0) {
        showAlert('Jumlah bid tidak valid', 'danger');
        return;
    }

    showLoading();
    bidBtn.disabled = true;

    try {
        const response = await fetch('/api/place-bid.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                bid_amount: amount
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Bid berhasil ditempatkan!', 'success');
            bidAmount.value = '';
            
            // Refresh bid history
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Gagal menempatkan bid', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan', 'danger');
    } finally {
        hideLoading();
        bidBtn.disabled = false;
    }
}

// ====================================
// AUCTION TIMER
// ====================================

function startAuctionTimer(endTime, productId) {
    const timerElement = document.getElementById(`timer-${productId}`);
    if (!timerElement) return;

    function updateTimer() {
        const now = new Date().getTime();
        const end = new Date(endTime).getTime();
        const distance = end - now;

        if (distance < 0) {
            timerElement.innerHTML = '<strong>Lelang Berakhir</strong>';
            timerElement.classList.add('warning');
            
            // Disable bid form
            const bidForm = document.getElementById(`bidForm-${productId}`);
            if (bidForm) {
                bidForm.style.display = 'none';
            }
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (distance < 3600000) {
            timerElement.classList.add('warning');
        }

        timerElement.innerHTML = `
            <div class="timer">
                <div class="timer-item">
                    <span class="timer-value">${days}</span>
                    <div class="timer-label">Hari</div>
                </div>
                <div class="timer-item">
                    <span class="timer-value">${hours}</span>
                    <div class="timer-label">Jam</div>
                </div>
                <div class="timer-item">
                    <span class="timer-value">${minutes}</span>
                    <div class="timer-label">Menit</div>
                </div>
                <div class="timer-item">
                    <span class="timer-value">${seconds}</span>
                    <div class="timer-label">Detik</div>
                </div>
            </div>
        `;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

// Initialize all timers on page load
document.addEventListener('DOMContentLoaded', function() {
    const timerElements = document.querySelectorAll('[id^="timer-"]');
    timerElements.forEach(element => {
        const productId = element.id.replace('timer-', '');
        const endTime = element.getAttribute('data-end-time');
        if (endTime) {
            startAuctionTimer(endTime, productId);
        }
    });
});

// ====================================
// FORM VALIDATION
// ====================================

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            input.style.borderColor = '#e0e0e0';
        }

        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.style.borderColor = '#dc3545';
                isValid = false;
            }
        }

        // Password confirmation
        if (input.id === 'password_confirm') {
            const password = document.getElementById('password');
            if (password && input.value !== password.value) {
                input.style.borderColor = '#dc3545';
                isValid = false;
            }
        }
    });

    return isValid;
}

// Form input listeners
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#e0e0e0';
        });
    });
});

// ====================================
// FILE UPLOAD
// ====================================

function previewImage(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (input && input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function validateImageFile(input) {
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (file) {
        if (!allowedTypes.includes(file.type)) {
            showAlert('Format gambar tidak valid. Gunakan JPG, PNG, atau GIF', 'danger');
            input.value = '';
            return false;
        }

        if (file.size > maxSize) {
            showAlert('Ukuran gambar terlalu besar. Maksimal 5MB', 'danger');
            input.value = '';
            return false;
        }

        return true;
    }

    return false;
}

// ====================================
// PRODUCT MANAGEMENT
// ====================================

async function deleteProduct(productId) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        return;
    }

    showLoading();

    try {
        const response = await fetch('/api/delete-product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId
            })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Produk berhasil dihapus', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Gagal menghapus produk', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan', 'danger');
    } finally {
        hideLoading();
    }
}

// ====================================
// SEARCH & FILTER
// ====================================

let filterTimeout;

function filterProducts() {
    clearTimeout(filterTimeout);
    
    filterTimeout = setTimeout(() => {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');

        if (!searchInput) return;

        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
        if (categoryFilter && categoryFilter.value) params.append('category', categoryFilter.value);

        const url = params.toString() ? `?${params.toString()}` : '/pages/products.php';
        window.location.href = url;
    }, 500);
}

// ====================================
// MODAL FUNCTIONALITY
// ====================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

    // Close modal button
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('show');
        });
    });
});

// ====================================
// KEYBOARD SHORTCUTS
// ====================================

document.addEventListener('keydown', function(e) {
    // ESC to close modal
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            modal.classList.remove('show');
        });
    }
});

// ====================================
// INITIALIZATION
// ====================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips (if needed)
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(el => {
        el.addEventListener('hover', function() {
            console.log('Tooltip:', this.getAttribute('data-tooltip'));
        });
    });

    // Check for notification in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showAlert(decodeURIComponent(urlParams.get('success')), 'success');
    }
    if (urlParams.get('error')) {
        showAlert(decodeURIComponent(urlParams.get('error')), 'danger');
    }
});