<?php

include '/../libraries/controller.php';

session_start();

if(isset($_POST['calc-submit'])){
    
    if(is_array($_POST['tnc']) && count($_POST['tnc'] > 0)){
        
        echo '<br/ >';
        var_dump($_POST['tnc']); 
	
	// remember the timepoint for the calculation
	$timepoint = $_POST['timepoint'];
       
	// convert all strings into floats
        foreach($_POST['tnc'] as $key => $value){
            $tnc[$key] = filter_var($value, FILTER_SANITIZE_STRING);
	    $tnc[$key] = floatval($value);
	}
	
	foreach($_POST['cd34'] as $key => $value){
	    $cd34[$key] = filter_var($value, FILTER_SANITIZE_STRING);
	    $cd34[$key] = floatval($value);
	}
        
        echo '<br/>';
        var_dump($tnc);
	echo '<br/>';
        var_dump($cd34);
        
	$tncml = $tnc['tncml'] * 1000000; // x 10^6
        $tnc['tnc'] = round($tncml * $tnc['tnc_dilution'] * $tnc['tnc_viability'] * $tnc['volume'], 2, 1);
        $tnc['tnckg'] = round($tnc['tnc'] / $tnc['weight'], 2, 1);
	
	$cd34_1 = $cd34['cd34_1'] * 1000; // x 10^3
	$cd34_2 = $cd34['cd34_2'] * 1000; // x 10^3
	$cd34_neg = $cd34['cd34_neg'] * 1000; // x 10^3
	$cd34['total'] = round(($cd34_1 + $cd34_2 / 2.0) - $cd34_neg, 2, 1);
	$cd34['cd34'] = round($cd34['total'] * $cd34['cd34_dilution'] * $cd34['cd34_viability'] * $tnc['volume'], 2, 1);
	$cd34['cd34kg'] = round($cd34['cd34'] / $tnc['weight'], 2, 1);
	
	echo '<br/>';
        var_dump($tnc);
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
	<script type='text/javascript'>
	    window.onload = function() {
		var volume = window.opener.document.getElementById("volume").value;
		this.document.getElementById("volume").value = volume;
	    }
	</script>
        <script type="text/javascript">
            function init_return_close() {
                if (window.opener != null && !window.opener.closed) {
                    var tnc = window.opener.document.getElementById("init_tnc");
                    tnc.value = document.getElementById("tnc").value;
                    var tnckg = window.opener.document.getElementById("init_tnckg");
                    tnckg.value = document.getElementById("tnckg").value;
		    var tnc = window.opener.document.getElementById("init_cd34");
                    tnc.value = document.getElementById("cd34").value;
                    var tnckg = window.opener.document.getElementById("init_cd34kg");
                    tnckg.value = document.getElementById("cd34kg").value;
		    var calc_id = window.opener.document.getElementById("calc_id");
		    calc_id.value = document.getElementById("calc_id").value;
                }
                window.close();
            }
        </script>
        <script type="text/javascript">
            function prefreeze_return_close() {
                if (window.opener != null && !window.opener.closed) {
                    var tnc = window.opener.document.getElementById("prefr_tnc");
                    tnc.value = document.getElementById("tnc").value;
                    var tnckg = window.opener.document.getElementById("prefr_tnckg");
                    tnckg.value = document.getElementById("tnckg").value;
		    var tnc = window.opener.document.getElementById("prefr_cd34");
                    tnc.value = document.getElementById("cd34").value;
                    var tnckg = window.opener.document.getElementById("prefr_cd34kg");
                    tnckg.value = document.getElementById("cd34kg").value;
		    var calc_id = window.opener.document.getElementById("calc_id");
		    calc_id.value = document.getElementById("calc_id").value;
                }
                window.close();
            }
        </script>
        <title> Cell Count Calculations </title>
    </head>
    <body>
        <div class="cell-count-calculations">
            <h1 style="align-content: center"> Cell Count Calculation Sheet </h1>
        </div>
        <form method="post" action="#">
            <input type="radio" name="timepoint" value="initial" checked>Initial<br />
            <input type="radio" name="timepoint" value="prefreeze">Pre-freeze<br />
            <br /><br />TNC/mL: (10^6/mL)
	    <input type="text" name="tnc[tncml]" 
                   value="<?php echo isset($tnc['tncml']) ? $tnc['tncml'] : '' ?>"/>
	    <br /><br />Dilution 1:
	    <input type="text" name="tnc[tnc_dilution]"
		   value="<?php echo isset($tnc['tnc_dilution']) ? $tnc['tnc_dilution'] : '' ?>"/>
            <br /><br /> Viability: (0.99 = 99%)
	    <input type="text" name="tnc[tnc_viability]"
                   value="<?php echo isset($tnc['tnc_viability']) ? $tnc['tnc_viability'] : '' ?>"/>
            <br /><br />Volume: 
	    <input type="text" name="tnc[volume]" id="volume"
		   value="<?php echo isset($tnc['volume']) ? $tnc['volume'] : '' ?>"/>
	    <br /><br />Patient Weight (kg):
	    <input type="text" name="tnc[weight]"
                   value="<?php echo isset($tnc['weight']) ? $tnc['weight'] : '' ?>"/>
            <br /><br />TNC: (10^10)
	    <input type="text" id="tnc" readonly="readonly" 
                   value="<?php echo isset($tnc['tnc']) ? $tnc['tnc'] : '0' ?>"/>
            <br /><br />TNC/kg: (10^8/kg)
	    <input type="text" id="tnckg" readonly="readonly" 
                   value="<?php echo isset($tnc['tnckg']) ? $tnc['tnckg'] : '0' ?>"/>
	    
            <br /><br />CD34/mL count 1: (10^6/mL)
	    <input type="text" name="cd34[cd34_1]"
		   value="<?php echo isset($cd34['cd34_1']) ? $cd34['cd34_1'] : '' ?>"/>
	    <br /><br />CD34/mL count 2: (10^6/mL)
	    <input type="text" name="cd34[cd34_2]"
		   value="<?php echo isset($cd34['cd34_2']) ? $cd34['cd34_2'] : '' ?>"/>
	    <br /><br />CD34/mL negative count: (10^6/mL)
	    <input type="text" name="cd34[cd34_neg]"
		   value="<?php echo isset($cd34['cd34_neg']) ? $cd34['cd34_neg'] : '' ?>"/>
	    <br /><br />Dilution 1:
	    <input type="text" name="cd34[cd34_dilution]"
		   value="<?php echo isset($cd34['cd34_dilution']) ? $cd34['cd34_dilution'] : '' ?>"/>
	    <br /><br />Viability: (0.99 = 99%)
	    <input type="text" name="cd34[cd34_viability]"
		   value="<?php echo isset($cd34['cd34_viability']) ? $cd34['cd34_viability'] : '' ?>"/>
	    <br /><br />CD34/mL average - CD34/mL negative control: 
	    <input type="text" name="cd34[total]" readonly="true"
		   value="<?php echo isset($cd34['total']) ? $cd34['total'] : '0' ?>"/>
	    <br /><br />Total CD34: (10^8)
	    <input type="text" name="cd34[cd34]" id="cd34" readonly="true"
		   value="<?php echo isset($cd34['cd34']) ? $cd34['cd34'] : '0' ?>"/>
	    <br /><br />Total CD34/kg: (10^6/kg)
	    <input type="text" name="cd34[cd34kg]" id="cd34kg" readonly="true"
		   value="<?php echo isset($cd34['cd34kg']) ? $cd34['cd34kg'] : '0' ?>"/>
	    
	    <br /><br />
            <input type="submit" name="calc-submit" value="Perform Calculations"/>
            <br /><br />
        </form>
	<?php if(isset($timepoint)){
	    if($timepoint == 'initial'){?>
	<input type="button" value="Return" onclick="init_return_close();"/>
	<?php }else{ ?>
	<input type="button" value="Return" onclick="prefreeze_return_close();"/>
	<?php }} ?>
    </body>
</html>

<?php

if(isset($timepoint) && isset($_POST['calc-submit'])){
    
    // connect to DB
    // validate connection
    // insert calculation information, track by calculation_id
    $pdo = connect();
    
    if($pdo){
	
	// populate $calculation array with all the data to be inserted
	$calculations = array();
	$calculations['timepoint'] = $timepoint;
	foreach($tnc as $key => $value){
	    $calculations[$key] = $value;
	}
	foreach($cd34 as $key => $value){
	    $calculations[$key] = $value;
	}
	
	echo '<br/><br/>';
	var_dump($calculations);

	$sql = 'INSERT INTO `calculations`
		(`timepoint`, `tncml`, `tnc_dilution`, `tnc_viability`, `volume`, `weight`, `tnc`, `tnckg`, 
		`cd34_1`, `cd34_2`, `cd34_neg`, `cd34_dilution`, `cd34_viability`, `total`, `cd34`, `cd34kg`) 
		VALUES 
		(:timepoint, :tncml, :tnc_dilution, :tnc_viability, :volume, :weight, :tnc, :tnckg,
		:cd34_1, :cd34_2, :cd34_neg, :cd34_dilution, :cd34_viability, :total, :cd34, :cd34kg)';
	
	// prepare statement and execute
	$statement = $pdo->prepare($sql);
	if($statement->execute($calculations)){
            echo '<br /><br />Calculation successfully added to DB';
            // log activity
        }
        else{
            echo '<br /><br />Failed to add calculation to DB';
        }
        var_dump($statement->errorInfo());
	
	// store last insert id to link the calculation page to the process! 
	// IMPORTANT
	$calc_id = $pdo->lastInsertId();
    }
}


?>

<br /><br />Calculation ID:
<input type="text" name="calc_id" id="calc_id" readonly="true"
       value="<?php echo isset($calc_id) ? $calc_id : 'You have not entered the data and performed the calculations yet!' ?>"/>
	    

