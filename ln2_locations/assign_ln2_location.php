<?php

include '/../constants/constants.php';
include '/../libraries/controller.php';

// resume session
session_start();
//var_dump($_SESSION);

// redirect if session not valid
if(!$_SESSION['valid']){
    header('Location: http://localhost/CTS/');
}

if(isset($_POST['assign-ln2-submit'])){
    
    foreach($_POST['assign'] as $key => $value){
	if(!is_array($value)){
	    $assign[$key] = filter_var($value);
	}
	else{
	    $assign[$key] = filter_array($value);
	}
    }
    
    var_dump($assign);
    
    $pdo = connect();
    
    if($pdo){
    
	// initialize an LN2 object with $assign array, this should 
	// populate the $attributes and $location array
	$ln2 = new LN2($assign);
	
	var_dump($ln2);	
	
	// if the position is empty
	if($ln2->is_empty($pdo)){
	    echo '<br />LN2 location is empty, assigning location now...';
	    $ln2->assign_ln2_location($pdo);
	}
	else{
	    echo '<br />The LN2 location you are trying to assign is already occupied';
	}
    }
}




?>

<html>
    <head>
	<link rel="stylesheet" type="text/css" href="./../styles.css">
	<title>Assign LN2 Locations</title>
    </head>
    
    <body>
	<div class="ln2_locations-nav">
	    <ul id="ln2_locations-nav-menu">
		<li><a href="index.php">LN2 Locations Index</a></li>
		<li><a href="view_ln2_location.php">View LN2 Locations</a></li>
		<li><a href="add_ln2_location.php">Add New LN2 Locations</a></li>
		<li><a href="update_ln2_location.php">Update LN2 Locations</a></li>
		<li><a href="delete_ln2_location.php">Delete LN2 Locations</a></li>
		<li><a href="assign_ln2_location.php">Assign LN2 Locations</a></li>
	    </ul>
	</div>
	<div class="ln2-assign">
	<form method="post" action="#" id="assign-ln2">
	    <br /><br /><strong>Product LN2 Locations</strong>
	    <br /><br />DIN:
	    <input type="text" name="assign[din]"
		   value="<?php echo isset($assign['din']) ? $assign['din'] : '' ?>"/>
	    <br /><br />Component Code:
	    <input type="text" name="assign[component_code]"
		   value="<?php echo isset($assign['component_code']) ? $assign['component_code'] : '' ?>"/>
	    <br /><br />Tank:
	    <select name="assign[tank]">
		<?php foreach($ln2_consts['tank'] as $option){
		    echo ($assign['tank'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Rack Letter:
	    <select name="assign[rack_letter]">
		<?php foreach($ln2_consts['rack_letter'] as $option){
		    echo ($assign['rack_letter'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Rack Number:
	    <input type="text" name="assign[rack_number]"
		   value="<?php echo isset($assign['rack_number']) ? $assign['rack_number'] : '' ?>"/>
	    <br /><br />Slot:
	    <select name="assign[slot]">
		<?php foreach($ln2_consts['slot'] as $option){
		    echo ($assign['slot'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    
	    <br /><br />
	    <input type="submit" name="assign-ln2-submit" value="Assign LN2 Locations"/>
	</form>
	</div>
    </body>
</html>

<?php


	    