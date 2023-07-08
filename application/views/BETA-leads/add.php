<div class='page-header'>add contact</div>
 
<form id='frm-add-contact'>

	<table> 
		<tr>
			<td>firstname</td>
			<td>middlename</td>
			<td>lastname</td>
		</tr>
		<tr>
			<td><input type='input'  id='firstname' name='firstname'   class='required' ></td>
			<td><input type='input'  id='middlename' name='middlename'   class=''></td>
			<td><input type='input'  id='lastname' name='lastname'   class='required'></td>
		</tr>
		<tr>
			<td>tel no.</td>
			<td>mobile no.</td>
			<td>office no.</td> 
		</tr>
		<tr>
			<td><input type='input'  name='telno'   class='required obj-conditional'></td> 
			<td><input type='input'  name='mobileno'   class=''></td> 
			<td><input type='input'  name='officeno'   class=''></td>
		</tr>
		<tr>
			<td>credit limit (cl)</td>
			<td>available cl</td>
		</tr>
		<tr>
			<td><input type='input'  name='credit_limit'   class='required' ></td> 
			<td><input type='input'  name='ava_cred_limit'   class=''></td>  
		</tr>   
		
		<tr>
			<td>bill cycle</td>
			<td>double cl</td>
		</tr>
		<tr>
			<td><input type='input'  name='bill_cycle'   class='' ></td> 
			<td><input type='input'  name='double_cl'   class=''></td>  
		</tr>   
		
		<tr>
			<td>lead identity</td>
			<td>agent</td>
		</tr>
		<tr>
			<td>
				<select name='lead_identity' class='required'>
					<option value=''>
					<?foreach($leadDetails as $k=>$details){?>
						<option value='<?=$details['lead_identity']?>'><?=$details['lead_identity']?></option>
					<?}?>
				</select>
			</td> 
			<td>
				<select name='assigned_agent' class='required'>
					<option value=''>
					<?foreach($users as $userid=>$username){?>
						<option value='<?=$userid?>'><?=$username?></option>
					<?}?>
				</select>
				
			</td>  
		</tr>   
		 
		<tr>
			<td valign=top>
				<input type='submit' id='btnSubmit' value=' add '>
				 
			</td>
		</tr>
	</table> 
</form>

<div id='div-add-msg'></div>

<script>
	$(document).ready(function(){
		$("#frm-add-contact").submit(function(){
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>leads/save/',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){ 
						$("#div-add-msg").html('<span class=warning>saved!</span>');
					}
				});
			}
			return false;
		})
		
		$(".dob").mask("99/99/9999");
	})
</script>