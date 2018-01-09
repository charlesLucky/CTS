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

$freeze_complete = false;
$freeze_validated = false;
$freeze_loaded = false;

// if in the same session load process from session into local variable
if(isset($_SESSION['process'])){
    
    $process = $_SESSION['process'];

    // if session has the in_process portion saved to DB and available, we can proceed directly
    if($process->freeze_id != null){
	$complete_din = $process->in_process['din'];
	$process->attributes = $process->in_process;
	$process->attributes['freeze_id'] = $process->freeze_id;
	
	$freeze_loaded = true;
    }
}
else{
    echo '<br />', 'No previous process found in session data';
}

//var_dump($process);

// if a DIN needs to be fetched, do it
if(isset($_GET['get-process'])){
    
    // filter DIN of cryopreservation to complete
    $complete_din = filter_var($_GET['complete_din']);
    
    // connect to DB
    $pdo = connect();
    if($pdo){
	
	// query the process
	$sql = 'SELECT * FROM `freeze` WHERE `din` = :din';
	$statement = $pdo->prepare($sql);
	
	if($statement->execute(array(':din' => $complete_din))){
	
	    $result = $statement->fetch(PDO::FETCH_ASSOC);

	    if($result['process_phase'] == 'post-process'){
		$process = new Freeze($result);
		$process->attributes = $process->pre;
		unset($process->pre);

		$complete_din = $process->attributes['din'];
		
		$freeze_loaded = true;
	    }
	    else{
		echo 'uh oh, that DIN belongs to a product that is not in the post-process phase';
	    }
	}
	else{
	    echo 'uh oh, that DIN was not found in the Freeze table';
	}
	
	$sql = 'SELECT * FROM `products` WHERE `products`.`din` = ' . $complete_din;
	$header = $pdo->query($sql);
		
	$process->pre = $header;
    }
}

//var_dump($process);

if(isset($_POST['submit-post-process'])){

    $error = array();
    
    // filter form data and populate the post_process array
    foreach($_POST['post_process'] as $key => $value){
	if(!is_array($value)){
		$post_process[$key] = filter_var($value); 
	    }
	    else{
		$post_process[$key] = filter_array($value);
	    }
    }
    
    // send post_process and error arrays by reference to a function which will validate the data
    // if validated, the function will also populate $this->post using the post_process array
    $process->validate_post_process($post_process, $error);
    
    if(empty($error)){

	$freeze_validated = true;
    }
}

echo '<br /><br />';
//var_dump($process);

?>

<html>
    <head>
        <meta charset="UTF-8">
        <script language="JavaScript" type="text/javascript">
            var popup;
            function calc_sheet() {
                popup = window.open("../calculations/cell_counts.php", "Popup", "width=auto,height=auto");
                popup.focus();
            }
        </script>
        <title> Post Freeze </title>
    </head>
    <body>
        <div class="process-start-freeze">
	<form method="get" action="#" id="get-process">
	    <br /><br />Please enter the DIN of the product you wish to proceed completing cryopreservation:
	    <input type="text" name="complete_din" 
		   value="<?php echo isset($complete_din) ? $complete_din : '' ?>"/>
	    
	    <br /><input type="submit" name="get-process" value="Load Freeze"/>
	</form>
	    
	<?php 
	if($freeze_loaded){
	    echo '<br />', 'Freeze loaded successfully, currently in th post-freeze phase.';
	    echo '<br />', 'Please fill out and submit the form to complete the freeze.';
	} 
	else{
	    echo '<br />', 'Freeze failed to load, either the DIN was not found,';
	    echo '<br />', 'or the process is not in the "post-freeze" phase';
	}
	?>
	    
        <form method="post" action="#" id="freeze">
	    <br /><br />Kryo 10 Used:
	    <select name="post_process[kryo_10]"> 
		<?php foreach($add_process_consts['kryo_10'] as $option){
		    echo ($post_process['kryo_10'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Kryo 10 Turned on and Verified By:
	    <select name="post_process[kryo_10_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($post_process['kryo_10_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Kryo 10 Turned on and Verified At:
	    <input type="datetime-local" name="post_process[kryo_10_datetime]"
		value="<?php echo isset($post_process['kryo_10_datetime']) ? $post_process['kryo_10_datetime'] : '' ?>"/>
	    <?php if(isset($error['kryo_10_datetime'])) {echo $error['kryo_10_datetime'];} ?>
	    <br /><br />Products Placed into Kryo 10 By:
	    <select name="post_process[products_kryo_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($post_process['products_kryo_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Products Placed into Kryo 10 At:
	    <input type="datetime-local" name="post_process[products_kryo_datetime]"
		value="<?php echo isset($post_process['products_kryo_datetime']) ? $post_process['products_kryo_datetime'] : '' ?>"/>
	    <?php if(isset($error['products_kryo_datetime'])) {echo $error['products_kryo_datetime'];} ?>
	    <br /><br />Products Placed into LN2 By:
	    <select name="post_process[products_ln2_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($post_process['products_ln2_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Products Placed into LN2 At:
	    <input type="datetime-local" name="post_process[products_ln2_datetime]"
		value="<?php echo isset($post_process['products_ln2_datetime']) ? $post_process['products_ln2_datetime'] : '' ?>"/>
	    <?php if(isset($error['products_ln2_datetime'])) {echo $error['products_ln2_datetime'];} ?>
	    <br /><br />Freezing Chart Review and LN2 Freezer Verification By:
	    <select name="post_process[ln2_chart_ver]">
		<?php foreach($users['initials'] as $option){
		    echo ($post_process['ln2_chart_ver'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    
	    <br /><br /><strong>Calculations</strong>
            <br /><br />Prefreeze TNC: 
            <input type="text" name="post_process[prefr_tnc]" id="prefr_tnc" readonly="true"
		   value="<?php echo isset($post_process['prefr_tnc']) ? $post_process['prefr_tnc'] : '' ?>"/>
            <br /><br />Prefreeze TNC/kg: 
            <input type="text" name="post_process[prefr_tnckg]" id="prefr_tnckg" readonly="true"
		   value="<?php echo isset($post_process['prefr_tnckg']) ? $post_process['prefr_tnckg'] : '' ?>"/>
            <br /><br />Prefreeze CD34: 
            <input type="text" name="post_process[prefr_cd34]" id="prefr_cd34" readonly="true"
		   value="<?php echo isset($post_process['prefr_cd34']) ? $post_process['prefr_cd34'] : '' ?>"/>
            <br /><br />Prefreeze CD34/kg: 
            <input type="text" name="post_process[prefr_cd34kg]" id="prefr_cd34kg" readonly="true"
		   value="<?php echo isset($post_process['prefr_cd34kg']) ? $post_process['prefr_cd34kg'] : '' ?>"/>
            <br /><br />Calculation ID:
	    <input type="text" name="post_process[prefr_calc_id]" id="calc_id" readonly="true"
		   value="<?php echo isset($post_process['prefr_calc_id']) ? $post_process['prefr_calc_id'] : '' ?>"/>
	    <br /><br />
            <input type="button" value="Open Cell Count Worksheet" onclick="calc_sheet()"/>

	    
	    <br /><br /><input type="submit" name="submit-post-process" value="Complete Cryopreservation"/>
	    &nbsp;<input type="reset" name="reset-post-process" value="Reset"/>
        </form>
        </div>
    </body>
</html>

<?php

// once freeze has been validated, we can update the fields in the freeze table and create the derivatives
// After this step, the entry in the freeze table will be complete and phase updated, now it awaits review
if($freeze_validated){
    
    if(!isset($pdo)){
	$pdo = connect();
    }
    // pass pdo object to allow it to update the entry, remember to change phase to complete
    // pass pdo object to allow it to create new entries in the derivatives table based on number of aliquots
    if($process->update_freeze($pdo)){
	
	echo '<br /> Freeze process has been updated';
	if($process->create_derivatives($pdo)){
	    
	    echo '<br /> Derivative products (aliquots) have been created';
	    $freeze_complete = true;
	}
	else{
	    echo '<br /> Failed to create derivative products (aliquots)';
	}
    }
    else{
	echo '<br /> Failed to update freeze process';
    }
}

// if freeze is complete, change the phase and await reviews
if($freeze_complete){
    
    $process->freeze_pending_review($pdo);
    
    // return to processes index
    echo '<br /><br /><a href="./">Processes Index</a>';
}



   