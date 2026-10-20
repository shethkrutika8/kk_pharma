<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    exit('Unauthorized access');
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Get order details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    exit('Order not found');
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, m.image_url, m.manufacturer FROM order_items oi LEFT JOIN medicines m ON oi.medicine_id = m.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Display order details
?>
<div class="order-details">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Order Information</h6>
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Order Date:</strong> <?php echo formatDate($order['created_at'], 'd M Y, h:i A'); ?></p>
            <p><strong>Status:</strong> <span class="badge bg-<?php echo getStatusColor($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
            <p><strong>Payment Status:</strong> <span class="badge bg-<?php echo getPaymentStatusColor($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Not specified'); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Delivery Information</h6>
            <?php if ($order['shipping_address']): ?>
                <?php $address = json_decode($order['shipping_address'], true); ?>
                <p><strong>Delivery Address:</strong><br>
                <?php echo htmlspecialchars($address['address_line1'] ?? ''); ?><br>
                <?php if (!empty($address['address_line2'])) echo htmlspecialchars($address['address_line2']) . '<br>'; ?>
                <?php echo htmlspecialchars($address['city'] ?? ''); ?>, <?php echo htmlspecialchars($address['state'] ?? ''); ?><br>
                <?php echo htmlspecialchars($address['postal_code'] ?? ''); ?></p>
            <?php endif; ?>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'Not provided'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'Not provided'); ?></p>
        </div>
    </div>

    <?php if ($order['tracking_number']): ?>
        <div class="alert alert-info">
            <strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?><br>
            <strong>Courier:</strong> <?php echo htmlspecialchars($order['courier_service'] ?? 'Not specified'); ?>
        </div>
    <?php endif; ?>

    <?php if ($order['estimated_delivery']): ?>
        <div class="alert alert-success">
            <strong>Estimated Delivery:</strong> <?php echo formatDate($order['estimated_delivery']); ?>
        </div>
    <?php endif; ?>

    <?php if ($order['order_notes']): ?>
        <div class="alert alert-secondary">
            <strong>Order Notes:</strong><br>
            <?php echo htmlspecialchars($order['order_notes']); ?>
        </div>
    <?php endif; ?>

    <h6 class="mt-3">Order Items</h6>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $subtotal = 0; ?>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['medicine_name']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/40x40/00d1b2/ffffff?text=M" alt="<?php echo htmlspecialchars($item['medicine_name']); ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['medicine_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($item['manufacturer'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($item['price']); ?></td>
                        <td><?php echo formatPrice($item['subtotal']); ?></td>
                    </tr>
                    <?php $subtotal += $item['subtotal']; ?>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Subtotal:</th>
                    <th><?php echo formatPrice($subtotal); ?></th>
                </tr>
                <?php if ($order['discount_amount'] > 0): ?>
                    <tr>
                        <th colspan="3">Discount:</th>
                        <th class="text-success">-<?php echo formatPrice($order['discount_amount']); ?></th>
                    </tr>
                <?php endif; ?>
                <?php if ($order['delivery_charge'] > 0): ?>
                    <tr>
                        <th colspan="3">Delivery Charge:</th>
                        <th><?php echo formatPrice($order['delivery_charge']); ?></th>
                    </tr>
                <?php endif; ?>
                <?php if ($order['tax_amount'] > 0): ?>
                    <tr>
                        <th colspan="3">Tax:</th>
                        <th><?php echo formatPrice($order['tax_amount']); ?></th>
                    </tr>
                <?php endif; ?>
                <tr class="table-primary">
                    <th colspan="3">Total:</th>
                    <th><?php echo formatPrice($order['total_amount']); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($order['cancel_reason']): ?>
        <div class="alert alert-danger mt-3">
            <strong>Cancellation Reason:</strong><br>
            <?php echo htmlspecialchars($order['cancel_reason']); ?>
        </div>
    <?php endif; ?>

    <?php if ($order['return_reason']): ?>
        <div class="alert alert-warning mt-3">
            <strong>Return Reason:</strong><br>
            <?php echo htmlspecialchars($order['return_reason']); ?>
        </div>
    <?php endif; ?>
</div>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'delivered': return 'success';
        case 'shipped': return 'info';
        case 'processing': return 'warning';
        case 'packed': return 'primary';
        case 'confirmed': return 'info';
        case 'cancelled': return 'danger';
        case 'returned': return 'secondary';
        case 'pending': return 'secondary';
        default: return 'secondary';
    }
}

function getPaymentStatusColor($status) {
    switch ($status) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'failed': return 'danger';
        case 'refunded': return 'info';
        default: return 'secondary';
    }
}
?>
