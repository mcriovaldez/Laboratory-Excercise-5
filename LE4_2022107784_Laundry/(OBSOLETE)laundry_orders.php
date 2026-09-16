<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
session_start();
require_once 'functions.php';

// role protection
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// holds all orders produced for current session
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

$services = getLaundryServices(); 
$paymentMethods = ["GCash", "Maya", "Bank Transfer", "Cash on Pickup"];
$orderSummary = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $service        = $_POST['service'] ?? '';
    $weight         = floatval($_POST['weight'] ?? 0);
    $instructions   = trim($_POST['instructions'] ?? '');
    $location       = trim($_POST['location'] ?? '');
    $paymentMethod  = $_POST['payment_method'] ?? '';
    $serviceType    = $_POST['service_type'] ?? 'Regular'; // Regular or Express

    // validation
    if (!array_key_exists($service, $services)){$errors[] = "Please select a valid laundry service.";}
    if ($weight <= 0) {$errors[] = "Laundry weight must be greater than 0 kg.";}
    if ($location === '') {$errors[] = "Pickup/Drop-off location is required.";}
    if (!in_array($paymentMethod, $paymentMethods)) {$errors[] = "Please select a valid payment method.";}

    if (empty($errors)) 
    {
        $rate          = $services[$service];
        $serviceCharge = $weight * $rate;
        $discount      = calculateDiscount($serviceCharge);
        $expressFee    = ($serviceType === 'Express') ? 100 : 0;
        $finalAmount   = $serviceCharge - $discount + $expressFee;
        $paymentStatus = getPaymentStatus($paymentMethod);
        $orderId       = generateOrderId($_SESSION['orders']);

        $newOrder = 
        [
            "order_id"       => $orderId,
            "customer"       => $_SESSION['username'],
            "service"        => $service,
            "weight"         => $weight,
            "rate"           => $rate,
            "service_charge" => $serviceCharge,
            "discount"       => $discount,
            "express_fee"    => $expressFee,
            "final_amount"   => $finalAmount,
            "instructions"   => $instructions,
            "location"       => $location,
            "payment_method" => $paymentMethod,
            "payment_status" => $paymentStatus,
            "order_status"   => "Order Received"
        ];

        // add new order to orders list for viewing
        $_SESSION['orders'][] = $newOrder;
        $orderSummary = $newOrder;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Laundry Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>CREATE LAUNDRY ORDER</h1>
            <hr>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

                        <!--ORDER SUMMARY-->
            <?php if ($orderSummary): ?>
                <div class="summary-box">
                    <h2>Laundry Order Summary</h2>
                    <table>
                        <tr><th>Order ID</th><td><?php echo htmlspecialchars($orderSummary['order_id']); ?></td></tr>
                        <tr><th>Customer</th><td><?php echo htmlspecialchars($orderSummary['customer']); ?></td></tr>
                        <tr><th>Service</th><td><?php echo htmlspecialchars($orderSummary['service']); ?></td></tr>
                        <tr><th>Laundry Weight</th><td><?php echo $orderSummary['weight']; ?> kg</td></tr>
                        <tr><th>Rate</th><td><?php echo formatPeso($orderSummary['rate']); ?>/kg</td></tr>
                        <tr><th>Service Charge</th><td><?php echo formatPeso($orderSummary['service_charge']); ?></td></tr>
                        <tr><th>Discount</th><td><?php echo formatPeso($orderSummary['discount']); ?></td></tr>
                        <tr><th>Express Fee</th><td><?php echo formatPeso($orderSummary['express_fee']); ?></td></tr>
                        <tr><th>Final Amount</th><td><strong><?php echo formatPeso($orderSummary['final_amount']); ?></strong></td></tr>
                        <tr><th>Payment Method</th><td><?php echo htmlspecialchars($orderSummary['payment_method']); ?></td></tr>
                        <tr><th>Payment Status</th><td><?php echo htmlspecialchars($orderSummary['payment_status']); ?></td></tr>
                        <tr><th>Order Status</th><td><?php echo htmlspecialchars($orderSummary['order_status']); ?></td></tr>
                    </table>
                    <br>
                    <a class="btn btn-primary" href="laundry_order.php">Create Another Order</a>
                    <a class="btn" href="my_orders.php">View My Orders</a>
                </div>
            <?php else: ?>

            <!--LAUNDRY FORM-->    
            <form method="POST" action="laundry_order.php">
                <label>Customer Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled>

                <label for="service">Laundry Service:</label>
                <select id="service" name="service" required>
                    <option value="">-- Select Service --</option>
                    <?php foreach ($services as $serviceName => $rate): ?>
                        <option value="<?php echo htmlspecialchars($serviceName); ?>">
                            <?php echo htmlspecialchars($serviceName); ?> (₱<?php echo $rate; ?>/kg)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="weight">Laundry Weight (kg):</label>
                <input type="number" id="weight" name="weight" min="0.1" step="0.1" required>

                <label for="instructions">Special Instructions:</label>
                <textarea id="instructions" name="instructions" rows="3"></textarea>

                <label for="location">Pickup/Drop-off Location:</label>
                <input type="text" id="location" name="location" required>

                <label>Service Option:</label>
                <div class="radio-group">
                    <label><input type="radio" name="service_type" value="Regular" checked> Regular Service</label>
                    <label><input type="radio" name="service_type" value="Express"> Express Service (+₱100)</label>
                </div>

                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">-- Select Payment Method --</option>
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?php echo htmlspecialchars($method); ?>"><?php echo htmlspecialchars($method); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary">Submit Order</button>
            </form>
            <?php endif; ?>

            <hr>
            <a class="btn" href="customer.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
