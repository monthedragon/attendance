
<?if($user_type == ADMIN_CODE || $user_type == TL_CODE){?>
<?php echo form_open_multipart('holiday/save/');?>
	<table>
		<tr>
			<td>Holiday</td>
			<td>
				<input type='input' name='holiday' class='required' value=''> 
			</td>
		</tr>
		<tr>
			<td>Date</td>
			<td>
				<input type='input' name='target_date' class='input_date required' value=''> 
			</td>
		</tr>
		<tr>
			<td>Type</td>
			<td>
				<?=select('holiday_type','holiday_type',' required ',$tagging,null,null,$lu_holiday_type)?> 
			</td>
		</tr>
		<tr>
			<td>
				<input type='button' name='back' onclick='window.location.href = "<?=base_url()?>";' value='Back' />
			</td>
			<td>
					<input type='submit' name='submit' value='save ' />
			</td>
		</tr>
	</table>
</form>
<?}else{?>

	<input type='button' name='back' onclick='window.location.href = "<?=base_url()?>";' value='Back' />
<?}?>
<div id='div-holiday-list'></div>



<script>
$(function(){
	
	$("form").submit(function(){
		if(!$(this).valid()){
			return false;
		}
	})
	
	$('#div-holiday-list').load("<?=base_url().'holiday/lw'?>");
	
	$(".input_date").datepicker({'dateFormat':'yy-mm-dd','showButtonPanel': true});
	
})
</script>