<?php

function connect($dsn = 'mysql:host=localhost;dbname=cts', $user = 'root', $pass ='root')
{
    $dbh = new PDO($dsn, $user, $pass);
    
    if($dbh){
        return $dbh;
    }
    else{
        return false;
    }
}

