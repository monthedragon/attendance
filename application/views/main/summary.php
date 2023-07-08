<?if($user_type == ADMIN_CODE || $user_type == TL_CODE){?>
	Employee: <?=select('user_id','user_id',' required ',$user_id,null,null,$user_list,$view_only)?> 
<?}?>
	Year: <?=select('year','year',' required ',$year,null,null,$year_list,$view_only)?> 

<?if($summary_key){?>

<table border=1 width=60%>
	<?php
	
	echo '<tr>';
	echo '<td></td>';
	for($month = 1; $month <= 12; $month++){
		
		$target_date 	= date("Y-{$month}-d");
		$month_lbl 		= date('M',strtotime($target_date));
		echo "<td class='number'>{$month_lbl}</td>";
		
	}
	echo '</tr>';
	
	foreach($summary_key as $key=>$key_lbl){
		
		if(isset($ot_sub[$key_lbl])){
			$label = $ot_sub[$key_lbl];
		}elseif(isset($leave_sub[$key_lbl])){
			$label = $leave_sub[$key_lbl];
		}elseif(isset($att_sub[$key_lbl])){
			$label = $att_sub[$key_lbl];
		}else{
			$label = $key_lbl;
		}
		
		echo "<td>{$label}</td>";
		
		for($month = 1; $month <= 12; $month++){
			
			$ctr = 0 ;
			if(isset($summary_info[$key][$month])){
				$ctr = $summary_info[$key][$month]['CTR'];
			}
			echo  "<td class='number'>{$ctr}</td>";
		}
		
		echo '</tr>';
	}
	?>
</table>
<?}else{?>
	<br>
	<span class='warning'>No record found</span>
<?}?>
<script>

function reloadSummary(user_id, year){
	window.location = '<?=base_url()?>main/summary/'+user_id+'/'+year;
	
}

$(function(){
	$('#user_id').change(function(){
		var user_id = $(this).val();
		var year = $('#year').val();
		reloadSummary(user_id, year)
	})
	
	$('#year').change(function(){
		var user_id = $('#user_id').val();
		var year = $(this).val();
		reloadSummary(user_id, year)
	})
})
</script>