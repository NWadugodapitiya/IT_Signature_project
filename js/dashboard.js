$(document).ready(function() {
    // Sidebar Toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('shifted');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 992) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
                mainContent.classList.remove('shifted');
            }
        }
    });
    
    // Task Checkboxes
    const taskCheckboxes = document.querySelectorAll('.task-item input[type="checkbox"]');
    taskCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskLabel = this.closest('label');
            if (this.checked) {
                taskLabel.style.textDecoration = 'line-through';
                taskLabel.style.color = '#6c757d';
            } else {
                taskLabel.style.textDecoration = 'none';
                taskLabel.style.color = 'inherit';
            }
        });
    });
    
    // Initialize Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Table Row Actions
    const actionButtons = document.querySelectorAll('.btn-light');
    actionButtons.forEach(button => {
        button.addEventListener('mouseover', function() {
            this.classList.remove('btn-light');
            this.classList.add('btn-primary');
        });
        
        button.addEventListener('mouseout', function() {
            this.classList.remove('btn-primary');
            this.classList.add('btn-light');
        });
    });
    
    // Notification Badge Update
    function updateNotificationBadge() {
        const badge = document.querySelector('.badge.bg-danger');
        if (badge) {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 0) {
                badge.textContent = currentCount - 1;
            }
        }
    }
    
    // Simulate real-time updates for system status
    function updateSystemStatus() {
        const cpuBar = document.querySelector('.progress-bar.bg-primary');
        const memoryBar = document.querySelector('.progress-bar.bg-info');
        
        if (cpuBar && memoryBar) {
            setInterval(() => {
                // Random CPU usage between 20% and 60%
                const cpuUsage = Math.floor(Math.random() * 40) + 20;
                cpuBar.style.width = cpuUsage + '%';
                cpuBar.closest('.system-status-item').querySelector('span:last-child').textContent = cpuUsage + '%';
                
                // Random Memory usage between 30% and 70%
                const memoryUsage = Math.floor(Math.random() * 40) + 30;
                memoryBar.style.width = memoryUsage + '%';
                memoryBar.closest('.system-status-item').querySelector('span:last-child').textContent = memoryUsage + '%';
            }, 5000); // Update every 5 seconds
        }
    }
    
    // Initialize real-time updates
    updateSystemStatus();
    
    // Handle dropdown menu clicks
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.textContent.includes('notification')) {
                e.preventDefault();
                updateNotificationBadge();
            }
        });
    });

    // Product Management
    const addProductForm = document.getElementById('addProductForm');
    const deleteButtons = document.querySelectorAll('.btn-danger');
    const editButtons = document.querySelectorAll('.btn-info');

    // Handle Add Product Form Submission
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Here you would typically send the form data to your backend
            const formData = new FormData(this);
            
            // Simulate success (replace with actual API call)
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();
            
            // Show success message
            showAlert('Product added successfully!', 'success');
            
            // Reset form
            this.reset();
        });
    }

    // Handle Delete Product
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productRow = this.closest('tr');
            const productName = productRow.querySelector('td:nth-child(2)').textContent;
            
            // Show confirmation modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
            deleteModal.show();
            
            // Handle delete confirmation
            const confirmDelete = document.querySelector('#deleteProductModal .btn-danger');
            confirmDelete.onclick = function() {
                // Here you would typically send a delete request to your backend
                
                // Simulate success (replace with actual API call)
                productRow.remove();
                deleteModal.hide();
                
                // Show success message
                showAlert('Product deleted successfully!', 'success');
            };
        });
    });

    // Handle Edit Product
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productRow = this.closest('tr');
            const productName = productRow.querySelector('td:nth-child(2)').textContent;
            const category = productRow.querySelector('td:nth-child(3)').textContent;
            const price = productRow.querySelector('td:nth-child(4)').textContent;
            const stock = productRow.querySelector('td:nth-child(5)').textContent;
            
            // Here you would typically populate and show an edit form modal
            // For now, we'll just show an alert
            showAlert('Edit functionality coming soon!', 'info');
        });
    });

    // Helper function to show alerts
    function showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '1050';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // File Input Preview
    const productImageInput = document.querySelector('input[type="file"]');
    if (productImageInput) {
        productImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Here you could show a preview of the image
                    console.log('Image loaded:', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Cart Management
    let cart = [];
    const cartCountElements = document.querySelectorAll('.cart-count');
    const cartItemsTable = document.querySelector('.cart-items-table');
    const cartTotalElements = document.querySelectorAll('.cart-total');
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));

    // Load cart on page load
    loadCart();
    
    // Add to cart (simplified to always add 1)
    $('.add-to-cart').click(function() {
        const productId = $(this).data('product-id');
        const button = $(this);
        
        button.prop('disabled', true);
        
        $.post('php/cart_actions.php', {
            action: 'add',
            product_id: productId,
            qty: 1  
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success',
                    text: 'Product added to cart',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
                button.prop('disabled', false);
            }
        }, 'json');
    });
    
    // Update cart quantity
    $(document).on('change', '.cart-qty', function() {
        const cartId = $(this).data('cart-id');
        const qty = $(this).val();
        
        $.post('php/cart_actions.php', {
            action: 'update',
            cart_id: cartId,
            qty: qty
        }, function(response) {
            if (response.success) {
                loadCart();
            } else {
                Swal.fire('Error', response.message, 'error');
                loadCart(); 
            }
        }, 'json');
    });
    
    // Remove from cart
    $(document).on('click', '.remove-from-cart', function() {
        const cartId = $(this).data('cart-id');
        
        Swal.fire({
            title: 'Remove Item',
            text: 'Are you sure you want to remove this item from cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('php/cart_actions.php', {
                    action: 'remove',
                    cart_id: cartId
                }, function(response) {
                    if (response.success) {
                        loadCart();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });
    
    $('.cart-trigger').click(function() {
        $('#cartModal').modal('show');
    });
});

// Load cart contents
function loadCart() {
    $.post('php/cart_actions.php', {
        action: 'get_cart'
    }, function(response) {
        if (response.success) {
            let html = '';
            let totalAmount = 0;
            
            response.items.forEach(item => {
                const itemTotal = parseFloat(item.price) * parseInt(item.qty);
                totalAmount += itemTotal;
                
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm cart-qty" 
                                value="${item.qty}" min="1" max="${item.stock}" 
                                style="width: 80px" data-cart-id="${item.cart_id}">
                        </td>
                        <td>Rs. ${itemTotal.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-sm btn-danger remove-from-cart" data-cart-id="${item.cart_id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            $('.cart-items').html(html || '<tr><td colspan="5" class="text-center">Your cart is empty</td></tr>');
            $('.cart-total').text('Rs. ' + totalAmount.toFixed(2));
            $('.cart-count').text(response.items.length);
        }
    }, 'json');
} 