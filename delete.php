<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/auth.php";
require_login();
$id=(int)($_GET['id']??0);
if($id>0){
  $stmt=$conn->prepare("DELETE FROM pelanggan WHERE PelangganID=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
}
header("Location: index.php"); exit;
?>