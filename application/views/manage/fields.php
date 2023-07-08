
<table>
	<tr>
		<td>&nbsp;</td>
		<td>column</td>
		<td>mask name</td>
		<td>group</td>
	</tr>
	<?foreach($dbCols as $col){
		if(substr($col,0,2) =='c_')
			continue;
	?>
		<tr col_name='<?=$col?>'>
			<td>
				<input class='chk-active' type='checkbox' <?=isset($displayFields[$col]) ? 'checked' : ''?> >
			</td>
			<td><?=$col?></td>
			<td> <input class='input-mask_name' type='input' value='<?=(isset($displayFields[$col]) ? $displayFields[$col]['mask_name'] : '')?>'></td>
			<td> <input class='input-group_name' type='input' value='<?=(isset($displayFields[$col]) ? $displayFields[$col]['display_group'] : '')?>'></td>
		<tr>
	<?}?>
</table>

<script>
	var doAlert  = 0;
	
	function save_fields(obj,action,value,col_name){
		var url= '<?=base_url()?>manage/save_fields/';
		var data = {};
		//action can be active,mask_name, or display_group
		data[action] = value;
		//this will served as the reference
		data['column_name']  = col_name;
		
		$.ajax({
			url:url,
			data:data,
			type:'POST',
			success:function(data){
				//if(doAlert)
				//	alert('saved');
			}
		})
	}
	$(function(){
		$('.chk-active').change(function(){
			var col_name = $(this).closest('tr').attr('col_name');
			var val = $(this).prop('checked');
			
			if(val == true)
				val = 1;
			else
				val = 0;
			
			doAlert = 0;
			save_fields(this,'is_active',val,col_name);
		})
		
		$('.input-mask_name').keyup(function(e){
		
			//if(e.keyCode==13){
				doAlert = 1;
				var col_name = $(this).closest('tr').attr('col_name');
				var val = $(this).val();
				save_fields(this,'mask_name',val,col_name);
			//}
			
		})
		
		
		$('.input-group_name').keyup(function(e){
		
			//if(e.keyCode==13){
				doAlert = 1;
				var col_name = $(this).closest('tr').attr('col_name');
				var val = $(this).val();
				save_fields(this,'display_group',val,col_name);
			//}
			
		})
	})
</script>

