<?php
//functions for laundry processing

// stops the browser from showing a cached copy of a protected page
// (e.g. via the Back/Forward button) after the user has logged out
function preventCaching() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
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