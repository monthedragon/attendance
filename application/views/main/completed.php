<?

$temp = $health_dec_info[0]['c_temperature'];
$allow = true;
foreach($target_allow_fields as $field){
	if($health_dec_info[0][$field] == 'yes'){
		$allow = false;
		break;
	}
		
}

if($temp >= TARGET_TEMP || !$allow){
	$msg = "<span class=warning>PLEASE SEE YOUR TEAM LEADER OR HR ASSOCIATE</span>";
}else{
	$msg =  "YOU CAN NOW LOGIN TO YOUR ACCOUNT!!!!";
}


echo "<h1>{$msg}</h1>";

?>