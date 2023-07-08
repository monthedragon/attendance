<!--
2013-11-21 revisions
call result should have space but call result code should haven't
-->

<form id='frm-add-cr-<?=$iden?>'>
<table>
	<tr>
		<td>
			<label for='lu_code'>Code</label>
			<input type='input' class='alphanumeric required' name='lu_code'>
		</td>
		<td>
			<label for='lu_desc'>Description</label>
			<input type='input' class='alphanumeric_wspace required' name='lu_desc'>
		</td>
		<td>
			<label for='btn-submit'>&nbsp;</label>
			<input type='submit' value=' add <?=($iden ==0) ? 'sub' : '' ?> callresult  ' name='btn-submit'>
		</td>
</table>
	<!--set the lu category based on the passed callresult-->
	<!--default is callresult -->
	<input type='hidden' name='lu_cat' value='<?=$cr?>'>
	
</form>

<br>
<hr>
<br>

<?if($iden ==1) {?>
	<span class='note'>*please press enter to save order by and description</span>
	<br>
	<span class='note'>*default callresult can't be edited.</span>
<?}?>

<table>
<tr>
<td valign=top>
	
	<table>
		<?foreach($callresult as $k=>$detail){?>
		<tr  lu_id ='<?=$detail['id']?>' class='<?=($detail['is_legacy'] == 0) ? 'tr-edit' : ''?>'>
			<td>
				<?if(($detail['is_legacy'] == 0)){?>
					<input type='checkbox' class='chk-active-lu' <?=($detail['is_active']) ? 'checked' : ''?>>
				<?}?>
			</td>
			<td><?=$detail['lu_code']?></td>
			<td><input type='input' value='<?=$detail['lu_desc']?>' class='txt-desc'></td> 
			<td><input type='input' value='<?=$detail['order_by']?>'  maxlength=3 size=4 class='txt-order-by number'></td>
			<td><?=(in_array($detail['lu_code'],$cr_with_sub)) ? "<span lu_code='{$detail['lu_code']}' class='spn-show-sub'>show sub</span>" : ''?></td>
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
		$('#frm-add-cr-<?=$iden?>').submit(function(){
			
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>manage/save_cr',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){
						if(data==1){
							alert('Saved!. This page will reload')
							window.location = '<?=base_url()?>manage/callresult';
						}else
							alert('Lookup Code is already exist!');
					}
				})
			}
			return false;
		})
	})
	
	
	$('.txt-desc').unbind('keyup');
	$(".txt-desc").keyup(function(e){
		if(e.keyCode == 13){
			var luID = $(this).closest('tr').attr('lu_id');
			var data = {};
			data['lu_desc'] = $(this).val();
			lu_ajax(data,luID)
		}
	}) 
	
	$(".txt-order-by").unbind('keyup');
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
		$('#div-show-sub').load('<?=base_url()?>manage/callresult/'+cr);
	})
	
	$('.chk-active-lu').unbind('change');
	$('.chk-active-lu').change(function(){
		var luID = $(this).closest('tr').attr('lu_id');
		var is_active= 0;
		if($(this).prop('checked')==  true)
			is_active= 1;
			
		
		var data = {};
		data['is_active'] = is_active;
		lu_ajax(data,luID)
	})

    $('.alphanumeric_wspace').alphanumeric({allow:" "});
    $('.alphanumeric').alphanumeric();
	$('.number').numeric();
</script>