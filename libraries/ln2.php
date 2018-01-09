<?php

class LN2
{
    public $location = array();
    public $attributes = array();
    
    // constructor takes the $assign array and stores into $attributes
    public function __construct($assign = null)
    {
	if(is_array($assign) && !empty($assign)){
	    $this->attributes = $assign;
	    $this->location['tank'] = $this->attributes['tank'];
	    $this->location['rack_letter'] = $this->attributes['rack_letter'];
	    $this->location['rack_number'] = $this->attributes['rack_number'];
	    $this->location['slot'] = $this->attributes['slot'];
	}
    }
    
    public function assign_ln2_location(&$pdo)
    {
	if(is_array($this->attributes) && count($this->attributes) == 6){
	    
	    $sql = 'INSERT INTO `ln2_locations`'
		    . '(`tank`, `rack_letter`, `rack_number`, `slot`, `din`, `component_code`) '
		    . 'VALUES '
		    . '(:tank, :rack_letter, :rack_number, :slot, :din, :component_code)';
	    
	    $statement = $pdo->prepare($sql);
	    if($statement->execute($this->attributes)){
		echo '<br />SUCCESS!!! Product has been properly assigned to that LN2 location';
	    }
	    else{
		echo '<br />Product was not successfully assigned, what went wrong??';
		var_dump($statement->errorInfo());
	    }
	}
	else{
	    echo '<br />Something went wrong, your attributes array is not properly populated';
	}
    }
    
    public function is_empty(&$pdo)
    {
	if(is_array($this->location) && count($this->location) == 4){
	   
	    $sql = 'SELECT * FROM `ln2_locations` '
		. 'WHERE `tank` = :tank AND '
		. '`rack_letter` = :rack_letter AND '
		. '`rack_number` = :rack_number AND '
		. '`slot` = :slot';

	    $statement = $pdo->prepare($sql);
	    if(!$statement->execute($this->location)){
		echo '<br />Failed to execute DB search, shutting down pdo connection';
		$pdo = null;
	    }	
	}
	else{
	    echo '<br />ERROR: the $location in your LN2 object is not currently set, but why??';
	}
	
	if($statement->rowCount() == 0){
	    echo '<br />That slot is empty';
	    return true;
	}
	else if($statement->rowCount() == 1){
	    echo '<br />That slot if occupied';
	    return false;
	}
	else{
	    echo '<br />Something impossible has happened, ABORT!!!!';
	    exit();
	}
    }
}

