<?php 

if(isset($_GET['page'])){
    $page = $_GET['page'];
    echo("pages/{$page}.php");
    include("pages/{$page}.php"); 
}

else{
    include('pages/home.php');
}
?>