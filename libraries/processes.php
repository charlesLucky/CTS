<?php

// generic function to validate preliminary information, regardless of process type
function validate_pre_process(&$pdo, &$pre, &$error)
{
    $sql = 'SELECT * FROM `products` WHERE `din` = :din';

    $statement = $pdo->prepare($sql);
    $statement->execute(array(':din' => $pre['din']));

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if(!$result){
	$error['din_lookup'] = 'Product DIN was not found in database, please make sure you have added the product before processing';
    }
    else{
	// populate $pre array using products table
	foreach($result as $key => $value){
	    $pre[$key] = $value;
	}

	// Check the process name with availability of children
	if($pre['process_name'] == 'Freeze' && $pre['derivatives'] != null){
	    $error['conflict'] = 'Cannot freeze product that already has derivative specimens';
	}

	if($pre['process_name'] == 'Thaw' && $pre['derivatives'] == null){
	    $error['conflict'] = 'Cannot thaw product that has no available derivative specimens';
	}
    }
}

// generic parent class for all processes, all specific processes will inherit from this class
class Process
{
    public $PID = null;
    public $pre = array();
    public $in_process = array();
    public $post = array();
    public $complete = false;
    public $attributes = array();
    
    public function __construct($pre)
    {
	$this->pre = $pre;
    }
    
    // creates generic process and saves process ID for specific processes
    public function initialize_process(&$pdo)
    {
	$sql = 'INSERT INTO `processes`
		(`process_name`, `din`) 
		VALUES 
		(:process_name, :din)';

	$statement = $pdo->prepare($sql);
	$statement->execute(array(':process_name' => $this->pre['process_name'],
				  ':din' => $this->pre['din']));
	
	// set PID based on insert
	$this->PID = $pdo->lastInsertId();
    }
}

class Thaw extends Process
{
    public $derivative_data = array();	
    
    public function load_derivatives(&$pdo)
    {
	$sql = 'SELECT `freeze`.`din`,`product_derivatives`.`volume`,`freeze`.`acc_prefr`, '
		. '`product_derivatives`.`component_code`, `product_derivatives`.`tnc`, '
		. '`product_derivatives`.`tnckg`, `product_derivatives`.`cd34`, `product_derivatives`.`cd34kg`,'
		. '`product_derivatives`.`status` '
		. 'FROM `freeze` '
		. 'INNER JOIN `product_derivatives` ON `freeze`.`din` = `product_derivatives`.`din` '
		. 'WHERE `freeze`.`din` = :din';
	
	$statement = $pdo->prepare($sql);
	
	if($statement->execute(array(':din' => $this->pre['din']))){
	    echo '<br />Derivative data loaded successfully';
	    $this->derivative_data = $statement->fetchAll(PDO::FETCH_ASSOC);
	    
	    // remove any components whose status is not cryopreserved
	    foreach($this->derivative_data as $key => $value){
		if($value['status'] != '-150 storage'){
		    unset($this->derivative_data[$key]);
		}
	    }
	}
	else{
	    echo '<br />Failed to load derivative data';
	    var_dump($statement->errorInfo());
	}
    }
    
    public function validate_in_process(&$in_process, &$error)
    {
	// validate accession numbers
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_tryvia'])){
	    $error['acc_tryvia'] = 'Invalid accession number';
	}
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_tpch'])){
	    $error['acc_tpch'] = 'Invalid accession number';
	}
	
	// validate key numbers and fields
	if(floatval($in_process['total_volume']) <= 0){
	    $error['total_volume'] = 'Please enter a valid total volume';
	}
	if(floatval($in_process['acda_added']) <= 0){
	    $error['total_volume'] = 'Please enter a valid volume of ACD-A added';
	}
	if(floatval($in_process['tryvia']) <= 0){
	    $error['tryvia'] = 'Please enter a valid trypan blue viability';
	}
	
	if(count($in_process['selected_components']) == 0){
	    $error['selected_components'] = 'You must select at least one component to thaw';
	}
	else if(count($in_process['selected_components']) == 1){
	    $in_process['pool_number'] = 'NA';
	    var_dump(count($in_process['selected_components']));
	}
	else if(count($in_process['selected_components']) > 1){
	    if($in_process['pool_number'] == 'NA' || $in_process['pool_number'] == ''){
		$error['pool_number'] = 'You must enter a valid pool number when thawing multiple components';
	    }
	}
	
	if($in_process['sterility'] == 'NG' || $in_process['sterility'] == 'G'){
	    $in_process['sterility_state'] = 'FINAL';
	}
	else{
	    if(intval($in_process['sterility_state']) > 14 || intval($in_process['sterilit_state']) < 1){
		$error['sterility_state'] = 'Please enter a valid number of days pending sterility results';
	    }
	}
	
	if(empty($error)){
	    // if validated...
	    // add the array to the process object
	    $this->in_process = $in_process;
	}
    }
    
    public function update_product_derivatives(&$pdo)
    {
	if($this->in_process['pool_number'] == 'NA'){
	    $this->in_process['pool_number'] = null;
	}
	
	// update the status of the product_derivatives
	$sql = "UPDATE `product_derivatives`"
		. "SET "
		. "`status` = 'thawed and infused', `pool_number` = :pool_number, "
		. "`generated_by` = :generated_by "
		. "WHERE "
		. "`din` = :din AND `component_code` = :component_code";
	
	$statement = $pdo->prepare($sql);
	
	// perform the update on each selected_component
	foreach($this->in_process['selected_components'] as $component){
	    
	    if($statement->execute(array(':pool_number' => $this->in_process['pool_number'],
					 ':generated_by' => $this->in_process['process_id'],
					 ':din' => $this->in_process['din'],
				         ':component_code' => $component))){
		echo '<br />Product derivative status has been updated';
	    }
	    else{
		echo '<br />Failed to update product derivative status';
	    }
	}
	
	// update the number of derivatives in the product table
	$new_count = count($this->derivative_data) - count($this->in_process['selected_components']);
	if($new_count <= 0){
	    $new_count = null;
	}
	
	$sql = 'UPDATE `products` SET `derivatives` = :new_count WHERE `products`.`din` = :din';
	$statement = $pdo->prepare($sql);
	
	if($statement->execute(array(':new_count' => $new_count,
				     ':din' => $this->in_process['din']))){
	    echo '<br />Derivative count updated in the product table';
	}
	else{
	    echo '<br />Failed to update the derivative count in the product table';
	    var_dump($pdo->errorInfo());
	}
    }
    
    public function thaw_ln2($pdo)
    {
	// free up the LN2 locations of the thawed components
	$sql = "DELETE FROM `ln2_locations` "
		. "WHERE `din` = :din AND `component_code` = :component_code";
	
	$statement = $pdo->prepare($sql);
	
	// perform the deletion on each selected component
	foreach($this->in_process['selected_components'] as $component){
	    
	    if($statement->execute(array(':din' => $this->in_process['din'],
				         ':component_code' => $component))){
		echo '<br />Product derivative has been deleted from LN2 locations';
	    }
	}
    }
    
    public function write_thaw_to_db(&$pdo)
    {
	include '/../constants/constants.php';
	
	// change phase to complete pending review before attempting to write to DB... risky???
	$this->in_process['process_phase'] = 'complete pending review';
	
	// concatenate selected_components into thawed_components field
	$this->in_process['thawed_components'] = '';
	foreach($this->in_process['selected_components'] as $value){
	    $this->in_process['thawed_components'] .= $value . ' ';
	}
	
	$temp_write = $this->in_process;
	unset($temp_write['selected_components']);
	
	// format all DT values from read format to timestamps or write_dt formats
	foreach($temp_write as $key => $value){
	    
	    // if key is for dt field
	    if(strpos($key, 'dt')){
		
		//create new DateTime object using the value of said key
		$temp_dt = new DateTime($value, $dtz);
		$temp_write[$key] = $temp_dt->format($dt_format_write);
	    }
	}
	
	// prepare sql statement for insertion
	$sql = 'INSERT INTO `thaw`'
		. '(`process_id`, `din`, `process_name`, `process_phase`, `process_tech`, `thaw_buddy`, '
		. '`start_dt`, `acc_tryvia`, `acc_tpch`, `bsc_sn`, `in_range`, `clean_before`, `clean_before_datetime`, '
		. '`clean_after`, `clean_after_datetime`, `waterbath_by`, `pool_number`, '
		. '`sterility`, `sterility_state`, `retrieved_by`, `verified_by`, `total_volume`, `acda_added`, '
		. '`waterbath_dt`, `waterbath_temp`, `tryvia`, `flags`, `thawed_components`) '
		. 'VALUES '
		. '(:process_id, :din, :process_name, :process_phase, :process_tech, :thaw_buddy, '
		. ':start_dt, :acc_tryvia, :acc_tpch, :bsc_sn, :in_range, :clean_before, :clean_before_datetime, '
		. ':clean_after, :clean_after_datetime, :waterbath_by, :pool_number, '
		. ':sterility, :sterility_state, :retrieved_by, :verified_by, :total_volume, :acda_added, '
		. ':waterbath_dt, :waterbath_temp, :tryvia, :flags, :thawed_components)';
	
	$statement = $pdo->prepare($sql);
	
	echo '<br />';
	var_dump($temp_write);
	echo '<br />' . $sql;
	
	if($statement->execute($temp_write)){
	    echo '<br /><br />Thaw successfully added to DB';
	    // log activity  
	}
	else{
	    echo '<br /><br />Failed to add thaw to DB';
	    var_dump($statement->errorInfo());
	}
    }
}

// class for specific process
class Freeze extends Process
{
    public $freeze_id = null;
    
    // specific validation for the process at hand
    public function validate_in_process(&$in_process, &$error)
    {
	// validate accession numbers
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_pccd'])){
	    $error['acc_pccd'] = 'Invalid accession number';
	}
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_init'])){
	    $error['acc_pccd'] = 'Invalid accession number';
	}
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_prefr'])){
	    $error['acc_pccd'] = 'Invalid accession number';
	}
	if(!preg_match('/(M|T|W|H|F){1}[0-9]{6,}/', $in_process['acc_sccc'])){
	    $error['acc_pccd'] = 'Invalid accession number';
	}
	
	// validate key numbers and fields
	if(floatval($in_process['init_volume']) <= 0){
	    $error['init_volume'] = 'Please enter a valid starting volume';
	}
	if(floatval($in_process['init_sample_volume']) <= 0){
	    $error['init_sample_volume'] = 'Please enter a valid QC sampling volume';
	}
	if(floatval($in_process['post_express_volume']) <= 0){
	    $error['post_express_volume'] = 'Please enter a valid volume post-expression';
	}
	if(floatval($in_process['plyte_added']) < 0){
	    $error['plyte_added'] = 'You cannot add a negative amount of plasmalyte';
	}
	if(floatval($in_process['prefr_volume']) <= 0){
	    $error['prefr_volume'] = 'Please enter a valid pre-freeze volume';
	}
	if(floatval($in_process['prefr_sample_volume']) <= 0){
	    $error['prefr_volume'] = 'Please enter a valid QC sampling volume';
	}
	
	// calculate expressed volume after verifying numbers
	$in_process['expressed_volume'] = strval((float)$in_process['init_volume'] - (float)$in_process['post_express_volume']);
	
	if(empty($error)){
	    // if validated...
	    // add the array to the process object
	    $this->in_process = $in_process;
	}
    }
    
    public function write_freeze_to_db(&$pdo)
    {
	include '/../constants/constants.php';
	
	// change phase to post-process before attempting to write to DB... risky???
	$this->in_process['process_phase'] = 'post-process';
	
	// format all DT values from read format to timestamps or write_dt formats
	foreach($this->in_process as $key => $value){
	    
	    // if key is for dt field
	    if(strpos($key, 'dt')){
		
		//create new DateTime object using the value of said key
		$temp_dt = new DateTime($value, $dtz);
		$this->in_process[$key] = $temp_dt->format($dt_format_write);
	    }
	}
	
	// prepare sql statement for insertion
	$sql = 'INSERT INTO `freeze`'
		. '(`process_id`, `din`, `process_name`, `process_phase`, '
		. '`process_tech`, `start_dt`, `acc_pccd`, `acc_init`, `acc_prefr`, `acc_sccc`, '
		. '`bsc_sn`, `in_range`, `clean_before`, `clean_before_datetime`, `clean_after`, `clean_after_datetime`, '
		. '`centrifuge`, `refrigerator`, `scale_sn`, `tube_welder_sn`, `init_volume`, `weighed_by`, `init_volume_dt`, '
		. '`acda_volume`, `other_anticoagulants`, `init_sampled_by`, `init_sample_dt`, `init_sample_volume`, '
		. '`centrifuged_by`, `centrifuged_dt`, `expressed_by`, `expressed_dt`, `post_express_volume`, '
		. '`expressed_volume`, `plyte_added`, `plyte_added_by`, `plyte_added_dt`, `prefr_volume`, '
		. '`freeze_solution_by`, `freeze_solution_dt`, `freeze_solution_ver`, `prefr_sampled_by`, `prefr_sample_dt`, '
		. '`prefr_sample_volume`, `drip_by`, `drip_begin_dt`, `drip_end_dt`, `init_tnc`, `init_tnckg`, `init_cd34`, '
		. '`init_cd34kg`, `init_calc_id`, `aliquots`, `aliquot_volume`, `flags`) '
		. 'VALUES '
		. '(:process_id, :din, :process_name, :process_phase,'
		. ':process_tech, :start_dt, :acc_pccd, :acc_init, :acc_prefr, :acc_sccc, '
		. ':bsc_sn, :in_range, :clean_before, :clean_before_datetime, :clean_after, :clean_after_datetime, '
		. ':centrifuge, :refrigerator, :scale_sn, :tube_welder_sn, :init_volume, :weighed_by, :init_volume_dt, '
		. ':acda_volume, :other_anticoagulants, :init_sampled_by, :init_sample_dt, :init_sample_volume, '
		. ':centrifuged_by, :centrifuged_dt, :expressed_by, :expressed_dt, :post_express_volume, '
		. ':expressed_volume, :plyte_added, :plyte_added_by, :plyte_added_dt, :prefr_volume, '
		. ':freeze_solution_by, :freeze_solution_dt, :freeze_solution_ver, :prefr_sampled_by, :prefr_sample_dt, '
		. ':prefr_sample_volume, :drip_by, :drip_begin_dt, :drip_end_dt, :init_tnc, :init_tnckg, :init_cd34, '
		. ':init_cd34kg, :init_calc_id, :aliquots, :aliquot_volume, :flags)';
	
	$statement = $pdo->prepare($sql);
	
	if($statement->execute($this->in_process)){
	    echo '<br /><br />Freeze successfully added to DB';
	    echo '<br />Remember to update the freeze with pre-freeze counts and LN2 locations';
	    // log activity
	}
	else{
	    echo '<br /><br />Failed to add freeze to DB';
	    var_dump($statement->errorInfo());
	}
	
	// store the freeze id, can use to proceed straight to post-process 
	// info without the need to reload data if session is intact
	$this->freeze_id = $pdo->lastInsertId();
    }
    
    public function update_product_derivatives(&$pdo)
    {
	$sql = 'UPDATE `products` '
		. 'SET `derivatives` = :aliquots '
		. 'WHERE `products`.`din` = :din';
	
	$statement = $pdo->prepare($sql);
	
	if($statement->execute(array
		(':aliquots' => $this->in_process['aliquots'],
		 ':din' => $this->in_process['din'])))
	{
	    echo '<br /> Successfully updated parent products derivative count';
	}	
    }
    
    public function validate_post_process(&$post_process, &$error)
    {
	include '/../constants/constants.php';
	
	// validate all datetime values
	foreach($post_process as $key => $value){
	    
	    // if key is for datetime field
	    if(strpos($key, 'datetime')){
		
		if($value == 0){
		    $error[$key] = 'Please enter a valid date and time';
		}
		
		//create new DateTime object using the value of said key
		$temp_dt = new DateTime($value, $dtz);
		$temp_ts = $temp_dt->getTimestamp();
		
		if($temp_ts > time()){
		    $error[$key] = 'Please enter a valid date and time';
		}
	    }
	}

	if(empty($error)){
	    // if validated...
	    // add the array to the process object
	    $this->post = $post_process;
	}
    }
    
    public function update_freeze(&$pdo)
    {
	include '/../constants/constants.php';
    
	// format datetime values for write to DB
	foreach($this->post as $key => $value){
	    
	    // if key is for datetime field
	    if(strpos($key, 'datetime')){
		
		//create new DateTime object using the value of said key
		$temp_dt = new DateTime($value, $dtz);
		$this->post[$key] = $temp_dt->format($dt_format_write);
	    }
	}
	
	$sql = 'UPDATE `freeze` SET '
		. '`kryo_10`=:kryo_10, `kryo_10_by`=:kryo_10_by, `kryo_10_datetime`=:kryo_10_datetime, '
		. '`products_kryo_by`=:products_kryo_by, `products_kryo_datetime`=:products_kryo_datetime, '
		. '`products_ln2_by`= :products_ln2_by,`products_ln2_datetime`= :products_ln2_datetime, '
		. '`ln2_chart_ver`= :ln2_chart_ver,`prefr_tnc`= :prefr_tnc,`prefr_tnckg`= :prefr_tnckg, '
		. '`prefr_cd34`= :prefr_cd34,`prefr_cd34kg`= :prefr_cd34kg,`prefr_calc_id`= :prefr_calc_id '
		. 'WHERE `freeze`.`freeze_id` = ' . $this->attributes['freeze_id'];
	
	$statement = $pdo->prepare($sql);
	
	if($statement->execute($this->post)){
	    echo '<br /> Freeze successfully updated in DB';
	    return true;
	}
	else{
	    echo '<br /> Unable to update freeze in DB';
	    var_dump($statement->errorInfo());
	    return false;
	}
    }
    
    public function create_derivatives(&$pdo)
    {
	include '/../constants/constants.php';
	
	$n = intval($this->attributes['aliquots']);
	
	// populate a temp_write array to insert new product_derivatives prior to post-freeze
	$temp_write = array();
	$temp_write['din'] = $this->attributes['din'];
	$temp_write['generated_by'] = $this->attributes['process_id'];
	$temp_write['status'] = '-150 storage';
	$temp_write['component_code'] = $add_process_consts['component_codes'][0];
	$temp_write['volume'] = $this->attributes['aliquot_volume'];
	$temp_write['tnc'] = strval(floatval($this->post['prefr_tnc'] / $n));
	$temp_write['tnckg'] = strval(floatval($this->post['prefr_tnckg'] / $n));
	$temp_write['cd34'] = strval(floatval($this->post['prefr_cd34'] / $n));
	$temp_write['cd34kg'] = strval(floatval($this->post['prefr_cd34kg'] / $n));
	
	$sql = 'INSERT INTO `product_derivatives` '
		. '(`din`, `generated_by`, `status`, `component_code`, `volume`, `tnc`, `tnckg`, `cd34`, `cd34kg`) '
		. 'VALUES '
		. '(:din, :generated_by, :status, :component_code, :volume, :tnc, :tnckg, :cd34, :cd34kg)';
	
	$statement = $pdo->prepare($sql);
	
	for($i = 0; $i < $n; $i++){
	    
	    $temp_write['component_code'] = $add_process_consts['component_codes'][$i];
	    if(!$statement->execute($temp_write)){
		echo '<br /> Unable to add derivative products to DB';
		 var_dump($statement->errorInfo());
		return false;
	    }
	}

	echo '<br /> Derivative products successfully added to DB';
	return true;
    }
    
    public function freeze_pending_review(&$pdo)
    {
	 $sql = 'UPDATE `freeze` '
		. 'SET `process_phase` = "complete pending review" '
		. 'WHERE `freeze`.`freeze_id` = ' . $this->attributes['freeze_id'];
	

	$statement = $pdo->prepare($sql);
	if($statement->execute()){
	    echo '<br /><br /> Freeze completed and process phase updated. Now pending review';
	}
	else{
	    echo 'huh???';
	}
    }
}





