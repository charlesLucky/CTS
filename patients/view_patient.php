<?php

include '/../constants/constants.php';
include '/../libraries/controller.php';

// resume session and validate it
session_start();
var_dump($_SESSION);

// redirect if session not valid
if(!$_SESSION['valid']){
    header('Location: http://localhost/CTS/');
}

// connect to DB
$pdo = connect();

if(!$pdo){
    die('Could not connect to DB');
}

// search
if(isset($_POST['submit-search'])){

    echo 'searching now...';

    // sanitize and filter search term
    $search_by = $_POST['search_by'];
    $search_term = filter_var($_POST['search_term'], FILTER_SANITIZE_STRING);

    $sql = "SELECT * FROM `patients` WHERE `$search_by` = '$search_term'";

    // store search result row in $result
    $statement = $pdo->query($sql); 

    $result = $statement->fetch(PDO::FETCH_ASSOC);
     var_dump($statement);
     var_dump($result);
}
// view all
else if(isset($_POST['view-all'])){

    // paginate results of view all
    if(isset($_GET['page'])){
	$page = $_GET['page'] + 1;
	$offset = $rec_limit * $page ;
    }
    else{
	$page = 0;
	$offset = 0;
    }

    $sql = 'SELECT * FROM `patients` LIMIT 10 OFFSET :offset';
    $results_all = $pdo->prepare($sql);

    $results_all->execute(array(':offset' => $offset));
    if(!$results_all || count($results_all) < 1){
	echo '<br />Failed to retrieve patient data from DB';
    }    
}

?>

<html>
    <head>
        <meta charset="UTF-8">
        <title> Welcome to CTS </title>
    </head>
    <body>
        <div class="patients-view">
            <h1 style="align-content: center"> View Patients </h1>
            <div class="patients-nav">
		<li style="display: inline-block"><a href="index.php">Patients Index</a></li>
                <li style="display: inline-block"><a href="view_patient.php">View Patients</a></li>
                <li style="display: inline-block"><a href="add_patient.php">Add New Patient</a></li>
                <li style="display: inline-block"><a href="update_patient.php">Update Patient</a></li>
                <li style="display: inline-block"><a href="delete_patient.php">Delete Product</a></li>
            </div><hr style="width:1368px"/>
            <div class="patients-search">
                <form method="post" action="#">
                    <br /><br />Search Parameter: 
                    <select name="search_by">
                        <option name="df_mrn" value="df_mrn">DF MRN</option>
                        <option name="other_mrn" value='other_mrn'>Other MRN</option>
                        <option name="last_name" value='first_name'>Last Name</option>
                        <option name="first_name" value='last_name'>First Name</option>
                    </select>
                    <br /><br />Search Term:
                    <input type="text" name="search_term"/>
                    <input type="submit" name="submit-search" value="Search"/>
                    <br /><br />
                    <input type="submit" name="view-all" value="View All Patients"/>
                </form>
            </div>
        </div>
    </body>
</html>

<?php

// if search was successful
if(isset($result) && is_array($result) && count($result) > 0){
    
    echo '<table border="1" style="width:100%">';
    echo '<tr>';
    echo '<th>Patient ID</th>';
    echo '<th>Last Name</th>';
    echo '<th>First Name</th>';
    echo '<th>DF MRN</th>';
    echo '<th>Other MRN</th>';
    echo '<th>NMDP ID</th>';
    echo '<th>DOB</th>';
    echo '<th>TP/Protocol</th>';
    echo '<th>ABO</th>';
    echo '<th>Rh</th>';
    echo '</tr>';
    
    echo '<tr>';
    foreach($result as $key => $value){
        echo "<td>$value</td>";
    }
    echo '</tr>';
    echo '</table>';  
}
else{
    echo "<br />SEARCH FAILED";
}


