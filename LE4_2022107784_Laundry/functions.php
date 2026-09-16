<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
//functions for laundry processing

// stops the browser from showing a cached copy of a protected page
// (e.g. via the Back/Forward button) after the user has logged out
function preventCaching() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
}

// ---------- ORDERS (read/write data/orders.xml) ----------
// Orders live here instead of $_SESSION so logout can fully destroy
// the session without deleting anyone's orders.

define('ORDERS_XML', __DIR__ . '/data/orders.xml');

// fields that need to come back as numbers instead of strings
define('ORDER_NUMERIC_FIELDS', ['rate', 'service_charge', 'discount', 'final_amount']);

// reads every order stored in orders.xml
function loadOrders() {
    $orders = [];

    if (!file_exists(ORDERS_XML)) {return $orders;}

    $xml = simplexml_load_file(ORDERS_XML);
    if ($xml === false) {return $orders;}

    foreach ($xml->order as $orderNode) {
        $order = [];
        foreach ($orderNode->children() as $field => $value) {
            $order[$field] = in_array($field, ORDER_NUMERIC_FIELDS) ? (float)$value : (string)$value;
        }
        $orders[] = $order;
    }
    return $orders;
}

// rewrites orders.xml with the full given list of orders
function saveOrders($orders) {
    $xml = new SimpleXMLElement('<orders/>');

    foreach ($orders as $order) {
        $orderNode = $xml->addChild('order');
        foreach ($order as $field => $value) {
            // addChild() escapes special characters automatically
            $orderNode->addChild($field, (string)$value);
        }
    }

    file_put_contents(ORDERS_XML, $xml->asXML(), LOCK_EX);
}

// appends one new order to orders.xml
function addOrder($newOrder) {
    $orders   = loadOrders();
    $orders[] = $newOrder;
    saveOrders($orders);
}

// updates the order_status field of one order inside orders.xml
function updateOrderStatus($orderId, $newStatus) {
    $orders = loadOrders();
    $found  = false;

    foreach ($orders as &$order) {
        if ($order['order_id'] === $orderId) {
            $order['order_status'] = $newStatus;
            $found = true;
            break;
        }
    }
    unset($order);

    if ($found) {saveOrders($orders);}
    return $found;
}

// returns laundry price
function getLaundrysizes() {
    return [
        "XXL"  => 60,
        "XL"   => 50,
        "L"    => 40,
        "M"    => 35,
        "S"    => 30,
        "XS"   => 25,
        "XXS"  => 20
    ];
}

// payment statud
function getPaymentStatus($paymentMethod) {
    $fullyPaidMethods = ["GCash", "Maya", "Bank Transfer"];

    if (in_array($paymentMethod, $fullyPaidMethods)) {return "Fully Paid";}
    else return "Unpaid";
}

// service charge
function calculateDiscount($serviceCharge) {
    if ($serviceCharge >= 1500) {return $serviceCharge * 0.10;} 
    elseif ($serviceCharge >= 1000) {return $serviceCharge * 0.05;}
    return 0;
}

// incremental order ID
function generateOrderId($orders) {
    $nextNumber = count($orders) + 1;
    return "LND" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}

// convert to peso
function formatPeso($amount) {return "₱" . number_format($amount, 2);}




?>