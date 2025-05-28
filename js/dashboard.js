document.addEventListener('DOMContentLoaded', function() {
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
    const cartItemsContainer = document.querySelector('.cart-items');
    const cartItemsTable = document.querySelector('.cart-items-table');
    const cartTotalElements = document.querySelectorAll('.cart-total');
    const cartButton = document.getElementById('cartButton');
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));

    // Add to Cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const product = {
                id: productCard.dataset.productId,
                name: productCard.querySelector('.card-title').textContent,
                price: parseFloat(productCard.querySelector('.card-text.text-primary').textContent.replace('Rs. ', '')),
                quantity: 1
            };

            addToCart(product);
            updateCartUI();
            showNotification('Product added to cart!');
        });
    });

    // Cart Modal
    if (cartButton) {
        cartButton.addEventListener('click', function(e) {
            e.preventDefault();
            cartModal.show();
        });
    }

    // Update quantity in cart
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quantity-btn')) {
            const row = e.target.closest('tr');
            const productId = row.dataset.productId;
            const change = e.target.classList.contains('plus') ? 1 : -1;
            updateQuantity(productId, change);
        }
    });

    // Remove from cart
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-from-cart')) {
            const row = e.target.closest('tr');
            const productId = row.dataset.productId;
            removeFromCart(productId);
        }
    });

    // Cart Functions
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push(product);
        }
        saveCart();
    }

    function updateQuantity(productId, change) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, item.quantity + change);
            saveCart();
            updateCartUI();
        }
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        saveCart();
        updateCartUI();
        showNotification('Product removed from cart!');
    }

    function updateCartUI() {
        // Update cart count
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountElements.forEach(element => {
            element.textContent = totalItems;
        });

        // Update cart dropdown
        if (cartItemsContainer) {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<div class="text-center text-muted">Your cart is empty</div>';
            } else {
                cartItemsContainer.innerHTML = cart.map(item => `
                    <div class="cart-item mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">${item.name}</h6>
                                <small class="text-muted">Qty: ${item.quantity}</small>
                            </div>
                            <div class="text-end">
                                <div>Rs. ${(item.price * item.quantity).toFixed(2)}</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Update cart modal table
        if (cartItemsTable) {
            cartItemsTable.innerHTML = cart.map(item => `
                <tr data-product-id="${item.id}">
                    <td>${item.name}</td>
                    <td>Rs. ${item.price.toFixed(2)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary quantity-btn minus">-</button>
                            <span class="btn btn-outline-secondary quantity-btn disabled">${item.quantity}</span>
                            <button class="btn btn-outline-secondary quantity-btn plus">+</button>
                        </div>
                    </td>
                    <td>Rs. ${(item.price * item.quantity).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-from-cart">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Update total
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotalElements.forEach(element => {
            element.textContent = `Rs. ${total.toFixed(2)}`;
        });
    }

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            cart = JSON.parse(savedCart);
            updateCartUI();
        }
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-success position-fixed top-0 end-0 m-3';
        notification.style.zIndex = '1050';
        notification.innerHTML = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }

    // Load cart on page load
    loadCart();

    // Search functionality
    const searchInput = document.querySelector('input[placeholder="Search products..."]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productName = card.querySelector('.card-title').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Cart Modal Functionality
    const cartTriggers = document.querySelectorAll('.cart-trigger');
    
    cartTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            cartModal.show();
        });
    });
}); 