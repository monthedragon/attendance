<form id='frm-time-tracker'>
	<table>
		<tr>
			<td>start date</td>
			<td><input type='input' name='startDate' class='date' value='<?=date('Y-m-d')?>'></td>
		</tr>
		<tr>
			<td>end date</td>
			<td><input type='input' name='endDate' class='date' value='<?=date('Y-m-d')?>'></td>
		</tr>
		<tr>
			<td>User</td>
			<td>
				<select name='users'>
					<option value=''>--all--</option>
					<?foreach($users as $userId=>$d){?>
						<option value='<?=$userId?>'><?=$d?></option>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2><input type='submit' value=' search '></td>
		</tr>
	</table>
</form>


<div id='div-time-tracker'></div>


<script>
	$(document).ready(function(){
		$(".date").datepicker({'dateFormat':'yy-mm-dd'});
		
		$("#frm-time-tracker").submit(function(){
			$.ajax({
				url:'<?=base_url()?>users/seach_time_logs',
				type:'POST',
				data:$(this).serialize(),
				success:function(data){ 
					console.log(data);
					$("#div-time-tracker").html(data);
				}
			})
		
			return false;
		})
	})
</script>