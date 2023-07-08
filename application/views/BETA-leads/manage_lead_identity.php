<table border =1>
	<tr>
		<td>lead identity</td>
		<td>active</td>
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
	$('.chk-lead-identity-active').change(function(){
        
		var is_active= 0;
        if($(this).prop('checked')==  true)
            is_active= 1;
			
		var data = {};
		data['is_active']=is_active;
		data['id'] = $(this).attr('li_id');
		
		
		$.ajax({
			url:'<?=base_url()?>leads/li_activator',
			data:data,
			type:'POST',
			success:function(data){
		
			}
		})
	})
})
</script>
