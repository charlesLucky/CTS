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
        <title> LN2 Locations Index </title>
    </head>
    <body>
        <div class="products-index">
            <h1 style="align-content: center"><br /> LN2 Locations </h1>
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
            
            <div class="ln2_locations-nav-main">
                <ul id="ln2_locations-nav-menu">
                    <li><a href="index.php">LN2 Locations Index</a></li>
                    <li><a href="view_ln2_location.php">View LN2 Locations</a></li>
                    <li><a href="add_ln2_location.php">Add New LN2 Locations</a></li>
                    <li><a href="update_ln2_location.php">Update LN2 Locations</a></li>
                    <li><a href="delete_ln2_location.php">Delete LN2 Locations</a></li>
		    <li><a href="assign_ln2_location.php">Assign LN2 Locations</a></li>
                </ul>
            </div>
        </div>
        
    </body>
</html>