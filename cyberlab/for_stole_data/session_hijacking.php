<?php
$url = 'http://localhost/Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/dashboard/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: PHPSESSID=vogvrt204525ibodjkifnclirl']);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo $response;
}
curl_close($ch);
