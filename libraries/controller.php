<?php

// all dependencies in one file
require_once '/dbh.php';
require_once '/products.php';
require_once '/patients.php';
require_once '/processes.php';
require_once '/ln2.php';
require_once '/logger.php';

class Controller
{
    public $db;
    public $product;
    public $patient;
    
    public function __construct()
    {
        $this->db = connect();
        $this->product = new Product();
        $this->patient = new Patient();
    }
}