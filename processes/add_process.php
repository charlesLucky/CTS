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

// flag for valid product to add
$add_process_valid = false;

// if start new process is submitted, validate that the DIN exists and all the preliminary fields are complete
if(isset($_POST['submit-process'])){
    
    // initialize empty array for preliminary fields
    $pre = array();
    $error = array();
    
    $pre['din'] = filter_var($_POST['pre']['din']);
    $pre['process_name'] = filter_var($_POST['pre']['process_name']);
    
    $pdo = connect();
    
    if($pdo){
	
	// validate that all the prerequisite data is correct and the
	// process is compatible with the product's child specimens
	validate_pre_process($pdo, $pre, $error);
	
	echo '<br /><br />';
	var_dump($pre);
    }
    
    if(empty($error)){
	
	// initialize a new process based on process_name
	$i = $pre['process_name'];
    
	// create a new object specific to the process
	// through inheritance, each of these are a process and will have access to its data members
	switch($i){

	    case 'Freeze':		   
		$process = new Freeze($pre);
		break;
	    case 'Thaw':		    
		$process = new Thaw($pre);
		break;
	    case 'Unmanipulated':	    
		$process = new Unmanip($pre);
		break;
	    case 'Plasma Deplete':
		$process = new PlDep($pre);
		break;
	    case 'RBC Deplete':
		$process = new RBCDep($pre);
		break;
	}
	
	// initialize process by creating an entry in processes table, DO NOT ADD TO PROCESS SPECIFIC TABLE YET!!!
	$process->initialize_process($pdo);
	
	// store the object in SESSION so you can use it throughout the processing
	$_SESSION['process'] = $process;
	
	if($process->PID != null){
	    $add_process_valid = true;
	}
	
	echo '<br /><br />';
	var_dump($process);
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Add a Process </title>
    </head>
    <body>
        <div class="processes-add">
            <h1 style="align-content: center"> Add a Process </h1>
            <div class="products-nav">
		<li style="display: inline-block"><a href="index.php">Processes Index</a></li>
                <li style="display: inline-block"><a href="view_process.php">View Processes</a></li>
                <li style="display: inline-block"><a href="add_process.php">Add New Processes</a></li>
                <li style="display: inline-block"><a href="update_process.php">Update Processes</a></li>
                <li style="display: inline-block"><a href="delete_process.php">Delete Processes</a></li>
            </div><hr style="width:1368px"/>
            <div class="products-add-form">
                <form method="post" action="#">
                    <br /><br />Please enter the DIN of the product you wish to process:
		    <input type="text" name="pre[din]" 
			value="<?php if(isset($pre['din'])) {echo $pre['din'];} ?>"/>
		    <?php if(isset($error['din_lookup'])) {echo $error['din_lookup'];} ?>
		    <br /><br />Please select the processing to perform:
		    <select name="pre[process_name]">
			<?php foreach($add_process_consts['process_name'] as $option){
			    echo ($pre['process_name'] == $option) ?
			    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			    '<option value="' . $option . '">' . $option . '</option>';
			} ?>
		    </select>
		    <?php if(isset($error['conflict'])) {echo $error['conflict'];} ?>
                    
                    <br /><br /><input type="submit" name="submit-process" value="Start New Process"/>
                    &nbsp;<input type="reset" name="reset-process" value="Reset"/>
                </form>
            </div>
        </div>
    </body>
</html>

<?php

if($add_process_valid && isset($_SESSION['process'])){

    // route process to appropriate page
    $i = $process->pre['process_name'];
    
    switch($i){
	
	case 'Freeze':		   
	    echo '<a href="start_freeze.php">Start Freeze</a>';
	    break;
	case 'Thaw':		    
	    echo '<a href="start_thaw.php">Start Thaw</a>';
	    break;
	case 'Unmanipulated':	    
	    echo '<a href="start_unmanip.php">Start Unmanipulated</a>';
	    break;
	case 'Plasma Deplete':
	    echo '<a href="start_pldep.php">Start Plasma Deplete</a>';
	    break;
	case 'RBC Deplete':
	    echo '<a href="start_rbcdep.php">Start RBC Deplete</a>';
	    break;
    }
}

//phpinfo(INFO_VARIABLES);

?>


