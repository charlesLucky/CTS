<?php

function add_product_validate(&$product_attributes, &$error)
{
    include '/../constants/constants.php';
    
    // validate DIN
    if(!preg_match('/=[A-Z]{1}[0-9]{14}/', $product_attributes['din'])){
        $error['din'] = 'Invalid DIN';
    }

    // if IDMs are not available, set IDM_date field to null and set biohazard tag to 1
    if($product_attributes['idm_date'] == ''){
        $product_attributes['idm_date'] = null;
        $product_attributes['biohazard_tags'] = '1';
    }
    // ELSE SET biohazard tags based on the check boxes
    else{
        if(isset($product_attributes['biohazard_tags'])){
            $tags = '';
            foreach($product_attributes['biohazard_tags'] as $tag){
                $tags .= $tag . ' ';
            }
            $product_attributes['biohazard_tags'] = $tags;
        }
        else{
            $product_attributes['biohazard_tags'] = null;
        }
    }

    // validate collection and receipt datetimes
    // format date for insertion into DB
    $coll_dt = new DateTime($product_attributes['collection_datetime'], $dtz);
    $coll_ts = $coll_dt->getTimestamp();
    if($coll_ts > time() || strlen($product_attributes['collection_datetime']) == 0){
	$error['collection_datetime'] = 'Please enter a valid collection date/time';
    }
    
    $recd_dt = new DateTime($product_attributes['receipt_datetime'], $dtz);
    $recd_ts = $recd_dt->getTimestamp();
    if($recd_ts < $coll_ts || $recd_ts > time()){
	$error['receipt_datetime'] = 'Please enter a valid receipt date/time';
    }

    // validate recipient info first
    if(!is_string($product_attributes['recipient_name']) || strlen($product_attributes['recipient_name']) == 0){
        $error['recipient_name'] = 'Please enter a valid recipient name';
    }

    if(!is_string($product_attributes['recipient_df_mrn']) || strlen($product_attributes['recipient_df_mrn']) != 6){
        $error['recipient_df_mrn'] = 'Please enter a valid recipient DF MRN';
    }

    if(!is_string($product_attributes['recipient_other_mrn']) || strlen($product_attributes['recipient_other_mrn']) < 10){
        $error['recipient_other_mrn'] = 'Please enter a valid recipient other MRN';
    }
    
    if(!is_string($product_attributes['recipient_nmdp_id']) || strlen($product_attributes['recipient_nmdp_id']) == 0){
        $error['recipient_nmdp_id'] = 'Please enter a valid recipient NMDP ID or NA';
    }
    
    if(!is_string($product_attributes['recipient_weight']) || strlen($product_attributes['recipient_weight']) == 0){
	$error['recipient_weight'] = 'Please enter a valid recipient weight, if weight is unknown, enter NA and update later';
    }

    // SET donor and recipient info equal IF type == AUTO
    if($product_attributes['type'] == 'AUTO'){
        $product_attributes['donor_name'] = $product_attributes['recipient_name'];
        $product_attributes['donor_df_mrn'] = $product_attributes['recipient_df_mrn'];
        $product_attributes['donor_other_mrn'] = $product_attributes['recipient_other_mrn'];
        $product_attributes['donor_nmdp_id'] = $product_attributes['recipient_nmdp_id'];
    }
    // In the ALLO case, set donor name, df mrn, other mrn, nmdp id to null if NA
    else if($product_attributes['type'] == 'ALLO'){

        if($product_attributes['donor_name'] == 'NA'){
            $product_attributes['donor_name'] = null;
        }
        else if(!is_string($product_attributes['donor_name'])){
            $error['donor_name'] = 'Please enter a valid donor name';
        }

        if($product_attributes['donor_df_mrn'] == 'NA'){
            $product_attributes['donor_df_mrn'] = null;
        }
        else if(!is_string($product_attributes['donor_name'])){
            $error['donor_df_mrn'] = 'Please enter a valid donor DF MRN';
        }

        if($product_attributes['donor_other_mrn'] == 'NA'){
            $product_attributes['donor_other_mrn'] = null;
        }
        else if(!is_string($product_attributes['donor_other_mrn']) || strlen($product_attributes['donor_other_mrn']) < 10){
            $error['donor_other_mrn'] = 'Please enter a valid other MRN';
        }

        if($product_attributes['donor_nmdp_id'] == 'NA'){
            $product_attributes['donor_nmdp_id'] = null;
        }
    }
}

function add_product_to_DB(&$pdo, &$product_attributes)
{
    include '/../constants/constants.php';
    // flag for recipient lookup
    $valid_recipient = false;
    
    // first, check if recipient is in the patients table, WARNING if not
    $check = 'SELECT `first_name`, `last_name`, `df_mrn`, `other_mrn`, `nmdp_id` FROM `patients`
              WHERE `df_mrn` = :df_mrn';
    
    $check_patient = $pdo->prepare($check);
    
    if($check_patient->execute(array(':df_mrn' => $product_attributes['recipient_df_mrn']))){

        // fetch results and format name to match
        $results = $check_patient->fetch(PDO::FETCH_ASSOC);
        $name = $results['last_name'] . ', ' . $results['first_name'];
	if($product_attributes['recipient_nmdp_id'] == 'NA'){
	    $product_attributes['recipient_nmdp_id'] = null;
	}
	if($product_attributes['type'] == 'AUTO'){
	    $product_attributes['donor_nmdp_id'] = $product_attributes['recipient_nmdp_id'];
	}

        // validate that name, DF MRN, and other MRN all match
        if($name == $product_attributes['recipient_name'] &&
           $results['df_mrn'] == $product_attributes['recipient_df_mrn'] &&
           $results['other_mrn'] == $product_attributes['recipient_other_mrn'] &&
           $results['nmdp_id'] == $product_attributes['recipient_nmdp_id'])
        {
            echo '<br />The recipient was found in the database and all fields match, proceeding to add product to DB';
            $valid_recipient = true;
        }
        else{
            echo '<br />WARNING: The recipient was found but some fields do not match';
        }
    }
    else{
        echo '<br />WARNING: The recipient of the product you are trying to add could not be found in the database';
    }
    var_dump($check_patient->errorInfo());

    // if recipient is found and all fields match, ready to insert
    if($valid_recipient){
	
	// format DIN from scanner
	$product_attributes['din'] = substr($product_attributes['din'], 1, 13);
	
	// format datetime for insertion into DB
	$coll_dt = new DateTime($product_attributes['collection_datetime'], $dtz);
	$product_attributes['collection_datetime'] = $coll_dt->format($dt_format_write);
		
	if($product_attributes['receipt_datetime'] == ''){
	    $product_attributes['receipt_datetime'] = null;
	}
	else{
	    $recd_dt = new DateTime($product_attributes['receipt_datetime'], $dtz);
	    $product_attributes['receipt_datetime'] = $recd_dt->format($dt_format_write);
	}
   
	// if NA, set to null before insertion into DB
	if($product_attributes['recipient_nmdp_id'] == 'NA'){
	    $product_attributes['recipient_nmdp_id'] = null;
	}

        $sql = 'INSERT INTO `products`
                (`din`, `name`, `type`, `abo`, `rh`, `receipt_status`, `idm_date`, `biohazard_tags`, `collection_site`, 
                `collection_datetime`, `receipt_datetime`, `donor_name`, `donor_df_mrn`, `donor_other_mrn`, `donor_nmdp_id`, 
                `recipient_name`, `recipient_df_mrn`, `recipient_other_mrn`, `recipient_nmdp_id`, `recipient_weight`) 
                VALUES 
                (:din, :name, :type, :abo, :rh, :receipt_status, :idm_date, :biohazard_tags, :collection_site, 
                :collection_datetime, :receipt_datetime, :donor_name, :donor_df_mrn, :donor_other_mrn, :donor_nmdp_id,
                :recipient_name, :recipient_df_mrn, :recipient_other_mrn, :recipient_nmdp_id, :recipient_weight)';

        $statement = $pdo->prepare($sql);

        var_dump($product_attributes);
        if($statement->execute($product_attributes)){
            echo '<br /><br />Product successfully added to DB';
            // log activity

        }
        else{
            echo '<br /><br />Failed to add product to DB';
        }
        var_dump($statement->errorInfo());
    }
}


// The Product object is instantiated with or without a DIN,
// once instantiated, the product object can fill its $attributes array
// using a connection to SQL. This object is NOT used to add new products,
// but is used to view and update existing products.
// Additionally, a product object will be passed to a Process object
// to initiate a process and populate some fields.
class Product
{
    public $din = '';
    public $attributes = array();
    
    public function __construct($_din = '')
    {
        $this->din = $_din;
    }
    
    public function set_attributes($attributes_array)
    {
        if(is_array($attributes_array) && count($attributes_array) > 0){
            $this->attributes = $attributes_array;
        }
    }
    
    public function get_attributes()
    {
        return $this->attributes;
    } 
}

