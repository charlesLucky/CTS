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

// flag for add freeze set to false
$add_freeze_valid = false;

// load process from SESSION, will manipulate and add more to it before saving again
$process = $_SESSION['process'];
$process_tech = $_SESSION['user']['initials'];

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
		$in_process[$key] = filter_array($value);
	    }
	    
	    // no blank spaces allowed
	    if($in_process[$key] == '' && $key != 'expressed_volume'){
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
	    $add_freeze_valid = true;
	}
    }
}
	
?>

<html>
    <head>
        <meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="./../styles.css">
        <script language="JavaScript" type="text/javascript">
            var popup;
            function calc_sheet() {
                popup = window.open("../calculations/cell_counts.php", "Popup", "width=auto,height=auto");
                popup.focus();
            }
        </script>
	<script language="JavaScript" type="text/javascript">
	    function getTimeStamp() {
		   var now = new Date();
		   return ((now.getMonth() + 1) + '/' + (now.getDate()) + '/' + now.getFullYear() + " " + now.getHours() + ':'
				 + ((now.getMinutes() < 10) ? ("0" + now.getMinutes()) : (now.getMinutes())) + ':' + ((now.getSeconds() < 10) ? ("0" + now
				 .getSeconds()) : (now.getSeconds())));;
	    }
	    window.onclick = "getTimeStamp" ;
	</script>
        <title> Start Freeze </title>
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
	    Process Started:  
	    <input type="text" name="in_process[start_dt]" readonly="true"
		value="<?php echo isset($in_process['start_dt']) ? $in_process['start_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[start_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br /><strong>Accession Numbers:</strong>
	    <br /><br />PCCD:
	    <input type="text" name="in_process[acc_pccd]"
		value="<?php echo isset($in_process['acc_pccd']) ? $in_process['acc_pccd'] : '' ?>"/>
	    <?php if(isset($error['acc_pccd'])) {echo $error['acc_pccd'];} ?>
	    <br /><br />Initial:
	    <input type="text" name="in_process[acc_init]"
		value="<?php echo isset($in_process['acc_init']) ? $in_process['acc_init'] : '' ?>"/>
	    <?php if(isset($error['acc_init'])) {echo $error['acc_init'];} ?>	
	    <br /><br />Pre-Freeze:
	    <input type="text" name="in_process[acc_prefr]"
		value="<?php echo isset($in_process['acc_prefr']) ? $in_process['acc_prefr'] : '' ?>"/>
	    <?php if(isset($error['acc_prefr'])) {echo $error['acc_prefr'];} ?>
	    <br /><br />SCCC:
	    <input type="text" name="in_process[acc_sccc]"
		value="<?php echo isset($in_process['acc_sccc']) ? $in_process['acc_sccc'] : '' ?>"/>
	    <?php if(isset($error['acc_sccc'])) {echo $error['acc_sccc'];} ?>
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
	    <br /><br />Centrifuge #:
	    <select name="in_process[centrifuge]">
		<?php foreach($add_process_consts['centrifuge'] as $option){
		    echo ($in_process['centrifuge'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Refrigerator #:
	    <select name="in_process[refrigerator]">
		<?php foreach($add_process_consts['refrigerator'] as $option){
		    echo ($in_process['refrigerator'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Scale S/N:
	    <select name="in_process[scale_sn]">
		<?php foreach($add_process_consts['scale_sn'] as $option){
		    echo ($in_process['scale_sn'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Tube Welder S/N:
	    <select name="in_process[tube_welder_sn]">
		<?php foreach($add_process_consts['tube_welder_sn'] as $option){
		    echo ($in_process['tube_welder_sn'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    
	    <br /><br /><strong>Processing Information</strong>
	    <br /><br />Initial Volume (mL):
	    <input type="text" name="in_process[init_volume]" id="volume" size="10"
		value="<?php echo isset($in_process['init_volume']) ? $in_process['init_volume'] : '' ?>"/>
	    <?php if(isset($error['init_volume'])) {echo $error['init_volume'];} ?>
	    By: 
	    <input type="text" name="in_process[weighed_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[init_volume_dt]" readonly="true"
		value="<?php echo isset($in_process['init_volume_dt']) ? $in_process['init_volume_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[init_volume_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br />ACD-A Volume (mL):
	    <input type="text" name="in_process[acda_volume]" id="volume" size="10"
		value="<?php echo isset($in_process['acda_volume']) ? $in_process['acda_volume'] : '' ?>"/>
	    <?php if(isset($error['acda_volume'])) {echo $error['acda_volume'];} ?>
	    <br /><br />Other Anticoagulants (specify type and amount):
	    <input type="text" name="in_process[other_anticoagulants]" size="100"
		value="<?php echo isset($in_process['other_anticoagulants']) ? $in_process['other_anticoagulants'] : '' ?>"/>
	    <br /><br />Initial QC Sampling By:
	    <input type="text" name="in_process[init_sampled_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[init_sample_dt]" readonly="true"
		value="<?php echo isset($in_process['init_sample_dt']) ? $in_process['init_sample_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[init_sample_dt]').value=getTimeStamp()" value="Now!"/>
	    Volume Sampled (mL):
	    <input type="text" name="in_process[init_sample_volume]" size="10"
		value="<?php echo isset($in_process['init_sample_volume']) ? $in_process['init_sample_volume'] : '' ?>"/>
	    <?php if(isset($error['init_sample_volume'])) {echo $error['init_sample_volume'];} ?>
	    <br /><br />Centrifuged By:
	    <input type="text" name="in_process[centrifuged_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[centrifuged_dt]" readonly="true"
		value="<?php echo isset($in_process['centrifuged_dt']) ? $in_process['centrifuged_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[centrifuged_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br />Supernatant Expressed By:
	    <input type="text" name="in_process[expressed_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[expressed_dt]" readonly="true"
		value="<?php echo isset($in_process['expressed_dt']) ? $in_process['expressed_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[expressed_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br />Product Volume Post-Expression:
	    <input type="text" name="in_process[post_express_volume]"
		value="<?php echo isset($in_process['post_express_volume']) ? $in_process['post_express_volume'] : '' ?>"/>
	    <?php if(isset($error['post_express_volume'])) {echo $error['post_express_volume'];} ?>
	    Volume Expressed (No need to fill this out, this will be auto-filled upon form completion):
	    <input type="text" name="in_process[expressed_volume]" readonly="true"
		value="<?php if(isset($in_process['expressed_volume'])) {echo $in_process['expressed_volume'];} ?>"/>
	    <br /><br />Volume of Plasmalyte Added to Product:
	    <input type="text" name="in_process[plyte_added]"
		value="<?php echo isset($in_process['plyte_added']) ? $in_process['plyte_added'] : '' ?>"/>
	    <?php if(isset($error['plyte_added'])) {echo $error['plyte_added'];} ?>
	    By:
	    <input type="text" name="in_process[plyte_added_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[plyte_added_dt]" readonly="true"
		value="<?php echo isset($in_process['plyte_added_dt']) ? $in_process['plyte_added_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[plyte_added_dt]').value=getTimeStamp()" value="Now!"/>
	    <br /><br />Final Prefreeze Volume
	    <input type="text" name="in_process[prefr_volume]"
		value="<?php if(isset($in_process['prefr_volume'])) {echo $in_process['prefr_volume'];} ?>"/>
	    <?php if(isset($error['prefr_volume'])) {echo $error['prefr_volume'];} ?>
	    <br /><br />Freezing Solution Prepared By:
	    <input type="text" name="in_process[freeze_solution_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[freeze_solution_dt]" readonly="true"
		value="<?php echo isset($in_process['freeze_solution_dt']) ? $in_process['freeze_solution_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[freeze_solution_dt]').value=getTimeStamp()" value="Now!"/>
	    Confirmed By:
	    <select name="in_process[freeze_solution_ver]">
		<?php foreach($users['initials'] as $option){
		    echo ($in_process['freeze_solution_ver'] == $option) ?
		    '<option value="' . $option . '" selected="true">' . $option . '</option>' :
		    '<option value="' . $option . '">' . $option . '</option>';
		} ?>
	    </select>
	    <br /><br />Prefreeze QC Sampling By:
	    <input type="text" name="in_process[prefr_sampled_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    At: 
	    <input type="text" name="in_process[prefr_sample_dt]" readonly="true"
		value="<?php echo isset($in_process['prefr_sample_dt']) ? $in_process['prefr_sample_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[prefr_sample_dt]').value=getTimeStamp()" value="Now!"/>
	    Volume Sampled (mL):
	    <input type="text" name="in_process[prefr_sample_volume]" size="10"
		value="<?php echo isset($in_process['prefr_sample_volume']) ? $in_process['init_sample_volume'] : '' ?>"/>
	    <?php if(isset($error['prefr_sample_volume'])) {echo $error['prefr_sample_volume'];} ?>
	    <br /><br />Addition of Freezing Solution By:
	    <input type="text" name="in_process[drip_by]" size="5" value="<?php echo $process_tech ?>" readonly="true"/>
	    Begin: 
	    <input type="text" name="in_process[drip_begin_dt]" readonly="true"
		value="<?php echo isset($in_process['drip_begin_dt']) ? $in_process['drip_begin_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[drip_begin_dt]').value=getTimeStamp()" value="Now!"/>
	    End:
	    <input type="text" name="in_process[drip_end_dt]" readonly="true"
		value="<?php echo isset($in_process['drip_end_dt']) ? $in_process['drip_end_dt'] : '' ?>"/>
	    <input type="button" onclick="this.form.elements.namedItem('in_process[drip_end_dt]').value=getTimeStamp()" value="Now!"/>
	    
	    <br /><br /><strong>Calculations</strong>
            <br /><br />Initial TNC: 
            <input type="text" name="in_process[init_tnc]" id="init_tnc" readonly="true"
		   value="<?php echo isset($in_process['init_tnc']) ? $in_process['init_tnc'] : '' ?>"/>
            <br /><br />Initial TNC/kg: 
            <input type="text" name="in_process[init_tnckg]" id="init_tnckg" readonly="true"
		   value="<?php echo isset($in_process['init_tnckg']) ? $in_process['init_tnckg'] : '' ?>"/>
            <br /><br />Initial CD34: 
            <input type="text" name="in_process[init_cd34]" id="init_cd34" readonly="true"
		   value="<?php echo isset($in_process['init_cd34']) ? $in_process['init_cd34'] : '' ?>"/>
            <br /><br />Initial CD34/kg: 
            <input type="text" name="in_process[init_cd34kg]" id="init_cd34kg" readonly="true"
		   value="<?php echo isset($in_process['init_cd34kg']) ? $in_process['init_cd34kg'] : '' ?>"/>
            <br /><br />Calculation ID:
	    <input type="text" name="in_process[init_calc_id]" id="calc_id" readonly="true"
		   value="<?php echo isset($in_process['init_calc_id']) ? $in_process['init_calc_id'] : '' ?>"/>
	    <br /><br />
            <input type="button" value="Open Cell Count Worksheet" onclick="calc_sheet()"/>
	    
	    <br /><br /><strong>Aliquot Info</strong>
	    <br /><br />Number of Aliquots:
	    <input type="text" name="in_process[aliquots]"
		   value="<?php echo isset($in_process['aliquots']) ? $in_process['aliquots'] : '' ?>"/>
	    <?php if(isset($error['aliquots'])) {echo $error['aliquots'];} ?>
	    <br /><br />Volume of Aliquots (mL):
	    <input type="text" name="in_process[aliquot_volume]"
		   value="<?php echo isset($in_process['aliquot_volume']) ? $in_process['aliquot_volume'] : '' ?>"/>
	    <?php if(isset($error['aliquot_volume'])) {echo $error['aliquot_volume'];} ?>
	    <br /><br /><strong>Flags</strong>
	    <br /><textarea name="in_process[flags]" rows="5" cols="25"><?php echo isset($in_process['flags']) ? $in_process['flags'] : '' ?>
	    </textarea>
	    
	    <br /><br /><input type="submit" name="submit-in-process" value="Proceed to Post-Freeze"/>
	    &nbsp;<input type="reset" name="reset-in-process" value="Reset"/>
        </form>
        </div>
    </body>
</html>

<?php

// if everything is all set add freeze to freeze table
if($add_freeze_valid){
    
    $pdo = connect();
    
    if($pdo){
	// pass the pdo object by reference to member function to write to freeze table
	$process->write_freeze_to_db($pdo);
	
	// pass pdo object by reference to member function to update the derivatives of the product to the number of aliquots
	$process->update_product_derivatives($pdo);
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
    echo '<br /><br /><a href="post_freeze.php">Proceed to Post-Freeze</a>';
}

?>
