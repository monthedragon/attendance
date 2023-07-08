<?php 
function drawCheckbox($value, $expected_val){
	$checked = ($value == $expected_val ) ? 'checked=checked'  : '';
	return  "<input readonly  type=checkbox {$checked}>";
}
?>

<input type='button' class='no-print red-button' value = 'PRINT' onclick='window.print()'>
<input type='button' class='no-print red-button' value = 'EDIT' onclick='window.location="<?=base_url()?>main/edit/<?=$record_id?>"'>
<div id='print_div_holder' style='width:50%'>

<table width=100%>
	<tr>
		<td style='text-align:right'><?=img('assets/images/dti_logo.png',FALSE,80)?></td>
		<td style='text-align:center'>
			<b>
			DTI and DOLE: INTERIM GUIDELINES ON
			<br>
			WORKPLACE PREVENTION AND CONTROL OF COVID-19
			</b>
		</td>
		<td><?=img('assets/images/logo_2.png',FALSE,80)?></td>
	</tr>
</table>
<table  width=100%>  
	<tr>
		<td>All visitors shall accomplish the visitor’schecklist</td>
		<td rowspan = 2 style='border: 2px solid black; text-align:center;'> <h3>Temperature: <?=$info['c_temperature']?></h3></td>
	</tr>
	<tr>
		<td colspan = 2 style='font-size:15px;;'><b>Health Checklist</b></td>
	</tr>
</table>
<br>
<table width=100%>	
	<tr>
		<td style='border-bottom:1px solid white;width:100px;'>Name:</td>
		<td style='border-bottom:1px solid black'><?=strtoupper($info['firstname']. ' ' . $info['lastname']);?></td>	
		<td style='border-bottom:1px solid white;width:30px;'>Sex</td>
		<td style='border-bottom:1px solid black'>
			<?=isset($gender[$info['c_gender']]) ? strtoupper($gender[$info['c_gender']]) : '&nbsp;'?>
		</td>
		<td style='border-bottom:1px solid white;width:20px;'>Age</td>
		<td style='border-bottom:1px solid black'><?=$info['c_age']?></td>
	</tr>
	<tr>
		<td>Residence</td>
		<td  colspan=6 style='border-bottom:1px solid black'><?=strtoupper($info['c_address'])?></td>
	</tr>
</table>
<table  width=100%>	
	<tr>
		<td rowspan=2>Nature of Visit <br> (Please check one)</td>
		<td >
			Official
		</td>
		<td >
			<?
				$official_checked = ($info['c_nature_of_visit'] == 'official') ? 'checked=checked' : '';
			?>
			<input type=checkbox <?=$official_checked?>>
		</td>
		<td rowspan=2>
			<b>If official, fill-in company details below</b>
		</td>
	</tr>
	<tr>
		<td >
			Personal
		</td>
		<td >
			<?
				$personal_checked = ($info['c_nature_of_visit'] == 'personal') ? 'checked=checked' : '';
			?>
			<input type=checkbox <?=$personal_checked?>>
		</td>
	</tr>

</table>
<table width=100%>	
	<tr>
		<td style='border-bottom:1px solid white;width:100px;'>Company Name:</td>
		<td style='border-bottom:1px solid black'>
			<?=strtoupper($info['c_company_name'])?>
		</td>	
	</tr>
	<tr>
		<td>Company Address</td>
		<td style='border-bottom:1px solid black'>
			<?=strtoupper($info['c_company_address'])?>
		</td>
	</tr>
</table>

<table style='border:1px solid black; margin-top:20px;' border=1>
   <tbody>
      <tr>
         <td rowspan="5" width="198" valign=top>
            <p>&nbsp;</p>
            <p>1.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Are you experiencing:</p>
            <p>(<em>nakakaranas ka ba ng:</em>)</p>
         </td>
         <td width="322">
            <p>&nbsp;</p>
         </td>
         <td width="46">
            <p>Yes</p>
         </td>
         <td width="57">
            <p>No</p>
         </td>
      </tr>
      <tr>
         <td width="322">
            <p>a.Sore throat</p>
            <p>(<em>pananakit ng lalamunan / masakit lumunok</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_sore_throat'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_sore_throat'],'no')?>
         </td>
      </tr>
      <tr>
         <td width="322">
            <p>b. Body pains (<em>pananakit ng katawan</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_body_pain'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_body_pain'],'no')?>
         </td>
      </tr>
      <tr>
         <td width="322">
            <p>c. Headache (<em>pananakit ng ulo</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_headache'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_headache'],'no')?>
         </td>
      </tr>
      <tr>
         <td width="322">
            <p>d. Fever for the past few days</p>
            <p>(<em>Lagnat sa nakalipas na mga araw</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_fever'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_fever'],'no')?>
         </td>
      </tr>
      <tr>
         <td colspan="2" width="519">
            <p>2. Have you worked &nbsp;together or stayed &nbsp;in the same close environment of a confirmed COVID-19 case? (<em>May nakasama ka ba o nakatrabahong tao na kumpirmadong may COVID-19 / may impeksyon ng coronavirus?</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_worked_stayed_positive'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_worked_stayed_positive'],'no')?>
         </td>
      </tr>
      <tr>
         <td colspan="2" width="519">
            <p>3. Have you had &nbsp;any contact with anyone with fever, cough, colds, and sore throat &nbsp;in the past 2 weeks? (<em>Mayroon ka bang nakasama na may lagnat, ubo, sipon &nbsp;o sakit &nbsp;ng lalamunan sa nakalipas ng dalawang (2) lingo?</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_has_contact'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_has_contact'],'no')?>
         </td>
      <tr>
         <td colspan="2" width="519">
            <p>4. Have you travelled outside of &nbsp;the Philippines in the last 14 days?&nbsp; (<em>Ikaw ba ay nagbyahe sa labas ng Pilipinas sa nakalipas na 14 na araw?</em>)</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_travel_ph'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_travel_ph'],'no')?>
         </td>
      </tr>
      <tr>
         <td colspan="2" width="519">
            <p>5. Have you travelled to any area in NCR aside from your home?</p>
            <p>(<em>Ikawba ay nagpunta sa iba pang parte ng NCR o Metro Manila bukod sa iyong bahay?</em>) Specify (<em>Sabihin kung saan</em>): 
			<u><?=strtoupper($info['c_travel_ncr_location']);?></u>
			</p>
         </td>
         <td width="46">
			<?=drawCheckbox($info['c_travel_ncr'],'yes')?>
         </td>
         <td width="57">
			<?=drawCheckbox($info['c_travel_ncr'],'no')?>
         </td>
      </tr>
   </tbody>
</table>	

<br>
<p style='width:700px'>
I hereby authorize <u>Teleprime Solutions, Inc.</u>, 
to collect and process the data indicated  herein for the purpose of effecting control of the COVID-19 infection.
I understand that my personal information  is protected  by RA 10173, 
Data Privacy Act of 2012, and  that I am  required by RA 11469, 
Bayanihan to Heal as One Act, to provide truthful information.
</p>

<table width=100% >
	<tr>
		<td style="width:20px"> Signature:</td>
		<td style='border-bottom:1px solid black'><?=strtoupper($info['firstname']. ' ' . $info['lastname']);?></td>
		<td style="width:30%"></td>
		<td style="width:20px"> Date:</td>
		<td style='border-bottom:1px solid black'> 
		<?=strtoupper(date('F d, Y',strtotime($info['date_entered'])))?>
		</td>
	</tr>
</table>
	
</div>

<script>
$(function(){
	$('form').submit(function(){
		if(!$(this).valid()){
			return false;
		}
	})
	
	$('.dup_check').change(function(){
		var querystr = '';
		var do_search = true;
		
		$('.dup_check').each(function(){
			var val = $(this).val();
			var name = $(this).prop('name');
			
			if(val == ''){
				do_search = false; //if at least one of the fields are empty dont search
			}
			
			querystr += '&' + name + '=' + val;
		})
		
		if(do_search){
			var url = '<?=base_url()?>main/check_online_duplicate';
			var type = 'GET'
			do_ajax(url,type,querystr,'div_dup_list')
		}
		
	})
})
</script>