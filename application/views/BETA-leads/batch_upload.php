<form id='frm-do-upload' method="post" action="<?=base_url()?>leads/do_batch_upload/" enctype="multipart/form-data" target='if-test'>
	<table> 
		<tr>
			<td>
				<label for='lead_identity'>Lead Identity</label>
				<input type='input' name='lead_identity' id='lead_identity' class='required'>
			</td>
		</tr>
		<tr>
			<td>
				<label for='userfile'>File</label>
				<input type="file" name="userfile" size="20" class='required' > 
				<input type="submit" id='btn-upload' value="upload" />
			</td>
		</tr> 
	</table>
</form>
<iframe style='display:none' id='if-test'></iframe>


<script>
	function check_lead_identity(){
		var li = $("#lead_identity").val();
		var data = {};
		data['li'] = li;
		var data = $.ajax({
			url:'<?=base_url()?>leads/check_lead_identity',
			type:'post',
			data:data,
			success:function(data){
				return data; //returns 1(existing and not allowed to proceed) and 0 is allowed!
			}
		})
		 
	}
	$(document).ready(function(){
		
		$("#frm-do-upload").submit(function(){
			
			
			if(!$(this).valid()){
				alert('Please check all required fields');
				return false;
			}else{
			
				var li = $("#lead_identity").val();
				var data = {};
				data['li'] = li;
				var data = $.ajax({
					url:'<?=base_url()?>leads/check_lead_identity',
					type:'post',
					data:data,
					beforeSend:function(){
						$("#btn-upload").val('please wait').prop('disabled',true);
					},
					success:function(data){
						if( data == 1){
							alert('Transaction canceled!. Make it sure Lead identity is UNIQUE!'); 
							$("#btn-upload").val('upload').removeProp('disabled');
						}else{ 
							var form = document.getElementById('frm-do-upload');
							var file  ='';

							var formData = new FormData(form); 
							formData.append('file', file);

							var xhr = new XMLHttpRequest();
							
							xhr.onreadystatechange = function()
							{
								if(xhr.readyState == 4)
								{
								  var resp = xhr.responseText;	 
								  
								  if(resp.indexOf('Success') == 0){
									alert('Done!'); 
									$("#div-messages").html('SUCCESS!');
								  }else{
									alert('Error!');
									$("#div-messages").html(resp); 
								 } 
								
									$("#btn-upload").val('upload').removeProp('disabled');
								}
							}
							
							xhr.open('POST', form.getAttribute('action'), true);
							xhr.send(formData);

						}
					}
				}) 
				
				
			}  
				return false; // To avoid actual submission of the form
				
		});
			
	})
</script>