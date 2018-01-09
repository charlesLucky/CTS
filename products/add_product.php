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

// flag for valid product to add
$add_product_valid = false;

// VALIDATION!!!!
if(isset($_POST['submit-product'])){

    // first, filter all variables and store in product_attributes array
    if(isset($_POST['add_product']) && is_array($_POST['add_product']) && count($_POST['add_product']) > 0){

	foreach($_POST['add_product'] as $key => $value){
	    if(!is_array($value)){
		$product_attributes[$key] = filter_var($value); 
	    }
	    else{
		$product_attributes[$key] = filter_array($value);
	    }
	}

	$error = array();

	// pass $product_attributes and $error by reference to add_product_validate()
	add_product_validate($product_attributes, $error);

	echo '<br /><br />';
	//var_dump($product_attributes);
	echo '<br /><br />';
	//var_dump($error);

	// Check error array
	if(empty($error)){
	    echo '<br />SUCCESS!!!!!';
	    $add_product_valid = true;
	}
	echo '<br/><br/>';
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Add a Product </title>
    </head>
    <body>
        <div class="products-add">
            <h1 style="align-content: center"> Add a Product </h1>
            <div class="products-nav">
                <li style="display: inline-block"><a href="index.php">Products Index</a></li>		
                <li style="display: inline-block"><a href="view_product.php">View Products</a></li>
                <li style="display: inline-block"><a href="add_product.php">Add New Product</a></li>
                <li style="display: inline-block"><a href="update_product.php">Update Products</a></li>
                <li style="display: inline-block"><a href="delete_product.php">Delete Products</a></li>
            </div><hr style="width:1368px"/>
            <div class="products-add-form">
                <form method="post" action="#">
                    <br /><br />DIN: 
                    <input type="text" name="add_product[din]"
			value="<?php echo isset($product_attributes['din']) ? $product_attributes['din'] : '' ?>"/>
                    <?php if(isset($error['din'])) {echo $error['din'];} ?>
                    <br /><br />Product Name: 
                    <select name="add_product[name]">
                    <?php foreach($add_product_consts['name'] as $option){
			echo ($product_attributes['name'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>';
                    } ?>
                    </select>
                    <br /><br />Product Type: 
                    <select name="add_product[type]">
                    <?php foreach($add_product_consts['type'] as $option){
                        echo ($product_attributes['type'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>'; 
                    } ?>
                    </select>
                    <br /><br />Product ABO: 
                    <select name="add_product[abo]">
                    <?php foreach($add_product_consts['ABO'] as $option){
                        echo ($product_attributes['abo'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>';
                    } ?>
                    </select>
                    <br /><br />Product Rh: 
                    <select name="add_product[rh]">
                    <?php foreach($add_product_consts['Rh'] as $option){
                        echo ($product_attributes['rh'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>'; 
                    } ?>     
                    </select>
                    <br /><br />Receipt Status: 
                    <select name="add_product[receipt_status]">
                    <?php foreach($add_product_consts['receipt_status'] as $option){
                        echo ($product_attributes['receipt_status'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>'; 
                    } ?>    
                    </select>
                    <br /><br />Donor IDM Date:
                    <input type="date" name="add_product[idm_date]"
			value="<?php echo isset($product_attributes['idm_date']) ? $product_attributes['idm_date'] : '' ?>"/>
                    <br /><br />Biohazard Tags:
                    1<input type="checkbox"  name="add_product[biohazard_tags][]" value="1"/>
                    2<input type="checkbox"  name="add_product[biohazard_tags][]" value="2"/>
                    3<input type="checkbox"  name="add_product[biohazard_tags][]" value="3"/>
                    <br /><br />Collection Site:
                    <select name="add_product[collection_site]">
                    <?php foreach($add_product_consts['collection_site'] as $option){
                        echo ($product_attributes['collection_site'] == $option) ?
                        '<option value="' . $option . '" selected="true">' . $option . '</option>' :
			'<option value="' . $option . '">' . $option . '</option>';
                    } ?>   
                    </select>
                    <br /><br />Collection Date/Time:
                    <input type="datetime-local" name="add_product[collection_datetime]"
			value="<?php echo isset($product_attributes['collection_datetime']) ? $product_attributes['collection_datetime'] : '' ?>"/>
                    <?php if(isset($error['collection_datetime'])) {echo $error['collection_datetime'];} ?>
                    <br /><br />Receipt Date/Time:
                    <input type="datetime-local" name="add_product[receipt_datetime]"
			   value="<?php echo isset($product_attributes['receipt_datetime']) ? $product_attributes['receipt_datetime'] : '' ?>"/>
                    <?php if(isset($error['receipt_datetime'])) {echo $error['receipt_datetime'];} ?>
                    <br /><br />Donor Name:
                    <input type="text" name="add_product[donor_name]"
			value="<?php echo isset($product_attributes['donor_name']) ? $product_attributes['donor_name'] : '' ?>"/>
                    <?php if(isset($error['donor_name'])) {echo $error['donor_name'];} ?>
                    <br /><br />Donor DF MRN:
                    <input type="text" name="add_product[donor_df_mrn]" maxlength="6"
			value="<?php echo isset($product_attributes['donor_df_mrn']) ? $product_attributes['donor_df_mrn'] : '' ?>"/>
                    <?php if(isset($error['donor_df_mrn'])) {echo $error['donor_df_mrn'];} ?>
                    <br /><br />Donor Other MRN:
                    <input type="text" name="add_product[donor_other_mrn]" maxlength="16"
			value="<?php echo isset($product_attributes['donor_other_mrn']) ? $product_attributes['donor_other_mrn'] : '' ?>"/>
                    <?php if(isset($error['donor_other_mrn'])) {echo $error['donor_other_mrn'];} ?>
                    <br /><br />Donor NMDP ID:
                    <input type="text" name="add_product[donor_nmdp_id]" maxlength="16"
			value="<?php echo isset($product_attributes['donor_nmdp_id']) ? $product_attributes['donor_nmdp_id'] : '' ?>"/>
                    <?php if(isset($error['donor_nmdp_id'])) {echo $error['donor_nmdp_id'];} ?>
                    <br /><br />Recipient Name:
                    <input type="text" name="add_product[recipient_name]"
			value="<?php echo isset($product_attributes['recipient_name']) ? $product_attributes['recipient_name'] : '' ?>"/>
                    <?php if(isset($error['recipient_name'])) {echo $error['recipient_name'];} ?>
                    <br /><br />Recipient DF MRN:
                    <input type="text" name="add_product[recipient_df_mrn]" maxlength="6"
			value="<?php echo isset($product_attributes['recipient_df_mrn']) ? $product_attributes['recipient_df_mrn'] : '' ?>"/>
                    <?php if(isset($error['recipient_df_mrn'])) {echo $error['recipient_df_mrn'];} ?>
                    <br /><br />Recipient Other MRN:
                    <input type="text" name="add_product[recipient_other_mrn]" maxlength="16"
			value="<?php echo isset($product_attributes['recipient_other_mrn']) ? $product_attributes['recipient_other_mrn'] : '' ?>"/>
                    <?php if(isset($error['recipient_other_mrn'])) {echo $error['recipient_other_mrn'];} ?>
                    <br /><br />Recipient NMDP ID:
                    <input type="text" name="add_product[recipient_nmdp_id]" maxlength="16"
			value="<?php echo isset($product_attributes['recipient_nmdp_id']) ? $product_attributes['recipient_nmdp_id'] : '' ?>"/>
                    <?php if(isset($error['recipient_nmdp_id'])) {echo $error['recipient_nmdp_id'];} ?>
		    <br /><br />Recipient Weight (kg):
		    <input type="text" name="add_product[recipient_weight]"
			value="<?php echo isset($product_attributes['recipient_weight']) ? $product_attributes['recipient_weight'] : '' ?>"/>
		    <?php if(isset($error['recipient_weight'])) {echo $error['recipient_weight'];} ?>
		   
                    
                    <br /><br /><input type="submit" name="submit-product" value="Add New Product"/>
                    &nbsp;<input type="reset" name="reset-product" value="Reset"/>
                </form>
            </div>
        </div>
    </body>
</html>

<?php

// if the add product data is valid
if($add_product_valid){
    
    // connect to DB, validate the connection
    // validate recipient is in the patient table
    // add product to db
    $pdo = connect();

    if($pdo){
        // pass $pdo and $product_attributes and $valid_recipient by reference to helper function
        add_product_to_DB($pdo, $product_attributes);
    }
    else{
        echo '<br /><br />Failed to connect to DB';
    }
    
    // return to product index
    echo '<br /><br /><a href="./">Product Index</a>';
}

//phpinfo(INFO_VARIABLES);

?>


