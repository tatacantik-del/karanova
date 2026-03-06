<?php
require_once __DIR__ . "/../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json");

$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
echo json_encode(["count"=>0,"total"=>0,"items"=>[]]);
exit;
}

$ids = implode(",", array_map("intval", array_keys($cart)));
$res = $conn->query("SELECT ProdukID, Nama_produk, Harga FROM produk WHERE ProdukID IN ($ids)");

$items=[];
$total=0;
$count=0;

while($p=$res->fetch_assoc()){
$pid=$p['ProdukID'];
$qty=$cart[$pid];
$sub=$p['Harga']*$qty;
$total+=$sub;
$count+=$qty;
$items[]=["Nama_produk"=>$p['Nama_produk'],"qty"=>$qty,"subtotal"=>$sub];
}

echo json_encode(["count"=>$count,"total"=>$total,"items"=>$items]);