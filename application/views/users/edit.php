<span class='page-header'>edit user</span>
<form id='frm-edit-user'>

<table>
	<tr>
		<td>Employee Code</td>
		<td><input type='input'  name='emp_code'   class='required' value='<?=$user[0]['emp_code']?>' ></td>
	</tr> 
	<tr>
		<td>username</td>
		<td><input type='input'  name='user_name'   class='required' value='<?=$user[0]['user_name']?>' readonly></td>
	</tr> 
	<tr>
		<td>firstname</td>
		<td><input type='input'  name='firstname'   class='required' value='<?=$user[0]['firstname']?>'></td>
	</tr>
	<tr>
		<td>middlename</td>
		<td><input type='input'  name='middlename'   class='required' value='<?=$user[0]['middlename']?>'></td>
	</tr>	
	<tr>
		<td>lastname</td>
		<td><input type='input'  name='lastname'   class='required' value='<?=$user[0]['lastname']?>'></td>
	</tr>
	<tr>
		<td>user type</td>
		<td>
			<select name='user_type'  class='required'>
				<option value=''>--select--</option>
				<?foreach($userTypes as $d){?>
					<option value='<?=$d['lu_code']?>' <?=(($user[0]['user_type'] == $d['lu_code']) ? 'selected' : '' )?>><?=$d['lu_desc']?></option>
				<?}?>
			</select>
		</td>
	</tr>
	<tr>
		<td>alternate username</td>
		<td><input type='input'  name='alt_user_name'   class='' value='<?=$user[0]['alt_user_name']?>'></td>
	</tr> 
	<tr>
		<td>Target login time</td>
		<td><input type='input'  name='target_login'   class='' value='<?=substr($user[0]['target_login'],0,5)?>' maxlength=5 size=10 placeholder='hh:mm'></td>
	</tr> 
	<tr>
		<td>Max leave</td>
		<td><input type='input'  name='leaves'   class='' value='<?=$user[0]['leaves']?>' ></td>
	</tr> 
	<tr>
		<tr colspan=2>
			<input type='checkbox' name='reset_pw'>Reset Password
		</td>
	</tr>
</table>

<table>
<tr>
	<td colspan=2>Rigths and Privileges<td>
</tr>
<?
$oldRightGroup='';
foreach($rigths as $r){
    if($oldRightGroup != $r['right_group']){
?>
    <tr class='header'>
           <td colspan=5><?=strtoupper($r['right_group'])?></td>
    </tr>
<?
        $oldRightGroup= $r['right_group'];
    }
?>
	<tr>
		<td>
			<input type='checkbox' name='privs[<?=$r['id']?>]' <?=(isset($user_privs[$r['id']]) ? 'checked' : '' )?>>
		</td>
		<td><?=$r['right_name']?></td>
		<td><?=$r['right_desc']?></td>
	</tr>
<?}?>

	<tr>
		<td colspan = 2>
			<input type='submit' value=' update '>
		</td>
	</tr>
</table>
</form>

<div id='div-update-msg'></div>

<script>
	$(document).ready(function(){
		$("#frm-edit-user").submit(function(){
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>users/save/1',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){ 
						$("#div-update-msg").html('<span class=warning>'+data+'</span>');
					}
				});
			}
			return false;
		})
	})
</script>




