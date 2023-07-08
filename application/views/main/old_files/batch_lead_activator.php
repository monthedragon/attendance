Sample Format:
<br>
<img src='../assets/images/leadactivator.png' >
<span class='note' style='display:block'>
	Note: If you want to deactivate or activate leads please follow this format. "is_active" 1 means the record will be activated otherwise deactivated. <br>
	
	If you want to delete or purge the leads, you can also follow this format but the functionality of "is_active" will be disregarded and make it sure you checked the "Do Delete".
	
	<br>
	
	***If you deleted the leads theres no way to get it back.
	
	
</span>

<form id='frm-do-activator' method="post" action="<?=base_url()?>main/do_batch_upload/2" enctype="multipart/form-data" target='if-test'>
	<table>
		<tr>
			<td>Do delete: <input type='checkbox' id='chk-do-delete' name='do_delete'></td>
		</tr>
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
			 
			
					
					if(!$(this).valid()){
						alert('Please check all required fields');
						return false;
					}else{
						var do_delete = ($("#chk-do-delete").prop('checked'));
						var result = true;
						
						if(do_delete==true)
							result = confirm('Are you sure you want to delete all records from this file?');
						
			
						if(result ==true){
							$("#btn-upload-activator").val('please wait').prop('disabled',true);
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
									alert('this is the error '+resp);
									alert('Error!!!');
									$("#div-messages").html(resp);
									
								 }
									$("#btn-upload-activator").val('upload').removeProp('disabled'); 
								} 
							}
							
							xhr.open('POST', form.getAttribute('action'), true);
							xhr.send(formData);
						}  
				}
				return false; // To avoid actual submission of the form
				
			});
			
		}) 
</script>