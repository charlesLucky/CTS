<?php

// returns a PDO object, defaults to user=root, pass=root
function connect($dsn = 'mysql:host=localhost;dbname=cts;charset=utf8mb4', $user = 'root', $pass ='root')
{
    $dbh = new PDO($dsn, $user, $pass);

    if($dbh){
        return $dbh;
    }
    else{
        return false;
    }
}






