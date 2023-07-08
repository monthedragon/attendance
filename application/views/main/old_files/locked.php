<?if(count($lockedRec) > 0){?> 
	<fieldset>
	<legend>Locked Leads</legend>
	<table class='tbl-lead-views'>
		<tr>
			<td> </td>
			<td>firstname</td>
			<td>lastname</td> 
		</tr>
	<?foreach($lockedRec as $detail){?>
		<tr class='tr-list'>
			<td>
			<?if(isset($privs[189])){?>
				<input type='checkbox' <?=($detail['is_active']) ? 'checked' : ''?> class='chk-contact-active'>
			<?}?>
			</td>
			<td><?=$detail['firstname']?></td>
			<td><?=$detail['lastname']?></td> 
			
			<?if(isset($privs[174])){?>
				<td class='td-pick'><a href='<?=base_url()?>main/edit/<?=$detail['id']?>' class='a-link '>Pick</a></td>
			<?}?>
		</tr>
	<?}?>
	</table>
	</fieldset> 
<?}?>


<script>
	$(document).ready(function(){
		a_link_fx();
	})
</script>