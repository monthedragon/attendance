<h2>SUB CALLRESULT</h2>
<form id='frm-add-cr'> 
<table>
	<tr>
		<td>
			<label for='lu_code'>Code</label>
			<input type='input' class='alphanumeric required' name='lu_code'>
		</td>
		<td>
			<label for='lu_desc'>Description</label>
			<input type='input' class='alphanumeric required' name='lu_desc'>
		</td>
		<td>
			<label for='btn-submit'>&nbsp;</label>
			<input type='submit' value=' add callresult  ' name='btn-submit'>
		</td>
</table>
	<input type='hidden' name='lu_cat' value='callresult'>
</form>

<br>
<hr>
<br>

<span class='note'>*please press enter to save order by and description</span>
<table>
<tr>
<td valign=top>
	
	<table>
		<?foreach($callresult as $k=>$detail){?>
		<tr  lu_id ='<?=$detail['id']?>' class='<?=($detail['is_legacy'] == 0) ? 'tr-edit' : ''?>'>
			<td><?=$detail['lu_code']?></td>
			<td><input type='input' value='<?=$detail['lu_desc']?>' class='txt-desc'></td> 
			<td><input type='input' value='<?=$detail['order_by']?>'  maxlength=3 size=4 class='txt-order-by number'></td>
		</tr>
		<?}?>
	</table>
	
</td>
<td valign=top>
	
	<div id='div-show-sub'></div>
	
</td>
</tr>
</table>
<script>
	function lu_ajax(data,luID){ 
		$.ajax({
			url:'<?=base_url()?>manage/save_cr/'+luID,
			type:'POST',
			data:data,
			success:function(data){
				alert('Saved');
			}
		})
	}
	
	
	$(function(){
		$('#frm-add-cr').submit(function(){
			
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>manage/save_cr',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){
						if(data == 1)
							window.location = '<?=base_url()?>manage/callresult';
						else
							alert('Lookup Code is already exist!');
					}
				})
			}
			return false;
		})
	})
	
	$(".txt-desc").keyup(function(e){
		if(e.keyCode == 13){
			var luID = $(this).closest('tr').attr('lu_id');
			var data = {};
			data['lu_desc'] = $(this).val();
			lu_ajax(data,luID)
		}
	}) 
	
	$(".txt-order-by").keyup(function(e){
		if(e.keyCode == 13){
			var luID = $(this).closest('tr').attr('lu_id');
			var data = {};
			data['order_by'] = $(this).val();
			lu_ajax(data,luID)
		}
	}) 
	
	$(".spn-show-sub").click(function(){
		var cr = $(this).attr('lu_code');
		$('#div-show-sub').load('<?=base_url()?>manage/sub_callresult')
	})
	
	$('.alphanumeric').alphanumeric();
	$('.number').numeric();
</script>