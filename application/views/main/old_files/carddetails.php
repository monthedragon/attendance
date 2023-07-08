<?if($action == 0){?>
	<input type='button' id='btn-add-card' value=' add card '>
<?}?>
<?if(count($cardDetails)==0){?>
	<br>
	<span class='warning'>No card details found.</span>
<?}else{?>

	<table width=50%>
		<tr class='header'>
			<td>issuer</td>
			<td>card no</td>
			<td>credit limit</td>
			<td>issue date</td>
			<td>&nbsp;</td>
		</tr>
		<?foreach($cardDetails as $data){?>
			<tr class='list'>
				<td valign=top><?=$data['issuer']?></td>
				<td valign=top><?=$data['card_no']?></td>
				<td valign=top><?=$data['credit_limit']?></td>
				<td valign=top><?=$data['issue_date']?></td>
				<td valign=top>
					<?if($action == 0){?>
						<a class='a-edit' cardID='<?=$data['id']?>'>edit</a> | <a class='a-del-card'  cardID='<?=$data['id']?>' >del</a>
					<?}?>
				</td>
			</tr>
		<?}?>
	</table>

<?}?> 

<div id='div-add-card'></div>

<script>
	
	$(function(){
		$("#btn-add-card").click(function(){
			do_modal('<?=base_url()?>main/add_card/<?=$id?>','div-add-card','card',150,650);
		})
		
		$(".a-edit").click(function(){
			var cardID = $(this).attr('cardID');
			do_modal('<?=base_url()?>main/add_card/<?=$id?>/'+cardID,'div-add-card','card',150,650);
			return false;
		})

        $(".a-del-card").inlineConfirmation({
            confirmCallback: function() {

                var cardID =  $(this).parent().parent().parent().find('a.a-del-card').attr('cardID');
                var url = '<?=base_url()?>main/delete_card/'+cardID;
                $.ajax({
                    url:url,
                    success:function(data){
                        getCardDetails();
                    }
                })
            },
            expiresIn: 3,
            confirm:"<a href='#' >Yes</a>",
            separator:" | ",
            cancel:"<a href='#'>No</a>"

        });
    })
</script>