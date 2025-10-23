// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
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
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
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
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('.table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
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
    
    // Stock update functionality
    const stockInputs = document.querySelectorAll('.stock-input');
    stockInputs.forEach(input => {
        input.addEventListener('change', function() {
            const prizeId = this.dataset.prizeId;
            const newStock = this.value;
            const isActive = this.closest('tr').querySelector('.active-toggle').checked;
            
            updateStock(prizeId, newStock, isActive);
        });
    });
    
    const activeToggles = document.querySelectorAll('.active-toggle');
    activeToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const prizeId = this.dataset.prizeId;
            const stock = this.closest('tr').querySelector('.stock-input').value;
            const isActive = this.checked;
            
            updateStock(prizeId, stock, isActive);
        });
    });
});

// Logout confirmation
function confirmLogout() {
    const modal = document.getElementById('logoutModal');
    modal.classList.add('show');
}

function closeModal() {
    const modal = document.getElementById('logoutModal');
    modal.classList.remove('show');
}

function performLogout() {
    window.location.href = 'logout.php';
}

// Update stock function
function updateStock(prizeId, stock, isActive) {
    const formData = new FormData();
    formData.append('action', 'update_stock');
    formData.append('prize_id', prizeId);
    formData.append('stock', stock);
    formData.append('is_active', isActive ? '1' : '0');
    
    fetch('manage-stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Cập nhật thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Có lỗi xảy ra khi cập nhật', 'error');
        console.error('Error:', error);
    });
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
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
document.addEventListener('click', function(e) {
    const modal = document.getElementById('logoutModal');
    if (e.target === modal) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
