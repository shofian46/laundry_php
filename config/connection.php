<?php 
    $hostname = "localhost";
    $hostusername = "root";
    $hostpassword = "";
    $hostdatabase = "db_laundry";
    $conn = mysqli_connect($hostname, $hostusername, $hostpassword, $hostdatabase);
    if(!$conn){
        echo "Connection failed!";
    }
?>