<form id='frm-do-pop'>
<table>
	<tr>
		<td>name</td>
		<td><?=$details[0]['firstname'] . ' ' . $details[0]['lastname']?></td>
	</tr>
	<tr>
		<td>calldate</td>
		<td><?=$details[0]['calldate']?></td>
	</tr>
	<tr>
		<td>agent</td>
		<td><?=(isset($users[$details[0]['agent']]) ? $users[$details[0]['agent']] : 'n/a')?></td>
	</tr>
	<tr>
		<td>pop to</td>
		<td>
			<select id='sel-pop-to' name='pop_to' class='<?=(($details[0]['forcedpop'] == 1) ? '' : ' required ')?>'>
				<option value=''> --select-- </option>
				<?foreach($users as $uid=>$name){?>
					<option value='<?=$uid?>'> <?=$name?> </option>
				<?}?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=3>
			<input type='submit' value='<?=(($details[0]['forcedpop'] == 1) ? ' cancel pop-out ' : ' pop-out ')?>'>
		</td>
	</tr>
</table>
</form>

<script>
$(function(){
	$("#frm-do-pop").submit(function(){
		if($(this).valid())
		{
			var popout = '<?=$details[0]['forcedpop']?>';
			//default is do pop out
			var url ='<?=base_url()?>main/dopop/<?=$id?>';
			
			//if already in pop out state, then cancel pop out
			if(popout == 1)
				url = url + '/0';
			else
				url = url + '/1';
			 
			$.ajax({
				url:url,
                data:$('#frm-do-pop').serialize(),
                type:'POST',
				success:function(data){
					//console.log(data);
					alert('DONE!');
					$.modal.close();
				}
			})
		}
		
		return false;
	})
})
</script>