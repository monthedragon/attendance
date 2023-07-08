*<span class='note'>click the checkbox to activate/deactivate lead identity </span>
<br><br>
<input type='checkbox' id='chk-show-active' <?=($isAll==1)?'':'checked'?>>Show only active 
<table border =1>
	<tr>
		<td>active</td>
		<td>lead identity</td>
		
	</tr>
	<?foreach($leadDetails as $k=>$details){?>
		<tr>
			<td><input type='checkbox' <?=($details['is_active']) ? 'checked' : ''?> class='chk-lead-identity-active' li_id='<?=$details['id']?>'></td>
			<td><?=$details['lead_identity']?></td>
		</tr>
	<?}?>
</table>

<script>
$(function(){

	$('#chk-show-active').change(function(){
		var show_active_only = 1
		if($(this).prop('checked') == true)
			show_active_only=0;  
			
		window.location ='<?=base_url()?>main/manage_lead_identity/'+show_active_only;
	});
	
	$('.chk-lead-identity-active').change(function(){
        
		var is_active= 0;
        if($(this).prop('checked')==  true)
            is_active= 1;
			
		var data = {};
		data['is_active']=is_active;
		data['id'] = $(this).attr('li_id');
		
		
		$.ajax({
			url:'<?=base_url()?>main/li_activator',
			data:data,
			type:'POST',
			success:function(data){
		
			}
		})
	})
})
</script>
