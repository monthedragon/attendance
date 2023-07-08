<div id='print_div_holder' style='width:50%'>
<?
	if($error_msg){
		echo "<span style='font-size:15px !important;' class='warning'>{$error_msg}</span>";
	}
	if($success_msg){
		//todo CHANGE THE ICON LATER
		echo "<span class='warning'>{$success_msg}</span>";
	}
	
?>
<?php echo form_open_multipart('main/save_file_form/'.$record);?>
<table>
<tr>
	<td>Employee:</td>
	<td><?=select('user_id','user_id',' required ',$user_id,null,null,$user_list)?> </td>
</tr>
<tr>
	<td>Tag: </td>
	<td>
		<?=select('tagging','tagging',' required ',$tagging,null,null,$lu_tagging)?> 
		<?=select('absent_sub_tagging','absent_sub_tagging',' sub_tagging ',$absent_sub_tagging,null,null,$absent_sub)?> 
		<?=select('ot_sub_tagging','ot_sub_tagging',' sub_tagging ',$ot_sub_tagging,null,null,$ot_sub)?> 
		<?=select('leave_sub_tagging','leave_sub_tagging',' sub_tagging ',$leave_sub_tagging,null,null,$leave_sub)?> 
	</td>
</tr>
<tr>
	<td>Date From:  </td>
	<td>
		<input type='input' name='from_date' class='input_date required' value='<?=($from_date) ? $from_date : DATE('Y-m-d')?>'> 
		To: <input type='input' name='to_date' class='input_date required' value='<?=($to_date) ? $to_date  : DATE('Y-m-d')?>'> 
	</td>
</tr>
<tr>
	<td valign=top>Notes/Comments:  </td>
	<td>
		<textarea name='reasons' cols=100 rows=10><?=$reasons?></textarea>
	</td>
</tr>
<tr>
	<td valign=top>Status:  </td>
	<td>
		<?=select('leave_status','leave_status','  ',$leave_status,null,null,$file_status)?> 
	</td>
</tr>
</table>
<input type='button' name='back' onclick='window.location.href = "<?=base_url()?>";' value='Back' />
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
		}else if(val == 'ot'){
			$('#ot_sub_tagging').show().addClass('required');
		}else if(val == 'leave'){
			$('#leave_sub_tagging').show().addClass('required');
		}else{
			$('.sub_tagging').hide();
		}
		
	})
	
	$('#tagging').change();
	
	$(".input_date").datepicker({'dateFormat':'yy-mm-dd'});
	
	
})
</script>