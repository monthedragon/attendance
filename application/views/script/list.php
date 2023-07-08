<a href = '<?=base_url('script/add')?>'>add script</a>
<br><br>
<table>
    <tr class='tr-header'>
        <td>order</td>
        <td>script subject</td>
    </tr>
    <?foreach($scripts as $details){?>
        <tr>
            <td><?=$details['order']?></td>
            <td><?=$details['script_label']?></td>
            <td><a href='<?=base_url('script/edit/'.$details['id'])?>'>edit</a> | delete </td>
        </tr>
    <?}?>
</table>