<?php

include '/../constants/constants.php';
include '/../libraries/controller.php';

// resume session
session_start();
var_dump($_SESSION);

// redirect if session not valid
if(!$_SESSION['valid']){
    header('Location: http://localhost/CTS/');
}

if(isset($_SESSION['process'])){
    // load process from session into local variable
    $process = $_SESSION['process'];
}

if(isset($_POST['update-process'])){
    
    $update['din'] = filter_var($_POST['update']['din']);
    
    $pdo = connect();
    
    $sql = 'SELECT * FROM `processes`
	    WHERE `din` = :din';
    $statement = $pdo->prepare($sql);
    $statement->execute(array(':din' => $update['din']));
    
    
	    
    if($pdo){
    
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Update a Process </title>
    </head>
    <body>
        <div class="processes-update">
            <h1 style="align-content: center"> Update a Process </h1>
            <div class="products-nav">
		<li style="display: inline-block"><a href="index.php">Processes Index</a></li>
                <li style="display: inline-block"><a href="view_process.php">View Processes</a></li>
                <li style="display: inline-block"><a href="add_process.php">Add New Processes</a></li>
                <li style="display: inline-block"><a href="update_process.php">Update Processes</a></li>
                <li style="display: inline-block"><a href="delete_process.php">Delete Processes</a></li>
		<li style="display: inline-block"><a href="post_freeze.php">Post-Freeze</a></li>
            </div><hr style="width:1368px"/>
            <div class="products-update-form">
                <form method="post" action="#">
                    <br /><br />Please enter the DIN of the product you wish to update:
		    <input type="text" name="update[din]" 
			value="<?php if(isset($update['din'])) {echo $update['din'];} ?>"/>
		    <?php if(isset($error['din_lookup'])) {echo $error['din_lookup'];} ?>
                    
                    <br /><br /><input type="submit" name="update-process" value="Update Process"/>
                    &nbsp;<input type="reset" name="reset-process" value="Reset"/>
                </form>
            </div>
        </div>
    </body>
</html>
