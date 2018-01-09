<?php

include '/../constants/constants.php';
include '/../libraries/controller.php'; 

// resume session and validate it
session_start();
//var_dump($_SESSION);

// redirect if session not valid
if(!$_SESSION['valid']){
    header('Location: http://localhost/CTS/');
}

// flag for valid patient to add
$add_patient_valid = false;

// VALIDATION!!!
if(isset($_POST['submit-patient'])){

    // first, filter all variables and store in patent_attributes array
    if(isset($_POST['add_patient']) && is_array($_POST['add_patient']) && count($_POST['add_patient'] > 0)){

	foreach($_POST['add_patient'] as $key => $value){
	    $patient_attributes[$key] = filter_var($value);
	}

	$error = array();

	// Pass $patient_attributes and $error by reference to add_patient_validate();
	add_patient_validate($patient_attributes, $error);

	echo '<br/><br/>';
	//var_dump($patient_attributes);
	echo '<br/><br/>';
	//var_dump($error);

	// Check error array
	if(empty($error)){
	    echo '<br />SUCCESS!!!!';
	    $add_patient_valid = true;
	}
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Add a Patient </title>
    </head>
    <body>
        <div class="patients-add">
            <h1 style="align-content: center"> Add a Patient </h1>
            <div class="patients-nav">
		<li style="display: inline-block"><a href="index.php">Patients Index</a></li>
                <li style="display: inline-block"><a href="view_patient.php">View Patients</a></li>
                <li style="display: inline-block"><a href="add_patient.php">Add New Patient</a></li>
                <li style="display: inline-block"><a href="update_patient.php">Update Patient</a></li>
                <li style="display: inline-block"><a href="delete_patient.php">Delete Product</a></li>
            </div><hr style="width:1368px"/>
            <div class="patients-add-form">
                <form method="post" action="#">
                    <br /><br />First Name: 
                    <input type="text" name="add_patient[first_name]" 
			value="<?php echo isset($patient_attributes['first_name']) ? $patient_attributes['first_name'] : '' ?>"/>
                    <?php if(isset($error['first_name'])) {echo $error['first_name'];} ?>
                    <br /><br />Last Name:
                    <input type="text" name="add_patient[last_name]"
			value="<?php echo isset($patient_attributes['last_name']) ? $patient_attributes['last_name'] : '' ?>"/>
                    <?php if(isset($error['last_name'])) {echo $error['last_name'];} ?>
                    <br /><br />DF MRN:
                    <input type="text" name="add_patient[df_mrn]" maxlength="6"
			value="<?php echo isset($patient_attributes['df_mrn']) ? $patient_attributes['df_mrn'] : '' ?>"/>
                    <?php if(isset($error['df_mrn'])) {echo $error['df_mrn'];} ?>
                    <br /><br />Other MRN:
                    <input type="text" name="add_patient[other_mrn]" maxlength="16"
			value="<?php echo isset($patient_attributes['other_mrn']) ? $patient_attributes['other_mrn'] : '' ?>"/>
                    <?php if(isset($error['other_mrn'])) {echo $error['other_mrn'];} ?>
                    <br /><br />NMDP ID:
                    <input type="text" name="add_patient[nmdp_id]" maxlength="16"
			value="<?php echo isset($patient_attributes['nmdp_id']) ? $patient_attributes['nmdp_id'] : '' ?>"/>
                    <?php if(isset($error['nmdp_id'])) {echo $error['nmdp_id'];} ?>
                    <br /><br />DOB:
                    <input type="date" name="add_patient[dob]"
			value="<?php echo isset($patient_attributes['dob']) ? $patient_attributes['dob'] : '' ?>"/>
                    <?php if(isset($error['dob'])) {echo $error['dob'];} ?>
                    <br /><br />Treatment Plan/Protocol:
                    <input type="text" name="add_patient[tp]"
			value="<?php echo isset($patient_attributes['tp']) ? $patient_attributes['tp'] : '' ?>"/>
                    <?php if(isset($error['tp'])) {echo $error['tp'];} ?>
                    <br /><br />ABO:
                    <select name="add_patient[abo]">
                    <?php foreach($add_patient_consts['ABO'] as $option){
                        echo ($patient_attributes['abo'] == $option) ? 
			'<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>';
                    } ?>
                    </select>
                    <br /><br />Rh:
                    <select name="add_patient[rh]">
                    <?php foreach($add_patient_consts['Rh'] as $option){
                        echo ($patient_attributes['rh'] == $option) ?
			'<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>';
                    } ?>
                    </select>
                    
                    <br /><br /><input type="submit" name="submit-patient" value="Add New Patient"/>
                    &nbsp;<input type="reset" name="reset-patient" value="Reset"/>
                </form>
            </div>
        </div>
    </body>
</html>

<?php

// if the add patient data is valid
if($add_patient_valid){
    
    // connect to DB
    // validate the connection
    // add patient to db
    $pdo = connect();
    
    if($pdo){
        // pass $pdo and $patient_attributes by reference to helper function
        add_patient_to_DB($pdo, $patient_attributes);
    }
    else{
        echo '<br /><br />Failed to connect to DB';
    }
    
    // return to patient index
    echo '<br /><br /><a href="./">Patient Index</a>';
}