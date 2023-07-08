<div class='page-header'>add user</div>


<form id='frm-add-user'>

<table>
	<tr>
		<td>Employee Code</td>
		<td><input type='input'  name='emp_code'   class='required' value='<?=$user[0]['emp_code']?>' ></td>
	</tr> 
	<tr>
		<td>username</td>
		<td><input type='input'  name='user_name'   class='required'></td>
	</tr>
	<tr>
		<td>password</td>
		<td><input type='password'  name='user_password'  class='required'></td>
	</tr>    
	<tr>
		<td>firstname</td>
		<td><input type='input'  name='firstname'   class='required'></td>
	</tr>
	<tr>
		<td>middlename</td>
		<td><input type='input'  name='middlename'   class='required'></td>
	</tr>	
	<tr>
		<td>lastname</td>
		<td><input type='input'  name='lastname'   class='required'></td>
	</tr>
	<tr>
		<td>user type</td>
		<td>
			<select name='user_type'  class='required'>
				<option value=''>--select--</option>
				<?foreach($userTypes as $d){?>
					<option value='<?=$d['lu_code']?>'><?=$d['lu_desc']?></option>
				<?}?>
			</select>
		</td>
	</tr>
	<tr>
		<td>alternate username</td>
		<td><input type='input'  name='alt_user_name'   class='' value=''></td>
	</tr> 
	<tr>
		<td>Max leave</td>
		<td><input type='input'  name='leaves'   class='' value='<?=$user[0]['leaves']?>' ></td>
	</tr> 
	<tr>
		<td colspan = 2>
			<input type='submit' value=' add '>
		</td>
	</tr>
</table>
</form>

<div id='div-add-msg'></div>

<script>
	$(document).ready(function(){
		$("#frm-add-user").submit(function(){
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>users/save/',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){ 
						$("#div-add-msg").html('<span class=warning>'+data+'</span>');
					}
				});
			}
			return false;
		})
	})
</script>