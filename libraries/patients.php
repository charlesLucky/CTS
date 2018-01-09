<?php

// used in add_patient.php to validate input data and set error array
function add_patient_validate(&$patient_attributes, &$error)
{
    include '/../constants/constants.php';
    
    // Validate name
    if(!is_string($patient_attributes['first_name']) || !(strlen($patient_attributes['first_name']) > 0)){
        $error['first_name'] = 'Please enter valid first name';
    }

    if(!is_string($patient_attributes['last_name']) || !(strlen($patient_attributes['last_name']) > 0)){
        $error['last_name'] = 'Please enter valid last name';
    }

    // Validate MRNs
    if(!is_string($patient_attributes['df_mrn']) || strlen($patient_attributes['df_mrn']) != 6){
        $error['df_mrn'] = 'Please enter a valid DF MRN';
    }

    if(!is_string($patient_attributes['other_mrn']) || strlen($patient_attributes['other_mrn']) < 10){
        $error['other_mrn'] = 'Please enter a valid MRN';
    }

    if(!is_string($patient_attributes['nmdp_id']) || strlen($patient_attributes['nmdp_id']) == 0){
        $error['nmdp_id'] = 'Please enter a valid NMDP ID, or enter NA';
    }

    // Validate DOB and format for insertion into DB
    $temp_dt = new DateTime($patient_attributes['dob'], $dtz);
    $temp_ts = $temp_dt->getTimestamp();
    if($temp_ts < time()){
        $patient_attributes['dob'] = $temp_dt->format('Y-m-d');
    }
    else{
        $error['dob'] = 'Please enter a valid DOB';
    }

    // Validate TP/protocol
    if(!is_string($patient_attributes['tp']) || !(strlen($patient_attributes['tp']) > 5)){
        $error['tp'] = 'Please enter a valid treatment plan/protocol';
    }
}

function add_patient_to_DB(&$pdo, &$patient_attributes)
{
    if($patient_attributes['nmdp_id'] == 'NA'){
        $patient_attributes['nmdp_id'] = null;
    }
    
    $sql = 'INSERT INTO `patients`
                (`first_name`, `last_name`, `df_mrn`, `other_mrn`, `nmdp_id`, `dob`, `tp`, `abo`, `rh`) 
                VALUES 
                (:first_name, :last_name, :df_mrn, :other_mrn, :nmdp_id, :dob, :tp, :abo, :rh);';
        
    $statement = $pdo->prepare($sql);
    if($statement->execute($patient_attributes)){
        echo '<br /><br />Patient successfully added to DB';
        // log activity
    }
    else{
        echo '<br /><br />Failed to add patient to DB';
    }
    var_dump($statement->errorInfo());
}


// The Patient object is instantiated with or without a DF MRN,
// once instantiated, the product object can fill its $attributes array
// using a connection to SQL. This object is NOT used to add new patients,
// but is used to view and update existing patients.
class Patient
{
    public $df_mrn = '';
    public $attributes = array();
    
    public function __construct($df_mrn = '')
    {
        $this->df_mrn = $df_mrn;
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

