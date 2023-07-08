<fieldset>
    <legend>List</legend>
<table>
	<tr>
		<td>Holiday</td>
		<td>Date</td>
		<td>Type</td>
		<td></td>
	</tr>
	<?foreach($holiday_list as $info){?>
		<tr>
			<td><?=$info['holiday']?></td>
			<td><?= date('F d, Y (l)',strtotime($info['target_date']))?></td>
			<td><?=$lu_holiday_type[$info['holiday_type']]?></td>
			<td>
				<?if($user_type == ADMIN_CODE || $user_type == TL_CODE){?>
					<span style='cursor:pointer;font-weight:bold;' class='delete_holiday' record = '<?=$info['id']?>'>delete</span>
				<?}?>
			</td>
		</tr>
	<?}?>
</table>
</fieldset>

<script>
$(function(){
	$('.delete_holiday').click(function(){
		window.location = "<?=base_url().'holiday/del/'?>"+$(this).attr('record');
	})
})
</script>