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

// flag for add thaw set to false
$add_thaw_valid = false;

// load process from SESSION, will manipulate and add more to it before saving again
$process = $_SESSION['process'];
$process_tech = $_SESSION['user']['initials'];

// Connect to DB right away to access derivative and freeze data
$pdo = connect();

if($pdo){
    // load the derivative information for use in the thawing procedure, mostly volume and component_code
    $process->load_derivatives($pdo);
}

//var_dump($process->derivative_data);

if(isset($_POST['submit-in-process'])){
    
    // first, filter all variables and store in in_process array
    if(isset($_POST['in_process']) && is_array($_POST['in_process']) && count($_POST['in_process']) > 0){
	
	// initialize error array
	$error = array();
	
	// populate in_process array with critical information
	$in_process['process_id'] = $process->PID;
	$in_process['din'] = $process->pre['din'];
	$in_process['process_name'] = $process->pre['process_name'];
	$in_process['process_phase'] = 'in-process';

	// filter form data and populate into in_process array
	foreach($_POST['in_process'] as $key => $value){
	    if(!is_array($value)){
		$in_process[$key] = filter_var($value); 
	    }
	    else{
		$in_process[$key] = array_filter($value);
	    }
	    
	    // no blank spaces allowed
	    if($in_process[$key] == ''){
		$error[$key] = 'No fields can be left blank on the worksheet';
	    }
	}
	
	// call validate function, if function call is successful, $this->in_process will be populated
	$process->validate_in_process($in_process, $error);
	
	echo '<br/><br/>';
	//var_dump($process);
	
	echo '<br/><br/>';
	//var_dump($error);
	
	// Check error array
	if(empty($error)){
	    echo '<br />SUCCESS!!!!!';
	    $add_thaw_valid = true;
	}
    }
}
	
?>

<html>
    <head>
        <meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="./../styles.css">
	<script language="JavaScript" type="text/javascript">
	    function getTimeStamp() {
		   var now = new Date();
		   return ((now.getMonth() + 1) + '/' + (now.getDate()) + '/' + now.getFullYear() + " " + now.getHours() + ':'
				 + ((now.getMinutes() < 10) ? ("0" + now.getMinutes()) : (now.getMinutes())) + ':' + ((now.getSeconds() < 10) ? ("0" + now
				 .getSeconds()) : (now.getSeconds())));;
	    }
	    window.onclick = "getTimeStamp" ;
	</script>
        <title> Start Thaw </title>
    </head>
    <body>
	<div class="fixed-header-preliminary-info">
		<br /><br /><strong>Preliminary Info:</strong><br />
		<table >
		<?php $x = 0;
		foreach($process->pre as $key => $value){ 
		    if($x % 5 == 0){
			echo '<tr><td>' . $key . ': '. $value . '</td>';
		    }
		    else{
			echo '&nbsp<td>' . $key . ': '. $value . '</td>';
		    }
		    $x++;
		} ?>
		</table>
		<br /><hr />
	</div>
	
        <div class="process-start">
        <form method="post" action="#" id="thaw">
	    <br /><br />Processing Tech:
	    <input type="text" name="in_process[process_tech]" value="<?php echo $process_tech ?>" size="5" readonly="true"/>
	    Thaw Buddy:
	    <select name="in_process[thaw_buddy]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['thaw_buddy'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    Process Started:  
	    <input type="text" name="in_process[start_dt]" readonly="true"
		value="<?php echo isset($in_process['start_dt']) ? $in_process['start_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[start_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br /><strong>Accession Numbers:</strong>
	    <br /><br />TRYVIA:
	    <input type="text" name="in_process[acc_tryvia]"
		value="<?php echo isset($in_process['acc_tryvia']) ? $in_process['acc_tryvia'] : '' ?>"/>
	    <?php if(isset($error['acc_tryvia'])) {echo $error['acc_tryvia'];} ?>
	    <br /><br />TPCH:
	    <input type="text" name="in_process[acc_tpch]"
		value="<?php echo isset($in_process['acc_tpch']) ? $in_process['acc_tpch'] : '' ?>"/>
	    <?php if(isset($error['acc_tpch'])) {echo $error['acc_tpch'];} ?>
	    
	    <br /><br /><strong>Equipment Information:</strong>
	    <br /><br />BSC S/N:
	    <select name="in_process[bsc_sn]">
		<?php foreach($add_process_consts['bsc_sn'] as $option){
		    echo ($in_process['bsc_sn'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />BSC airflow within range?
	    <select name="in_process[in_range]">
		<option value="True" selected="true">True</option>
		<option value="False">False</option>
	    </select>
	    <br /><br />BSC Cleaned Before By:
	    <select name="in_process[clean_before]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['clean_before'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />BSC Cleaned Before At:
	    <input type="datetime-local" name="in_process[clean_before_datetime]"
		value="<?php echo isset($in_process['clean_before_datetime']) ? $in_process['clean_before_datetime'] : '' ?>"/>
	    <?php if(isset($error['clean_before_datetime'])) {echo $error['clean_before_datetime'];} ?>
	    <br /><br />BSC Cleaned After By:
	    <select name="in_process[clean_after]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['clean_after'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />BSC Cleaned After At:
	    <input type="datetime-local" name="in_process[clean_after_datetime]"
		value="<?php echo isset($in_process['clean_after_datetime']) ? $in_process['clean_after_datetime'] : '' ?>"/>
	    <?php if(isset($error['clean_after_datetime'])) {echo $error['clean_after_datetime'];} ?>
	    <br /><br />Water Bath Cleaned By:
	    <select name="in_process[waterbath_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['waterbath_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    
	    <br /><br /><strong>Processing Information:</strong>
	    <br /><br />Please select the components you wish to thaw:
	    <?php echo '<br /><br />' , $process->pre['din'];
	    $i = 0;
	    foreach($process->derivative_data as $derivative){
		foreach($derivative as $key => $value){
		    if($key == 'component_code'){
			if($in_process['selected_components'][$i] == $value){
			    echo '<br /><input type="checkbox" name="in_process[selected_components][]" value="' . $value . '"checked>' . $value . '</input>';
		    	}
			else{
			    echo '<br /><input type="checkbox" name="in_process[selected_components][]" value="' . $value . '">' . $value . '</input>';
			}
			$i++;
		    }
		    
		}
	    } ?>
	    <?php if(isset($error['selected_components'])) {echo '<br />', $error['selected_components'];} ?>
	    <br /><br />Pool Number (if not applicable, enter NA):
	    <input type="text" name="in_process[pool_number]"
		   value="<?php echo isset($in_process['pool_number']) ? $in_process['pool_number'] : '' ?>"/>
	    <?php if(isset($error['pool_number'])) {echo $error['pool_number'];} ?>
	    <br /><br />Product Sterility:
	    (Check Accn: <?php echo $process->derivative_data[0]['acc_prefr']; ?>)
	    <select name="in_process[sterility]">
		<?php foreach($add_process_consts['sterility'] as $option){
		    echo ($in_process['sterility'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br />If pending sterility, how many days since BacT's were inoculated
	    <input type="text" name="in_process[sterility_state]"
		   value="<?php echo isset($in_process['sterility_state']) ? $in_process['sterility_state'] : '' ?>"/>
	    <?php if(isset($error['sterility_state'])) {echo $error['sterility_state'];} ?>
	    <br /><br />Product Retrieved From LN2 By:
	    <select name="in_process[retrieved_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['retrieved_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Product Verified By:
	    <select name="in_process[verified_by]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['verified_by'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Total Volume:
	    <input type="text" name="in_process[total_volume]"
		   value="<?php echo isset($in_process['total_volume']) ? $in_process['total_volume'] : '' ?>"/>
	    <br /><br />ACD-A Added (mL):
	    <input type="text" name="in_process[acda_added]"
		   value="<?php echo isset($in_process['acda_added']) ? $in_process['acda_added'] : '' ?>"/>
	    <br /><br />Product Placed Into Water Bath At:
	    <input type="text" name="in_process[waterbath_dt]" readonly="true"
		value="<?php echo isset($in_process['waterbath_dt']) ? $in_process['waterbath_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[waterbath_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br />Temperature of Water Bath:
	    <input type="text" name="in_process[waterbath_temp]"
		   value="<?php echo isset($in_process['waterbath_temp']) ? $in_process['waterbath_temp'] : '' ?>"/>
	    <br /><br />Trypan Blue Viability:
	    <input type="text" name="in_process[tryvia]"
		   value="<?php echo isset($in_process['tryvia']) ? $in_process['tryvia'] : '' ?>"/>
	    
	    
	    <br /><br /><strong>Flags</strong>
	    <br /><textarea name="in_process[flags]" rows="5" cols="25"><?php echo isset($in_process['flags']) ? $in_process['flags'] : '' ?>
	    </textarea>
	    
	    <br /><br /><input type="submit" name="submit-in-process" value="Complete Thaw"/>
	    &nbsp;<input type="reset" name="reset-in-process" value="Reset"/>
        </form>
        </div>
    </body>
</html>

<?php

// if everything is all set add thaw to thaw table
if($add_thaw_valid){
    
    $pdo = connect();
    
    if($pdo){
	echo '<br /> all validated, doing the heavy lifting now';
	
	// pass the pdo object by reference to member function to write to thaw table
	$process->write_thaw_to_db($pdo);  
	
	// pass pdo object by reference to member function to update the derivative statuses
	// this will also update the product table to reflect the derivative count, 
	// will set to NULL if all components are thawed
	// lastly, this function will update the pool number if needed
	$process->update_product_derivatives($pdo);
	
	$process->thaw_ln2($pdo); 
    }
    else{
	echo '<br /><br />Failed to connect to DB';
    }
    
    echo '<br/><br/>';
    //var_dump($process);
    // update SESSION variable storing the process
    $_SESSION['process'] = $process;
    
    // return to patient index
    echo '<br /><br /><a href="./">Return to Process Index</a>';
}

?>