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
        <title> Patients Index </title>
    </head>
    <body>
        <div class="patients-index">
            <h1 style="align-content: center"><br /> Patients </h1>
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
            
            <div class="patients-nav">
                <ul id="patients-nav-menu">
                    <li><a href="index.php">Patients Index</a></li>
                    <li><a href="view_patient.php">View Patients</a></li>
                    <li><a href="add_patient.php">Add New Patient</a></li>
                    <li><a href="update_patient.php">Update Patient</a></li>
                    <li><a href="delete_patient.php">Delete Patient</a></li>
                </ul>
            </div>
        </div>
        
    </body>
</html>


