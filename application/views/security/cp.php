<div class='page-header'>change password</div>
<p>
<form id='frm-change-pw'>
<table>
	<tr>
		<td>
			<label for='old_password'>old password</label>
			<input type='password' name='old_password' class='required'>
		</td>
	</tr>
	<tr>
		<td>
			<label for='password'>new password</label>
			<input type='password' name='password' class='required'>
		</td>
	</tr>
	<tr>
		<td>
			<label for='rep_password'>repeat new password</label>
			<input type='password' name='rep_password' class='required'>
		</td>
	</tr>
	<tr>
		<td><input type='submit' value = ' change password '></td>
	</tr>
</table>
</form>
</p>
<div id='div-cp-result'></div>

<script>
	$("#frm-change-pw").submit(function(){
		if($(this).valid())
		{
			$.ajax({
				url:'<?=base_url()?>security/cp_save',
				data:$(this).serialize(),
				type:'POST',
				success:function(data){
					$("#div-cp-result").html('<span class=warning>'+data+'</span>');
				}
			})
		}
		return false;
	})
</script>