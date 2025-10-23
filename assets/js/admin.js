// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Debug: Log all clicks
    document.addEventListener('click', function(e) {
        console.log('Click detected on:', e.target);
        console.log('Click target classes:', e.target.className);
        console.log('Click target tag:', e.target.tagName);
    });
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.body.classList.toggle('dark-theme');
            const icon = this.querySelector('i');
            if (document.body.classList.contains('dark-theme')) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        });
    }

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                showAlert('Vui lòng điền đầy đủ thông tin bắt buộc', 'error');
            }
        });
    });

    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach((input) => {
        input.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('.table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });

    // Initialize all save buttons as disabled
    const saveButtons = document.querySelectorAll('.save-stock-btn');
    saveButtons.forEach((button) => {
        button.disabled = true;
        button.classList.add('btn-secondary');
        button.innerHTML = '<i class="fas fa-save"></i> Lưu';
    });

    // Save button functionality - only save when button is clicked
    saveButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const prizeId = row.querySelector('.stock-input').dataset.prizeId;
            const stock = row.querySelector('.stock-input').value;
            const isActive = row.querySelector('.active-toggle').checked;

            // Validate stock input
            if (stock < 0) {
                showAlert('Số lượng stock không thể âm', 'error');
                return;
            }

            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            this.disabled = true;

            updateStock(prizeId, stock, isActive, this);
        });
    });

    // Optional: Add visual feedback for unsaved changes
    const stockInputs = document.querySelectorAll('.stock-input');
    const activeToggles = document.querySelectorAll('.active-toggle');

    // Add change tracking for visual feedback
    [...stockInputs, ...activeToggles].forEach((element) => {
        element.addEventListener('change', function () {
            const row = this.closest('tr');
            const saveBtn = row.querySelector('.save-stock-btn');

            // Check if save button exists before manipulating it
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('btn-secondary');
                saveBtn.classList.add('btn-primary');
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Lưu';
            }
        });
    });
});

// Logout confirmation
function confirmLogout() {
    console.log('confirmLogout called');
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        console.error('logoutModal not found');
    }
}

function closeModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function performLogout() {
    window.location.href = 'logout.php';
}

// Update stock function
function updateStock(prizeId, stock, isActive, saveButton = null) {
    const formData = new FormData();
    formData.append('action', 'update_stock');
    formData.append('prize_id', prizeId);
    formData.append('stock', stock);
    formData.append('is_active', isActive ? '1' : '0');

    fetch('manage-stock.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get as text first
        })
        .then((text) => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    showAlert('Cập nhật thành công!', 'success');

                    // Reset button state on success
                    if (saveButton) {
                        saveButton.innerHTML = '<i class="fas fa-save"></i> Lưu';
                        saveButton.disabled = true;
                        saveButton.classList.remove('btn-primary', 'btn-success');
                        saveButton.classList.add('btn-secondary');
                    }
                } else {
                    showAlert('Có lỗi xảy ra: ' + data.message, 'error');

                    // Reset button state on error
                    if (saveButton) {
                        saveButton.innerHTML = '<i class="fas fa-save"></i> Lưu';
                        saveButton.disabled = false;
                        saveButton.classList.remove('btn-secondary');
                        saveButton.classList.add('btn-primary');
                    }
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                showAlert('Server trả về dữ liệu không hợp lệ', 'error');

                // Reset button state on error
                if (saveButton) {
                    saveButton.innerHTML = '<i class="fas fa-save"></i> Lưu';
                    saveButton.disabled = false;
                    saveButton.classList.remove('btn-secondary');
                    saveButton.classList.add('btn-primary');
                }
            }
        })
        .catch((error) => {
            console.error('Fetch error:', error);
            showAlert('Có lỗi xảy ra khi cập nhật: ' + error.message, 'error');

            // Reset button state on error
            if (saveButton) {
                saveButton.innerHTML = '<i class="fas fa-save"></i> Lưu';
                saveButton.disabled = false;
                saveButton.classList.remove('btn-secondary');
                saveButton.classList.add('btn-primary');
            }
        });
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${
            type === 'success'
                ? 'check-circle'
                : type === 'error'
                ? 'exclamation-circle'
                : 'info-circle'
        }"></i>
        ${message}
    `;

    // Add alert styles if not exists
    if (!document.querySelector('#alert-styles')) {
        const style = document.createElement('style');
        style.id = 'alert-styles';
        style.textContent = `
            .alert {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 3000;
                display: flex;
                align-items: center;
                gap: 8px;
                opacity: 1;
                transition: opacity 0.3s ease;
            }
            .alert-success { background: #28a745; }
            .alert-error { background: #dc3545; }
            .alert-info { background: #17a2b8; }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    }, 3000);
}

// Export CSV function
function exportCSV(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'export_type';
    input.value = type;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Close modal when clicking outside
document.addEventListener('click', function (e) {
    const modal = document.getElementById('logoutModal');
    if (e.target === modal) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
