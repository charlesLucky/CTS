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
        <title> Users Index </title>
    </head>
    <body>
        <div class="products-index">
            <h1 style="align-content: center"><br /> Users </h1>
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
            
            <div class="users-nav">
                <ul id="users-nav-menu">
                    <li><a href="index.php">Users Index</a></li>
                    <li><a href="view_user.php">View Users</a></li>
                    <li><a href="add_user.php">Add New Users</a></li>
                    <li><a href="update_user.php">Update Users</a></li>
                    <li><a href="delete_user.php">Delete Users</a></li>
                </ul>
            </div>
        </div>
        
    </body>
</html>

