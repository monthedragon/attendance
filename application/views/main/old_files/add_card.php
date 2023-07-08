<div class='page-header'>add card</div>
 
<form id='frm-add-card'>

	<table> 
		<tr>
			<td>issuer</td>
			<td>card no</td>
			<td>credit limit</td>
			<td>issue date</td>
		</tr>
		<tr>
			<td><input type='input'  id='issuer' name='issuer'   class='required' value='<?=isset($cardDetails[0]['issuer']) ? $cardDetails[0]['issuer'] : ''?>'></td>
			<td><input type='input'  id='card_no' name='card_no'   class='required creditcard' value='<?=isset($cardDetails[0]['card_no']) ? $cardDetails[0]['card_no'] : ''?>'></td>
			<td><input type='input'  id='credit_limit' name='credit_limit'   class='required' value='<?=isset($cardDetails[0]['credit_limit']) ? $cardDetails[0]['credit_limit'] : ''?>'></td>
			<td><input type='input'  id='issue_date' name='issue_date'   class='required date' value='<?=isset($cardDetails[0]['issue_date']) ? $cardDetails[0]['issue_date'] : ''?>' placeholder='mm/dd/yyyy'></td>
		</tr>
		<tr>
			<td valign=top>
					<input type='submit' id='btnSubmit' value=' <?=($cardID == 0 ) ? 'add card' : 'update card'?> '> 
			</td>
		</tr>
	</table> 
</form>

<div id='div-add-msg'></div>

<script>
	$(document).ready(function(){
		$("#frm-add-card").submit(function(){
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>main/save_card/<?=$id?>/<?=$cardID?>',
					data:$(this).serialize(),
					type:'POST',
					success:function(data){ 
						alert('Saved');
						//$("#div-add-msg").html('<span class=warning>saved!</span>'); 
						$.modal.close();
					}
				});
			}
			return false;
		})

         $(".date").mask("99/99/9999");
		 $(".creditcard").mask('9999-9999-9999-9999');
	})
</script>