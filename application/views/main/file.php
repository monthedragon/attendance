<div id='print_div_holder' style='width:50%'>
<?
	if($error_msg){
		echo "<span style='font-size:15px !important;' class='warning'>{$error_msg}</span>";
	}
	if($success_msg){
		//todo CHANGE THE ICON LATER
		echo "<span class='warning'>{$success_msg}</span>";
	}
	
	$view_only = ($record) ? true : false;
	
	$separator = ($view_only) ? ' / ' : '';
?>
<?php echo form_open_multipart('main/save_file_form/'.$record );?>
<table>
<tr>
	<td>Employee:</td>
	<td><?=select('user_id','user_id',' required ',$user_id,null,null,$user_list,$view_only)?> </td>
</tr>
<tr>
	<td>Tag: </td>
	<td>
		<?=select('tagging','tagging',' required ',$tagging,null,null,$lu_tagging,$view_only)?> 
		<?=$separator?>
		<?=select('absent_sub_tagging','absent_sub_tagging',' sub_tagging ',$absent_sub_tagging,null,null,$absent_sub)?> 
		<?=select('leave_sub_tagging','leave_sub_tagging',' sub_tagging ',$leave_sub_tagging,null,null,$leave_sub)?> 
		<span class='sub_tagging spn_main_spn_leave'><span id='spn_rem_leave'><?=$no_of_leave?></span> remaining leave/s</span>
		<?=select('ot_sub_tagging','ot_sub_tagging',' sub_tagging ',$ot_sub_tagging,null,null,$ot_sub)?> 
		<?=$separator?>
		<?=select('max_ot_hr','max_ot_hr',' sub_tagging ',$max_ot_hr,null,null,$max_ot_hr_tagging)?> 
		<?=select('max_ut_hr','max_ut_hr',' sub_tagging ',$max_ut_hr,null,null,$max_ut_hr_tagging)?> 
		
	</td>
</tr>
<tr>
	<td>Date   </td>
	<td>
	<?
		if($record){
			//IF record is selected then dont show the two fields for FROM and To
			//instead display the value from from_date
			echo date('F d, Y (l)',strtotime($target_date));
		}else{
	?>
		From:<input type='input' name='from_date' class='input_date required' value='<?=($from_date) ? $from_date : DATE('Y-m-d')?>'> 
		To: <input type='input' name='to_date' class='input_date required' value='<?=($to_date) ? $to_date  : DATE('Y-m-d')?>'> 
	<?}?>
	</td>
</tr>
<tr>
	<td valign=top>Notes/Comments:  </td>
	<td>
		<textarea name='reasons' cols=100 rows=10><?=$file_reason?></textarea>
	</td>
</tr>

<?if($user_type == ADMIN_CODE || ($user_type == TL_CODE && $username != $user_id && $user_id)){?>
	<tr>
		<td valign=top>Status:  </td>
		<td>
			<?=select('file_status','file_status','  ',$file_status,'pending',null,$file_status_sub)?> 
		</td>
	</tr>
<?}?>	
<?if(isset($modified_by)){?>
	<tr>
		<td valign=top>Last modified:  </td>
		<td>
			<?=$user_list_for_disp[$modified_by]?> 
		</td>
	</tr>
<?}?>	
</table>
<br>
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
			$('#max_ot_hr').show().addClass('required');
		}else if(val == 'ut'){
			$('#max_ut_hr').show().addClass('required');
		}else if(val == 'leave'){
			$('#leave_sub_tagging').show().addClass('required');
			$('.spn_main_spn_leave').show();
		}else{
			$('.sub_tagging').hide();
		}
		
	})
	
	$('#tagging').change();
	
	$(".input_date").datepicker({'dateFormat':'yy-mm-dd'});
	
	$('#user_id').change(function(){
		var user_id = $(this).val();
		$('#spn_rem_leave').load('<?=base_url()?>main/get_rem_leave_ajax/'+user_id);
		
	})
	
	
})
</script>