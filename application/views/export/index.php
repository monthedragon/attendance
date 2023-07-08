<form id='frm-export'>
    <table>
        <tr>
            <td valign=top>
                <label for="start_calldate">start date</label>
                <input name='start_calldate' class='date'>
            </td>
            <td valign=top>
                <label for="end_calldate">end date</label>
                <input name='end_calldate' class='date'>
            </td>

            <td valign=top>
                <label for="agents">user list</label>
                <select multiple name='agents[]' style="height:300px;width:200px;">
                    <?foreach($users as $userid=>$username){?>
                    <option value='<?=$userid?>'><?=$username?>
                        <?}?>
                </select>
            </td>

            <td valign=top>
                <label for="lead_identities">lead identity</label>
                <select multiple name='lead_identities[]' style="height:300px;width:200px;">
                    <?foreach($leadIdentity as $details){?>
                    <option value="<?=$details['lead_identity']?>"><?=$details['lead_identity'] ?>
                        <?}?>
                </select>
            </td>
            <td valign=top>
                <label for="columns">columns</label>
                <select multiple name='columns[]' style="height:300px;width:200px;">
                    <?foreach($cols as $details){?>
                    <option value='<?=$details?>'><?=$details?>
                        <?}?>
                </select>
            </td>
            <td colspan =2 valign=bottom>
				include remarks<input type='checkbox' name='include_remarks'>
                <input type='submit' value=' search '>
            </td>
        </tr>

</form>
</table>
<script>
    $(function(){
        $('.date').datepicker({'dateFormat':'yy-mm-dd'});

        $('#frm-export').submit(function(){
            window.location = '<?=base_url()?>export/do_export?'+$(this).serialize();
            return false;
        })
    })
</script>