<a href='<?=base_url()?>main/leads' class='a-back-to-leads'>go back</a>
<hr>
Lead Details of <?=$agent[$userid]?>
<table class='tbl-lead-manage' border=1 width=80%> 
<tr  class='header'>
    <td >&nbsp;</td>
	<td colspan=3 align='center'>ASSIGNED</td>
	<td >&nbsp;</td>
	<td colspan=3 align='center'>UNASSIGNED</td>
</tr>
<tr class='header'>
    <td>&nbsp;</td>
	<td>Lead identity</td>
	<td>Callresults (<9)</td> 
	<td>Callresults (>=9)</td> 
	<td class='td-separator'>&nbsp;</td> 
	<td>TOUCHED (<9)</td> 
	<td>TOUCHED (>=9)</td> 
	<td>VIRGIN</td> 
</tr>
<?foreach($leadDetails as $d){?>
	<tr class='tr-list' li="<?=$d['lead_identity']?>">
        <td>
					<input type='checkbox' <?=(isset($leads_assigned[$d['lead_identity']])) ? 'checked' : ''?> class='chk-lead-identity-active' >
				</td>
		<td valign=top>
			<a name="<?=$d['lead_identity']?>"></a>
			<?=$d['lead_identity']?>
		</td>
		<!---allocated leads--->
		<td valign=top>
			<?
			$over_used_leads = '';
			if(isset($allocLeads[$d['lead_identity']])){
				foreach($allocLeads[$d['lead_identity']] as $cr=>$ctr){
					
					if(!isset($restricted[$cr]) ){
						echo input('txt-alloc','','input','txt-alloc','','',"cr='$cr' number alloc=1 ",2,10);
					}
					
					if(isset($cr_lookup[$cr])){
						$cr = $cr_lookup[$cr];
					}
					
                    echo "{$cr}  [".$ctr['LESS_9']."]" . '<br>';
					
					if(isset($ctr['OVER_USED']) && $ctr['OVER_USED'] > 0){
						$over_used_leads .= "{$cr}  [".$ctr['OVER_USED']."]" . '<br>';
					}
				
				}	
			}?>
		</td>
		<td valign=top><?=$over_used_leads?></td>
		
		<td class='td-separator'>&nbsp;</td> 
		
		<!---unallocated leads touched--->
		<td  valign=top>
			<?
			$over_used_leads_unassigned = '';
			if(isset($unAllocLeads[$d['lead_identity']])){
				foreach($unAllocLeads[$d['lead_identity']] as $cr=>$ctr){
                    if(!isset($restricted[$cr]) ){
                        echo input('txt-alloc','','input','txt-alloc','','',"cr='$cr' number alloc=0 li='{$d['lead_identity']}'",2,10);
                    }
					
					if(isset($cr_lookup[$cr])){
						$cr = $cr_lookup[$cr];
					}
					
					echo "{$cr}  [".$ctr['LESS_9']."]" . '<br>';
					
					if(isset($ctr['OVER_USED']) && $ctr['OVER_USED'] > 0){
						$over_used_leads_unassigned .= "{$cr}  [".$ctr['OVER_USED']."]" . '<br>';
					}
			?>
				
			<?
				}
			}?>
		</td>
		<td  valign=top><?=$over_used_leads_unassigned?></td>
	
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

function scrollTo(hash) {
    location.hash = "#" + hash;
}

$(function(){ 
	
	//go to targeted hash
	scrollTo("<?=$target_li?>");
	
	$(".tr-list").mouseover(function(){$(this).addClass('tr_highlight');});
	$(".tr-list").mouseout(function(){$(this).removeClass('tr_highlight');});
	
	$('.txt-alloc').keyup(function(e){
		
		var key = e.keyCode;
		//pressed enter
		if(key == 13){

			var data = {};
			var selected_li = $(this).closest('tr').attr('li');
			data['callresult']=$(this).attr('cr');
			data['value']=$(this).val();
			data['allocType']=$(this).attr('alloc');
			data['li']=selected_li;
			
			$.ajax({
				url:'<?=base_url().'main/allocate_leads'?>',
				data:data,
				type:'POST',
				success:function(data){
					$("#div-contact-list").html('');
					window.location = '<?=base_url()?>main/manage/<?=$userid?>/'+selected_li;
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
            url:'<?=base_url().'main/lead_iden_activator'?>',
            data:data,
            type:'POST'
        })
    })
})
</script>