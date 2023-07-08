<table>
	<tr>
		<td>Time</td>
		<td>Action</td>
	</tr>
	<?foreach($timedetails as $d){?>
		<tr>
			<td><?=$d['time_stamp']?></td>
			<td><?=time_log_equiv($d['log_status'])?></td>
		</tr>
	<?}?>
	
</table>