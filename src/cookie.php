<?php
session_start();
include("config.php");
if(isset($_COOKIE["id"])){
    $_SESSION["id"] = $_COOKIE["id"];
    $id = $_SESSION["id"];
    $users = mysqli_query($conn, "SELECT * FROM t_admin WHERE f_id = '$id'");
    $user = mysqli_fetch_assoc($users); 
}elseif(isset($_SESSION["id"])){
    $id = $_SESSION["id"];
    $users = mysqli_query($conn, "SELECT * FROM t_admin WHERE f_id = '$id'");
    $user = mysqli_fetch_assoc($users); 
}else{
    header("Location: ../../index.php");
}
?>