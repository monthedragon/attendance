<div id='print_div_holder' style='width:50%'>

<?php echo form_open_multipart('main/save/'.$record);?>
<input type='hidden' name='log_date' value='<?=substr($attendance_info['login'],0,10)?>'>
<table>
<tr>
	<td>Date</td>
	<td><h2><?=date('F d, Y (l)',strtotime($attendance_info['login']));?></h2></td>
</tr>
<tr>
	<td>Time IN</td>
	<td>
		<?
			echo select('login_hr','login_hr',' obj-conditional ',$login_time[0],null,null,$hour_lu);
			echo select('login_minute','login_minute',' obj-conditional ',$login_time[1],null,null,$minute_lu);
			echo select('login_meridiem','login_meridiem',' obj-conditional ',$login_time[2],null,null,$meridiem);
		?>
	</td>
</tr>
<tr>
	<td>Time OUT</td>
	<td>
		<?
			echo select('logout_hr','logout_hr',' obj-conditional ',$logout_time[0],null,null,$hour_lu);
			echo select('logout_minute','logout_minute',' obj-conditional ',$logout_time[1],null,null,$minute_lu);
			echo select('logout_meridiem','logout_meridiem',' obj-conditional ',$logout_time[2],null,null,$meridiem);
		?>
		<span class=note><i>unselect any of the fields to re-tag the logout time</i></span>
	</td>
</tr>
<tr>
	<td>Tags</td>
	<td>
		<?=select('tagging','tagging',' obj-conditional ',$attendance_info['tagging'],null,null,$tagging)?> 
		<?=select('absent_sub_tagging','absent_sub_tagging',' sub_tagging ',$attendance_info['sub_tagging'],null,null,$absent_sub)?> 
	</td>
	
		
</tr>
<tr>
	<td>Notes</td>
	<td><?=nl2br($attendance_info['file_reason'])?></td>
</tr>
</table>
<br>
<input type='button' name='back' onclick='window.location.href = "<?=base_url()?>";'  value='Back' />
<input type='submit' name='submit' value='Save' />
	
</form>
<script>
$(function(){
	
	$('form').submit(function(){
		if(!$(this).valid()){
			return false;
		}
	})
	
	
	$('#tagging').change(function(){
		var val = $(this).val();
		$('.sub_tagging').hide().removeClass('required');
		
		if(val == 'absent'){
			$('#absent_sub_tagging').show().addClass('required');
		}
		
	})
	
	$('#tagging').change();
	
})
</script>