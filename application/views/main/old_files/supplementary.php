<?if($action == 0){?>
	<input type='button' id='btn-add-supple' value=' add supple '>
<?}?>
<?if(count($suppleDetails)==0){?>
	<br>
	<span class='warning'>No supple details found.</span>
<?}else{?>

	<table width=50%>
		<tr class='header'>
			<td>fullname</td>
			<td>relationship</td>
			<td>assigned spend limit</td>
			<td>&nbsp;</td>
		</tr>
		<?foreach($suppleDetails as $data){?>
			<tr class='list'>
				<td valign=top><?=$data['firstname']. ' ' . $data['middlename'] . ' ' . $data['lastname']?></td>
				<td valign=top><?=$data['relationship']?></td>
				<td valign=top><?=$data['assigned_spend_limit']?></td>
				<td valign=top>
					<?if($action == 0){?>
						<a class='a-edit-supple' suppleID='<?=$data['id']?>'>edit</a> | <a class='a-del-supple'  suppleID='<?=$data['id']?>'>del</a>
					<?}?>
				</td>
			</tr>
		<?}?>
	</table>

<?}?> 

<div id='div-add-supplementary'></div>

<script> 
	$(function(){
		$("#btn-add-supple").click(function(){  
			do_modal('<?=base_url()?>main/add_supple/<?=$id?>','div-add-supplementary','supple',500,550);
		})
		
		$(".a-edit-supple").click(function(){
			var suppleID = $(this).attr('suppleID'); 
			do_modal('<?=base_url()?>main/add_supple/<?=$id?>/'+suppleID,'div-add-supplementary','supple',500,500);
			return false;
		})


        $(".a-del-supple").inlineConfirmation({
            confirmCallback: function() {

                var suppleID =  $(this).parent().parent().parent().find('a.a-del-supple').attr('suppleID');
                var url = '<?=base_url()?>main/delete_supple/'+suppleID;
                alert(url);
                $.ajax({
                    url:url,
                    success:function(data){
                        getSuppleCards();
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