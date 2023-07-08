<form id='frm-report'>
	Target Date: 
	<input type='input' name='start_date' class='date' value='<?php echo date('Y-m-d');?>'>
	<input type='input' name='end_date' class='date' value='<?=date('Y-m-d')?>'>
	<input type='submit' value=' generate ' id='btnSubmit'>
</form>

<div id='div-viewer'></div>

<script>
	$(function(){
		$(".date").datepicker({'dateFormat':'yy-mm-dd'});
		$('form').submit(function(){
		
			if($(this).valid()){
				var url = "<?=base_url('reports/generate_obr_rpt')?>";
				var data = $(this).serialize();
				var type ='POST';
				
				window.location  =(url+'?'+data);
		
			}
			return false;
		})
		
	})
</script>