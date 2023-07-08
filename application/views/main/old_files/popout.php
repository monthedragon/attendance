<?if(count($popoutRec) > 0){
		$i=1;
		$ctr=0;	
?> 
	<fieldset>
	<legend>Forced Popout Leads</legend>
	<table class='tbl-lead-views'>
		<tr>
			<td> </td>
			<td>firstname</td>
			<td>lastname</td>
			<td>calldate</td>
			<td>callresult</td>
		</tr>
	<?foreach($popoutRec as $detail){
			 if($ctr == ITEM_PER_PAGE){
				$i++;
				$ctr=0;
			}	
	?>
		<tr class='tr-list popup-selector_selection popup-selector_page-<?=$i?>' >
			<td>
			<?if(isset($privs[189])){?>
				<input type='checkbox' <?=($detail['is_active']) ? 'checked' : ''?> class='chk-contact-active'>
			<?}?>
			</td>
			<td><?=$detail['firstname']?></td>
			<td><?=$detail['lastname']?></td>
			<td><?=$detail['calldate']?></td>
			<td>
				<?=(isset($cr[$detail['callresult']]) ? $cr[$detail['callresult']] : '')?>
				
				<?if(in_array($detail['callresult'],$withSubCR)){?>
					<?=isset($subCr[$detail['sub_callresult']]) ?  "(<i>{$subCr[$detail['sub_callresult']]} </i>)" : ''?>
				<?}?>
			</td> 
			<?if(isset($privs[174])){?>
				<td class='td-pick'><a href='<?=base_url()?>main/edit/<?=$detail['id']?>' class='a-link '>Pick</a></td>
			<?}?>
		</tr>
	<?
     $ctr++;
	}?>
	</table>
	<div id='popup-selector'></div>
	</fieldset> 
	
<script>
	$(document).ready(function(){
		a_link_fx('popup-selector','<?=count($popoutRec)?>');
	})
</script>

<?}?> 
