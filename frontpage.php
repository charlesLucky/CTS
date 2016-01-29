<?php

// resume session
session_start();

// ensure the session was valid and that login credentials were good
// prevents people from accessing this page straight from URL
if($_SESSION['valid']){
    echo 'this be the front page after successful login <br />';
    echo 'session continued <br />';
}
// otherwise, redirect to login page
else{
    header('Location: http://localhost/CTS/');
}


?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> Welcome to CTS </title>
    </head>
    <body>
        <div class="frontpage-landing">
            <h1 style="align-content: center"> Welcome to Cell Therapy Solutions </h1>
            <div class="main-nav">
                <ul> Main Menu:
                    <li><a href="products/index.php">Products</a></li>
                    <li><a href="users/index.php">Users</a></li>
                    <li><a href="ln2_locations/index.php">LN2 Locations</a></li>
                    <li><a href="processes/index.php">Processes</a></li>
                </ul>
            </div>
        </div>
        
    </body>
</html>




<? php

phpinfo(INFO_VARIABLES);

?>



