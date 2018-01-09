<?php

class Logger
{
    public $user;
    public $dt;
    public $fp;
    public $log = __DIR__ . '../logs/activity_log.txt';
    
    public function __construct()
    {
	if(isset($_SESSION['user'])){
	    
	    $user = $_SESSION['user'];
	    $dt = new DateTime();
	}
    }
    
    public function init_logger()
    {
	$fp = fopen($log, 'a+');
	if($fp){
	    return true;
	}
	else{
	    return false;
	}
    }
    
    public function close_logger()
    {
	if(fclose($fp)){
	    return true;
	}
	else{
	    return false;
	}
    }
    
    public function log_patient($activity)
    {
	$dt->setTimestamp(time());
	$dt->format($dt_format_read);
	$entry = $this->dt . ' : ' . $this->user . ' : ' . $activity['action'] 
		. ' patient ' . $activity['full_name'] . ' ' . $activity['df_mrn'];
	
	fwrite($this->fp, $entry);
    }
    
    public function log_product($activity)
    {
	$dt->setTimestamp(time());
	$dt->format($dt_format_read);
	$entry = $this->dt . ' : ' . $this->user . ' : ' . $activity['action'] 
		. ' product ' . $activity['full_name'] . ' ' . $activity['df_mrn'];
	
	fwrite($this->fp, $entry);
    }
    
    public function log_process($activity)
    {
	$dt->setTimestamp(time());
	$dt->format($dt_format_read);
	$entry = $this->dt . ' : ' . $this->user . ' : ' . $activity['action'] 
		. ' process ' . $activity['full_name'] . ' ' . $activity['df_mrn'];
	
	fwrite($this->fp, $entry);
    }
   
    
    
    
}