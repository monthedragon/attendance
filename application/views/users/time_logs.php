<table>
	<tr>
		<td>User</td>
		<td>Time</td>
		<td>Action</td>
	</tr>
	<?foreach($timedetails as $d){?>
		<tr>
			<td><?=(isset($users[$d['user_id']]) ? $users[$d['user_id']] : '')?></td>
			<td><?=$d['time_stamp']?></td>
			<td><?=time_log_equiv($d['log_status'])?></td>
		</tr>
	<?}?>
	
</table>