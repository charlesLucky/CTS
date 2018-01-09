<?php

// redirect if session is not valid
session_start();
if(!$_SESSION['valid']){
    header('Location: http://localhost/CTS/');
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="./../styles.css">
        <title> Products Index </title>
    </head>
    <body>
        <div class="products-index">
            <h1 style="align-content: center"><br /> Products </h1>
            <div class="main-nav">
                <ul id="main-nav-menu">
                    <li><a href="./../frontpage.php">Home</a></li>
                    <li><a href="./../products/index.php">Products</a></li>
                    <li><a href="./../users/index.php">Users</a></li>
                    <li><a href="./../ln2_locations/index.php">LN2 Locations</a></li>
                    <li><a href="./../processes/index.php">Processes</a></li>
                    <li><a href="./../patients/index.php">Patients</a></li>
		    <ul style="float:right;list-style-type:none;">
			<li><a href="./../logout.php">Logout</a></li>
		    </ul>
                </ul>
            </div>
            
            <div class="products-nav">
                <ul id="products-nav-menu">
                    <li><a href="index.php">Products Index</a></li>
                    <li><a href="view_product.php">View Products</a></li>
                    <li><a href="add_product.php">Add New Products</a></li>
                    <li><a href="update_product.php">Update Products</a></li>
                    <li><a href="delete_product.php">Delete Products</a></li>
                </ul>
            </div>
        </div>
        
    </body>
</html>


