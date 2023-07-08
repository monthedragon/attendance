<a href='<?=base_url()?>leads/leads' class='a-back-to-leads'>go back</a>
<hr>
Lead Details of <?=$agent[$userid]?>
<table class='tbl-lead-manage' border=1 width=50%> 
<tr  class='header'>
    <td >&nbsp;</td>
	<td colspan=2 align='center'>ASSIGNED</td>
	<td >&nbsp;</td>
	<td colspan=2 align='center'>UNASSIGNED</td>
</tr>
<tr class='header'>
    <td>&nbsp;</td>
	<td>Lead identity</td>
	<td>Callresults</td> 
	<td class='td-separator'>&nbsp;</td> 
	<td>TOUCHED</td> 
	<td>VIRGIN</td> 
</tr>
<?foreach($leadDetails as $d){?>
	<tr li='<?=$d['lead_identity']?>'>
        <td><input type='checkbox' <?=(isset($leads_assigned[$d['lead_identity']])) ? 'checked' : ''?> class='chk-lead-identity-active' ></td>
		<td valign=top><?=$d['lead_identity']?></td>
		<!---allocated leads--->
		<td valign=top>
			<?if(isset($allocLeads[$d['lead_identity']])){
				foreach($allocLeads[$d['lead_identity']] as $cr=>$ctr){

                    if(!isset($restricted[$cr]) ){
                        echo input('txt-alloc','','input','txt-alloc','','',"cr='$cr' number alloc=1 ",2,10);
                    }
                    echo "{$cr}  [{$ctr}]" . '<br>';
				}
			}?>
		</td>
		
		<td class='td-separator'>&nbsp;</td> 
		
		<!---unallocated leads touched--->
		<td  valign=top>
			<?if(isset($unAllocLeads[$d['lead_identity']])){
				foreach($unAllocLeads[$d['lead_identity']] as $cr=>$ctr){
                    if(!isset($restricted[$cr]) ){
                        echo input('txt-alloc','','input','txt-alloc','','',"cr='$cr' number alloc=0 li='{$d['lead_identity']}'",2,10);
                    }
					echo "{$cr}  [{$ctr}]" . '<br>';
			?>
				
			<?
				}
			}?>
		</td>
		
	
		<!---unallocated leads virgin--->
		<td valign=top>
			<?if(isset($unAllocVirginLeads[$d['lead_identity']])){  
				echo input('txt-virgin','','input','txt-alloc','','',"cr='V'  virgin alloc=0 li='{$d['lead_identity']}'",2,10) ." [{$unAllocVirginLeads[$d['lead_identity']]}]" . '<br>';
				 
			?>
				
			<?}?>
		</td>
	</tr>
<?}?>  
</table>
<script>
$(function(){ 
	
	$('.txt-alloc').keyup(function(e){
		
		var key = e.keyCode;
		//pressed enter
		if(key == 13){

			var data = {};
			data['callresult']=$(this).attr('cr');
			data['value']=$(this).val();
            data['allocType']=$(this).attr('alloc');
            data['li']=$(this).closest('tr').attr('li');
			
			$.ajax({
				url:'<?=base_url().'leads/allocate_leads'?>',
				data:data,
				type:'POST',
				success:function(data){
					$("#div-contact-list").html('');
					window.location = '<?=base_url()?>leads/manage/<?=$userid?>';
				}
			})
		}
	})

    $(".chk-lead-identity-active").change(function(){

        var is_assign= 0;
        if($(this).prop('checked')==  true)
            is_assign= 1;

        var data = {};
        data['lead_identity'] = $(this).closest('tr').attr('li');
        data['is_assign'] = is_assign;

        $.ajax({
            url:'<?=base_url().'leads/lead_iden_activator'?>',
            data:data,
            type:'POST'
        })
    })
})
</script>