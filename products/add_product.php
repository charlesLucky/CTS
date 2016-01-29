<?php

include '/../constants/const_products.php';

session_start();

if(isset($_POST['submit-product'])){
    
    
    
    var_dump($_POST);
    
}

phpinfo(INFO_VARIABLES);


?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Welcome to CTS </title>
    </head>
    <body>
        <div class="products-add">
            <h1 style="align-content: center"> Add a Product </h1>
            <div class="products-nav">
                <ul> Products Menu:
                    <li><a href="view_product.php">View Products</a></li>
                    <li><a href="add_product.php">Add New Product</a></li>
                    <li><a href="update_product.php">Update Products</a></li>
                    <li><a href="delete_product.php">Delete Products</a></li>
                </ul>
            </div>
            <br /><hr />
            <div class="products-add-form">
                <form method="post" action="#">
                    DIN: <input type="text" name="add_product[din]" value="<?php echo isset($add_product['din']) ? $add_product['din'] : '' ?>" />
                    <br /><br />Product Name: 
                    <select name="add_product[name]">
                    <?php foreach($add_product_consts['name'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>';
                    }
                    ?>
                    </select>
                    <br /><br />Product Type: 
                    <select name="add_product[type]">
                    <?php foreach($add_product_consts['type'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>'; 
                    }
                    ?>
                    </select>
                    <br /><br />Product ABO: 
                    <select name="add_product[ABO]">
                    <?php foreach($add_product_consts['ABO'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>'; 
                    }
                    ?>
                    </select>
                    <br /><br />Product Rh: 
                    <select name="add_product[Rh]">
                    <?php foreach($add_product_consts['Rh'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>'; 
                    }    
                    ?>
                    </select>
                    <br /><br />Status: 
                    <select name="add_product[status]">
                    <?php foreach($add_product_consts['status'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>'; 
                    }    
                    ?>
                    </select>
                    <br /><br />Donor IDM Date:
                    <input type="date" name="add_product[idm_date]"/>
                    <br /><br />Biohazard Tags:
                    1<input type="checkbox"  name="add_product[biohazard_tags][]" value="1"/>
                    2<input type="checkbox"  name="add_product[biohazard_tags][]" value="2"/>
                    3<input type="checkbox"  name="add_product[biohazard_tags][]" value="3"/>
                    <br /><br />Collection Site:
                    <select name="add_product[collection_site]">
                    <?php foreach($add_product_consts['collection_site'] as $option){
                        echo '<option value="' . $option . '">' . $option . '</option>'; 
                    }    
                    ?>
                    </select>
                    <br /><br />Collection Date/Time:
                    <input type="datetime" name="add_product[collection_datetime]"/>
                    <br /><br />Receipt Date/Time:
                    <input type="datetime" name="add_product[receipt_datetime]"/>
                    
                    <br /><br /><input type="submit" name="submit-product" value="Add New Product"/>
                </form>
            </div>
        </div>
        
    </body>
</html>


