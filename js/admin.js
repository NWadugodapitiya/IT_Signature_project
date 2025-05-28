$(document).ready(function() {
    // Section Navigation
    $('.sidebar-nav a[data-section]').click(function(e) {
        e.preventDefault();
        const targetSection = $(this).data('section');
        
        // Update active state
        $('.sidebar-nav li').removeClass('active');
        $(this).parent().addClass('active');
        
        // Show target section
        $('.section').hide();
        $(`#${targetSection}-section`).show();
    });
    
    // Show products section by default
    $('#products-section').show();
    $('.sidebar-nav a[data-section="products"]').parent().addClass('active');
    
    // View Order Details
    $('.view-order').click(function() {
        const button = $(this);
        const orderId = button.data('id');
        const customerName = button.data('customer');
        const address1 = button.data('address1');
        const address2 = button.data('address2');
        const city = button.data('city');
        const postal = button.data('postal');
        const instructions = button.data('instructions');
        const status = button.data('status');
        
        // Update customer information
        $('#viewOrderModal .customer-name').text(customerName);
        $('#viewOrderModal .customer-address').text(address1 + (address2 ? '\n' + address2 : ''));
        $('#viewOrderModal .customer-city-postal').text(`${city}, ${postal}`);
        $('#viewOrderModal .special-instructions').text(instructions || 'No special instructions');
        
        // Update status badge
        const statusClass = {
            'pending': 'bg-warning',
            'processing': 'bg-info',
            'completed': 'bg-success',
            'cancelled': 'bg-danger'
        }[status] || 'bg-secondary';
        
        $('#viewOrderModal .status-badge').attr('class', `badge ${statusClass}`).text(status.charAt(0).toUpperCase() + status.slice(1));
        
        // Load order items
        $.post('php/order_actions.php', {
            action: 'get_order_items',
            order_id: orderId
        }, function(response) {
            if (response.success) {
                let html = '';
                let total = 0;
                
                response.items.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    
                    html += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>Rs. ${itemTotal.toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                html += `
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>Rs. ${total.toFixed(2)}</strong></td>
                    </tr>
                `;
                
                $('.order-items').html(html);
            } else {
                $('.order-items').html('<tr><td colspan="4" class="text-center">Failed to load order items</td></tr>');
            }
        }, 'json');
        
        $('#viewOrderModal').modal('show');
    });
    
    // Update Order Status
    $('.update-status').click(function() {
        const orderId = $(this).data('id');
        const currentStatus = $(this).data('status');
        
        $('#statusOrderId').val(orderId);
        $('#updateStatusForm select[name="status"]').val(currentStatus);
        $('#updateStatusModal').modal('show');
    });
    
    $('#updateStatusForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true);
        
        $.post('php/order_actions.php', {
            action: 'update_status',
            order_id: $('#statusOrderId').val(),
            status: form.find('select[name="status"]').val()
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
                submitBtn.prop('disabled', false);
            }
        }, 'json');
    });
}); 