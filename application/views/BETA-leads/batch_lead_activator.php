<form id='frm-do-activator' method="post" action="<?=base_url()?>leads/do_batch_upload/2" enctype="multipart/form-data" target='if-test'>
	<table>
		<tr>
			<td>
				<label for='userfile'>Files</label>
				<input type="file" name="userfile" size="20" class='required' > <input type="submit" id='btn-upload-activator' value=" upload " />
			</td>
		</tr> 
	</table>
</form>
<iframe style='display:none' id='if-test'></iframe>


<script>
	$(document).ready(function(){
		
		$("#frm-do-activator").submit(function(){ 
			$("#btn-upload-activator").val('please wait').prop('disabled',true);
			
				if(!$(this).valid()){
					alert('Please check all required fields');
					$("#btn-upload-activator").val('upload').removeProp('disabled'); 
					return false;
				}else{
	 
					var form = document.getElementById('frm-do-activator');
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
							$("#btn-upload-activator").val('upload').removeProp('disabled'); 
						} 
					}
					
					xhr.open('POST', form.getAttribute('action'), true);
					xhr.send(formData);
				}  
				return false; // To avoid actual submission of the form
				
			});
			
		}) 
</script>