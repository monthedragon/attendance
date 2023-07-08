<div class='page-header'>add supplementary cards</div>
 
<form id='frm-add-supple'>
  
	<table>  
		<tr>
			<td>
				<label for="firstname">firstname</label>
				<?=input('firstname','firstname','input','required obj-conditional',((count($detail) > 0) ? $detail[0]['firstname'] :''),'')?>
			</td>
			<td>
				<label for="middlename">middlename</label>
				<?=input('middlename','middlename','input','',((count($detail) > 0) ? $detail[0]['middlename'] : ''),'')?>
			</td>
			<td>
				<label for="lastname">lastname</label>
				<?=input('lastname','lastname','input','required obj-conditional',((count($detail) > 0) ? $detail[0]['lastname'] : ''),'')?>
			</td>
		</tr>	
		   
		<tr>
			<td>
				<label for="dob">dob</label>
				<?=input('dob','dob','input','required obj-conditional dob',((count($detail) > 0) ? $detail[0]['dob'] : ''),'',"placeholder='mm/dd/yyyy'")?> 
			</td> 
			<td>
				<label for="gender">gender</label>
				<?=select('gender','gender','required obj-conditional',((count($detail) > 0) ? $detail[0]['gender'] : ''),null,null,$gender)?> 
			</td> 
		</tr>


        <tr>
            <td>
                <label for="relationship">relatioship to principal</label>
                <?=select('relationship','relationship','required obj-conditional',((count($detail) > 0) ? $detail[0]['relationship'] : ''),null,null,$relationship)?>
            </td>
            <td>
                <label for="relationship">other relationsip</label>
                <?=input('other_relationship','other_relationship','input',' ',((count($detail) > 0) ? $detail[0]['other_relationship'] : ''),'')?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="place_of_birth">place of birth</label>
                <?=input('place_of_birth','place_of_birth','input','required obj-conditional ',((count($detail) > 0) ? $detail[0]['place_of_birth'] : ''),'')?>
            </td>
            <td>
                <label for="nationality">nationality</label>
                <?=input('nationality','nationality','input','required obj-conditional ',((count($detail) > 0) ? $detail[0]['nationality'] : ''),'')?>
            </td>
        </tr>
		
		<tr>
			<td>
				<label for="home_no">tel no.</label>
				<?=input('home_no','home_no','input','required obj-conditional',((count($detail) > 0) ? $detail[0]['home_no'] : ''),'')?>
			</td> 
			<td>
				<label for="office_no">office no.</label>
				<?=input('office_no','office_no','input','',((count($detail) > 0) ? $detail[0]['office_no'] : ''),'')?> 
			</td>
			<td>
				<label for="mobile_no">mobile no.</label>
				<?=input('mobile_no','mobile_no','input','',((count($detail) > 0) ? $detail[0]['mobile_no'] : ''),'')?>
			</td> 
		</tr> 
		
		
		
		<tr>
			<td>
				<label for="employment">employment</label>
				<?=select('employment','employment','',((count($detail) > 0) ? $detail[0]['employment'] : ''),null,null,$employment)?> 
			</td> 
			<td>
				<label for="comp_name">company name</label>
				<?=input('comp_name','comp_name','input','',((count($detail) > 0) ? $detail[0]['comp_name'] : ''),'')?> 
			</td>
		</tr> 
		 
		<tr> 
			<td colspan=2>
				<label for="comp_add">compay address</label>
				<?=input('comp_add','comp_add','input','',((count($detail) > 0) ? $detail[0]['comp_add'] : ''),'','',30,50)?>
			</td> 
			<td>
				<label for="comp_city">compay city</label>
				<?=input('comp_city','comp_city','input','',((count($detail) > 0) ? $detail[0]['comp_city'] : ''),'','',30,50)?>
			</td> 
		</tr>
		
		<tr>
			<td>
				<label for="email_add">email address</label>
				<?=input('email_add','email_add','input','',((count($detail) > 0) ? $detail[0]['email_add'] : ''))?>  
			</td> 
			<td>
				<label for="occupation_pos">occupation / position</label>
				<?=input('occupation_pos','occupation_pos','input','',((count($detail) > 0) ? $detail[0]['occupation_pos'] : ''),'')?> 
			</td>
			<td>
				<label for="assigned_spend_limit">assigned spend limit</label>
				<?=input('assigned_spend_limit','assigned_spend_limit','input','required',((count($detail) > 0) ? $detail[0]['assigned_spend_limit'] : ''),'','',30,50)?>
			</td> 
		</tr> 
		 
		<tr>
			<td valign=top>
					<input type='submit' id='btnSubmit' value=' <?=($suppleID == 0 ) ? 'add supple' : 'update supple'?> '> 
			</td>
		</tr>
	</table> 
	
</form>

<div id='div-add-msg'></div>

<script>
	$(document).ready(function(){
		$("#frm-add-supple").submit(function(){
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>main/save_supple/<?=$id?>/<?=$suppleID?>',
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
		 
		 $(".dob").mask("99/99/9999");
	})
</script>