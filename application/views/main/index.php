<?
echo "<div style=float:right>";

echo '<h3>'. date('F d, Y') . '</h3>';

	if($today_att_info['login'] && empty($today_att_info['logout'])){
		
		echo "<div class='div_break'>";
		//BATHROOM BREAK
		if($bb_status == false || !empty($bb_status['break_out'])){
			//if bb_status = false means no break for the day
			//if break_out is not empty means BREAK IN is possible
			echo "<input type = 'button' VALUE = 'Bathroom break-IN' 	class = 'btn_break button' break_type='bathroom' action='in'  msg='Bathroom break IN?'>";
		}else{
			$bathroom_in = date('g:i A',strtotime($bb_status['break_in']));
			echo "
					<input type = 'button' VALUE = 'Bathroom break-OUT' 	class = 'btn_break button btn-out' break_type='bathroom' action='out' msg='Bathroom break OUT?'>
					<br>
					Bathroom break In: {$bathroom_in} 
				";
		}
		echo "</div>";
		
		echo "<div class='div_break'>";
		//COFFE BREAK
		if($cb_status == false || !empty($cb_status['break_out'])){
			//if bb_status = false means no break for the day
			//if break_out is not empty means BREAK IN is possible
			echo "<input type = 'button' VALUE = 'Coffee break-IN' 	class = 'btn_break button' break_type='coffee' action='in'  msg='Coffee break IN?'>";
		}else{
			$coffee_in = date('g:i A',strtotime($cb_status['break_in']));
			echo "
					<input type = 'button' VALUE = 'Coffee break-OUT' class = 'btn_break button btn-out' break_type='coffee' action='out' msg='Coffee break OUT?'>
					 <br>
					<span stye='padding-left:20px'>Coffee break In: {$coffee_in}</span>
				";
		}
		echo "</div>";
	}
	
echo "</div>";
?>
<?
if($today_att_info){
	if($today_att_info['login']){
		echo "<h2>TIME IN: ".date('g:i:s A',strtotime($today_att_info['login'])) . '</h2>';
	}
	
	if($today_att_info['logout']){
		echo "<h2>TIME OUT: ".date('g:i:s A',strtotime($today_att_info['logout'])) . '</h2>';
	}
}
?>
<?if(!$is_log_in){?>
	<input type = 'button' VALUE = 'LOG-IN' class = 'btn_log button' action='login' msg="DO LOG IN?">
<?}elseif($is_log_out != 1){ //logout will be displayed if the value is TRUE meaning it has no value on attendance.logout?>
	<input type = 'button' VALUE = 'LOG-OUT' class = 'btn_log button btn-logout btn-out'  action='logout' msg = "DO LOG OUT?">
<?}else{
	echo "<h2>See you tomorrow {$user_full_name}! </h3>";
  }
?>

<form id='frm-search-agent'>
	<table>
		<tr>
		<?if($user_type == ADMIN_CODE || $user_type == TL_CODE){?>
			<td>
				<label for='firstname'>firstname</label>
				<input type='input' name='firstname' value='<?=$firstname?>'>
			</td>
			<td>
				<label for='lastname'>lastname</label>
				<input type='input' name='lastname' value = '<?=$lastname?>'>
			</td>
		<?}?>
			<td>
				<label for='from_date'>From date</label>
				<input type='input' name='from_date' class='input_date ' value='<?=$from_date?>'>
			</td>
			<td>
				<label for='to_date'>TO date</label>
				<input type='input' name='to_date' class='input_date '  value='<?=$to_date?>'>
			</td>
			
			<td>
				<label for='search_type'>Search type</label>
				<?=select('search_type','search_type','  ',$search_type,null,null,$search_type_lu);?>
				
				<?if($user_type == ADMIN_CODE || $user_type == TL_CODE){?>
					<input type='input' placeholder='input reason' name='file_reason' value='<?=$file_reason?>' id='inp_file_reason'>
				<?}?>
			</td>
			
			<td>
				<label for='include_break'>Pending L/OT</label>
				<input type='checkbox'  name='pending_only' id='pending_only'  <?=$pending_only?>>
			</td>
			<td>
				<label for='btnSearch'>&nbsp;</label>
				<input type='submit' value=' search ' name='btnSearch'	id='btnSearch'>
			</td>
		</tr>
	</table>
</form>
<div id='div-agent-list'></div>



<script>
$(function(){
	
	$("#frm-search-agent").submit(function(){
		
		if($(this).valid()){
			$.ajax({
				url:'<?=base_url();?>main/search_agent/',
				data:$(this).serialize(),
				type:'POST',
				success:function(data){
					$("#div-agent-list").html(data);
					$("#div-manage").html('');
					
					//TODO set it back later (on hold as of 2021-02-07)
					//return back once issue found on the performance in future
					//$('.input_date').addClass('required');
				}
			})
			return false;
		}
		
		return false;
	})
	
		
	$('.btn_log').click(function(){
		var action = $(this).attr('action');
		var msg = $(this).attr('msg');
		
		if(confirm(msg)){
			window.location = '<?=base_url()?>main/index/'+action;
		}
	})
		
	$('.btn_break').click(function(){
		var break_type = $(this).attr('break_type');
		var action = $(this).attr('action');
		var msg = $(this).attr('msg');
		
		if(confirm(msg)){
			window.location = '<?=base_url()?>main/index/break/'+break_type+'/'+action;
		}
	})
	
	$(".input_date").datepicker({'dateFormat':'yy-mm-dd','showButtonPanel': true});
	
	//search LW from init load
	$("#frm-search-agent").submit();
	
	//control file_reason input display
	$('#search_type').change(function(){
		var val = $(this).val();
		
		$('#inp_file_reason').hide();
		if(val == 'leave'){
			$('#inp_file_reason').show();
		}
		
	})
	
	$('#search_type').change();
})
</script>