<table>
<?foreach($users as $d){?>	
	<tr userid='<?=$d['user_name']?>'>
		<td><a href='<?=base_url()?>main/manage/<?=$d['user_name']?>' class='a-manage-agent-leads'>[ manage ]</a></td>
		<td><?=$d['firstname']. ' ' . $d['lastname']?></td>
	</tr>
<?}?>
</table>
 