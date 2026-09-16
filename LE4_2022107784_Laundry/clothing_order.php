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

$sizes = getLaundrysizes(); 
$paymentMethods = ["GCash", "Maya", "Bank Transfer", "Cash on Pickup"];
$orderSummary = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $clothingType   = $_POST['clothing_type'] ?? ''; 
    $size           = $_POST['size'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $instructions   = trim($_POST['instructions'] ?? '');
    $phone          = $_POST['phone'] ?? '';
    $location       = trim($_POST['location'] ?? '');
    $date           = $_POST['date'] ?? '';
    $paymentMethod  = $_POST['payment_method'] ?? '';
    
    // validation
    if (!array_key_exists($size, $sizes)){$errors[] = "Please select a size.";}
    if ($email === '') {$errors[] = "Valid Email required.";}
    if ($location === '') {$errors[] = "Pickup/Drop-off location is required.";}
    if (!in_array($paymentMethod, $paymentMethods)) {$errors[] = "Please select a valid payment method.";}

    if (empty($errors)) 
    {
        $rate          = $sizes[$size];
        $serviceCharge = $rate;
        $discount      = calculateDiscount($serviceCharge);
        $finalAmount   = $serviceCharge - $discount;
        $paymentStatus = getPaymentStatus($paymentMethod);
        $orderId       = generateOrderId($_SESSION['orders']);

        $newOrder = 
        [
            "order_id"       => $orderId,
            "customer"       => $_SESSION['username'],
            "size"           => $size,
            "rate"           => $rate,
            "service_charge" => $serviceCharge,
            "discount"       => $discount,
            "final_amount"   => $finalAmount,
            "instructions"   => $instructions,
            "location"       => $location,
            "payment_method" => $paymentMethod,
            "payment_status" => $paymentStatus,
            "order_status"   => "Order Received",

            "clothing_type"  => $clothingType,
            "email"          => $email,
            "phone"          => $phone,
            "date"    => $date
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
    <title>Create Clothing Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>CREATE CLOTHING ORDER</h1>
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
                        <tr><th>Clothing Type</th><td><?php echo htmlspecialchars($orderSummary['clothing_type']); ?></td></tr>
                        <tr><th>Size</th><td><?php echo htmlspecialchars($orderSummary['size']); ?></td></tr>
                        <tr><th>Email</th><td><?php echo htmlspecialchars($orderSummary['email']); ?></td></tr>
                        <tr><th>Special Instructions</th><td><?php echo htmlspecialchars($orderSummary['instructions']); ?></td></tr>
                        <tr><th>Phone Number</th><td><?php echo htmlspecialchars($orderSummary['phone'])?></td></tr>
                        <tr><th>Location</th><td><?php echo htmlspecialchars($orderSummary['location'])?></td></tr>
                        <tr><th>Date</th><td><?php echo htmlspecialchars($orderSummary['date'])?></td></tr>
                        <tr><th>Discount</th><td><?php echo formatPeso($orderSummary['discount']); ?></td></tr>
                        <!--<tr><th>Express Fee</th><td><?php echo formatPeso($orderSummary['express_fee']); ?></td></tr>-->
                        <tr><th>Final Amount</th><td><strong><?php echo formatPeso($orderSummary['final_amount']); ?></strong></td></tr>
                        <tr><th>Payment Method</th><td><?php echo htmlspecialchars($orderSummary['payment_method']); ?></td></tr>
                        <tr><th>Payment Status</th><td><?php echo htmlspecialchars($orderSummary['payment_status']); ?></td></tr>
                        <tr><th>Order Status</th><td><?php echo htmlspecialchars($orderSummary['order_status']); ?></td></tr>
                    </table>
                    <br>
                    <a class="btn btn-primary" href="clothing_order.php">Create Another Order</a>
                    <a class="btn" href="my_orders.php">View My Orders</a>
                </div>
            <?php else: ?>

            <!--Clothing FORM-->    
            <form method="POST" action="clothing_order.php">
                <label>Customer Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled>

                <label>Clothing Option:</label>
                <div class="radio-group">
                    <label><input type="radio" name="clothing_type" value="Headwear">HeadWear</label>
                    <label><input type="radio" name="clothing_type" value="BodyWear">Bodywear</label>
                    <label><input type="radio" name="clothing_type" value="Legwear">Legwear</label>
                </div>


                <label for="size">Laundry Size:</label>
                <select id="size" name="size" required>
                    <option value="">-- Select size --</option>
                    <?php foreach ($sizes as $sizeName => $rate): ?>
                        <option value="<?php echo htmlspecialchars($sizeName); ?>">
                            <?php echo htmlspecialchars($sizeName); ?> (₱<?php echo $rate; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>

                <label for="instructions">Special Instructions:</label>
                <textarea id="instructions" name="instructions" rows="3"></textarea>

                <label for="phone">Enter your phone number:</label>
                <input  type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="09123456789" 
                        pattern="^09[0-9]{9}$" 
                        maxlength="11" 
                        inputmode="numeric" 
                        required>

                <label for="location">Pickup/Drop-off Location:</label>
                <input type="text" id="location" name="location" required>

                <label for="date">Expected Delivery Date:</label>
                <input type="date" id="date" name="date">
                <script>
                  const today = new Date().toISOString().split('T')[0];
                  document.getElementById('date').setAttribute('min', today);
                </script>


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
