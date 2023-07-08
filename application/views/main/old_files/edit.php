<?if(!is_array($detail)){?> 
	<span class='warning'><?=$errMsg?></span>
<?exit();}?>

<!---
111913 admin account update
UPDATE users SET user_name = 'admin', user_password =MD5('admin'),firstname='Administrator',middlename='',lastname='' WHERE id = 1;
update `privilege` set user_id = 'admin' where user_id = 'mon';

112113 no required during enrollment
As per SIr. Mark no required fields are needed since this will be verified first by SV
-->

<h2><?=CLIENT_NAME?></h2>
 

<iframe style='display:none' id='if-test'></iframe>
<div id='div-attachments'></div>

<?
    $htmlPagesRd = '';//radio holder
    $htmlPagesLbl = '';//label holder
    $divPages = array(
        1=>'Applicant info',
        2=>'MGM',
        3=>'Mother info',
        4=>'Address',
        5=>'Financial & Car',
//        6=>'Car',
        7=>'Spouse',
        8=>'Billing',
        9=>'Other',
        10=>'Cards',
        11=>'Supple',);
$width = number_format(100/(count($divPages)),2);
foreach($divPages as $page=>$label){
    $htmlPagesRd.="<td class='td-navigator td-navigator-chk' rd-target='rd-$page' width='{$width}%'><input type='radio' name='pages' class='rd-paging' value='$page' id='rd-{$page}'></td>";
    $htmlPagesLbl.="<td class='td-navigator' rd-target='rd-$page'> $label</td>";
}
?>


<div class='div-given-info'>
    Name: <?=$detail[0]['firstname'] . ' ' . $detail[0]['lastname']?> @ <?=$detail[0]['telno']?>  / <?=$detail[0]['mobileno']?>  / <?=$detail[0]['officeno']?>
    <br>
    Credit LIMIT: <?=$detail[0]['credit_limit']?><br>
    Available Credit LIMIT: <?=$detail[0]['ava_cred_limit']?><br>
    Bill Cycle: <?=$detail[0]['bill_cycle']?><br>
    Double CL: <?=$detail[0]['double_cl']?><br>
</div>


<table width=100% id='tbl-navigator'>
    <tr><?=$htmlPagesRd?></tr>
    <tr><?=$htmlPagesLbl?></tr>
</table>

<form id='frm-edit-contact'>  


<fieldset class='fs-info fs-form fs-1'>
<legend>APPLICANT INFORMATION</legend>
	<table> 
		<tr>
			<td>
				<label for="c_card_type">card type</label>
                <!--111913 revision-->
                <?=input('c_card_type','c_card_type','input','obj-conditional ',$detail[0]['c_card_type'],$detail[0]['c_card_type'])?>
			</td> 
		</tr> 
		<tr>
			<td>
				<label for="c_firstname">firstname</label>
				<?=input('c_firstname','c_firstname','input','obj-conditional ',$detail[0]['c_firstname'],$detail[0]['firstname'])?>
			</td>
			<td>
				<label for="c_middlename">middlename</label>
				<?=input('c_middlename','c_middlename','input','',$detail[0]['c_middlename'],$detail[0]['middlename'])?>
			</td>
			<td>
				<label for="c_lastname">lastname</label>
				<?=input('c_lastname','c_lastname','input','obj-conditional ',$detail[0]['c_lastname'],$detail[0]['lastname'])?>
			</td>
		</tr>	
		 
		<tr>
			<td colspan=3>
				<label for="c_name_in_card">name to appear on card</label>
				<?=input('c_name_in_card','c_name_in_card','input','obj-conditional ',$detail[0]['c_name_in_card'],'','',80,20)?>
			</td>
		</tr>
		 
		<tr>
			<td>birthdate</td>
			<td>gender</td>
		</tr>
		<tr>
			<td><?=input('c_dob','c_dob','input','obj-conditional  dob',$detail[0]['c_dob'],'',"placeholder='mm/dd/yyyy'")?> </td>
			<td><?=select('c_gender','c_gender',' obj-conditional ',$detail[0]['c_gender'],null,null,$gender)?> </td>
		</tr>
		
		
		<tr>
			<td>civil status</td>
            <td>nationality</td>
            <td>if US citizen (US TIN)</td>
			<td>place of birth</td>
		</tr>
		<tr>
			<td><?=input('c_civil_status','c_civil_status','input','obj-conditional  ',$detail[0]['c_civil_status'],'')?> </td>
            <td><?=input('c_nationality','c_nationality','input','obj-conditional  ',$detail[0]['c_nationality'],'')?> </td>
            <td><?=input('c_us_tin','c_us_tin','input','  ',$detail[0]['c_us_tin'],'')?> </td>
			<td><?=input('c_place_of_birth','c_place_of_birth','input','obj-conditional  ',$detail[0]['c_place_of_birth'],'')?> </td>
		</tr>

        <tr>
            <td>
                <label for="c_homeno">tel no.</label>
                <?=input('c_homeno','c_homeno','input',' obj-conditional ',$detail[0]['c_homeno'],$detail[0]['telno'])?>
            </td>
            <td>
                <label for="c_mobileno">mobile no.</label>
                <?=input('c_mobileno','c_mobileno','input','',$detail[0]['c_mobileno'],$detail[0]['mobileno'])?>
            </td>
            <td>
                <label for="c_officeno">office no.</label>
                <?=input('c_officeno','c_officeno','input','',$detail[0]['c_officeno'],$detail[0]['officeno'])?>
            </td>
            <td>
                   <!--
                    111913 revision
                    added new column c_other_no
                    ALTER TABLE contact_list ADD COLUMN c_other_no CHAR(100) NOT NULL
                   -->
                <label for="c_officeno">other no.</label>
                <?=input('c_other_no','c_other_no','input','',$detail[0]['c_other_no'],$detail[0]['c_other_no'])?>
            </td>
        </tr>

        <tr>
            <td >
                <label for="c_process_cash_loan">process cash loan?</label>
                <?=select('c_process_cash_loan','c_process_cash_loan','',$detail[0]['c_process_cash_loan'],null,null,$yesno)?>
            </td>
			<td >
                <label for="c_location">location?</label>
                <?=select('c_location','c_location','required ',$detail[0]['c_location'],null,null,$locations)?>
            </td>
			<td >
                <label for="c_dialled_no">dialled no</label>
                <?=input('c_dialled_no','c_dialled_no','input','required',$detail[0]['c_dialled_no'],$detail[0]['c_dialled_no'])?>
            </td>
        </tr>
        <tr>
            <td valign=top>
                <label for="c_amount_loan">amount loan</label>
                <?=input('c_amount_loan','c_amount_loan','input','',$detail[0]['c_amount_loan'],$detail[0]['c_amount_loan'])?>
            </td>
            <td valign=top>
                <label for="c_payment_terms">payment terms</label>
                <?=input('c_payment_terms','c_payment_terms','input','',$detail[0]['c_payment_terms'],$detail[0]['c_payment_terms'])?>
            </td>
            <td valign=top>
                <label for="c_icl_request">icl request</label>
                <?=input('c_icl_request','c_icl_request','input','',$detail[0]['c_icl_request'],$detail[0]['c_icl_request'])?>
            </td>
            <td>
                <label for="c_other_request">other request</label>
                <?=textarea('c_other_request','c_other_request','',$detail[0]['c_other_request'],'','',2,50)?>
				<!--?=input('c_other_request','c_other_request','input','',$detail[0]['c_other_request'],$detail[0]['c_other_request'])?-->
            </td>
        </tr>
    </table>
</fieldset>

<fieldset class='fs-info fs-form fs-2'>
<legend>MGM (Member get Member)</legend>
    <table>
		<tr>
			<td >
				<label for="c_mgm">Enroll for MGM?</label>
				<?=select('c_mgm','c_mgm','sel-prod',$detail[0]['c_mgm'],null,null,$yesno)?>
			</td>
			<td colspan=5>
				<div id='div_c_mgm' class='<?=(($detail[0]['c_mgm']!='2') ? 'hidden' : '') ?>'>
					<label for="c_mgm">NI for MGM</label>
					<?=select('c_mgm_ni','c_mgm_ni','',$detail[0]['c_mgm_ni'],null,null,$ni)?>
				</div>
			</td>
		</tr>
        <tr>
            <td>
                <label for="c_mgm_firstname">firstname</label>
                <?=input('c_mgm_firstname','c_mgm_firstname','input',' ',$detail[0]['c_mgm_firstname'],'')?>
            </td>
            <td>
                <label for="c_mgm_middlename">middlename</label>
                <?=input('c_mgm_middlename','c_mgm_middlename','input',' ',$detail[0]['c_mgm_middlename'],'')?>
            </td>
            <td>
                <label for="c_mgm_lastname">lastname</label>
                <?=input('c_mgm_lastname','c_mgm_lastname','input',' ',$detail[0]['c_mgm_lastname'],'')?>
            </td>
            <td>
                <label for="c_mgm_contact_no">contact no.</label>
                <?=input('c_mgm_contact_no','c_mgm_contact_no','input',' ',$detail[0]['c_mgm_contact_no'],'')?>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset class='fs-info fs-form fs-3'>
<legend>MOTHER'S FULL MAIDEN NAME</legend>
	<table>  
	 <tr>
		<td>
			<label for="c_mother_firstname">firstname</label>
			<?=input('c_mother_firstname','c_mother_firstname','input','obj-conditional ',$detail[0]['c_mother_firstname'],'')?>
		</td>
		<td>
			<label for="c_mother_middlename">middlename</label>
			<?=input('c_mother_middlename','c_mother_middlename','input','',$detail[0]['c_mother_middlename'],'')?>
		</td>
		<td>
			<label for="c_mother_lastname">lastname</label>
			<?=input('c_mother_lastname','c_mother_lastname','input','obj-conditional ',$detail[0]['c_mother_lastname'],'')?>
		</td>
	</tr> 
	</table>
</fieldset>  

<fieldset class='fs-info fs-form fs-4'>
<legend>ADDRESS DETAILS</legend>
	<fieldset class='fs-info-child '>
	<legend class='child'>present home</legend>
		<table>  
		 <tr>
			<td>
                   <!--
                   111913 revision
                   change the label of all addresses
                   -->
				<label for="c_present_add1">ADD 1 <span class='note'>(Unit/Floor/Bldg Name/No./Street)</span></label>
				<?=input('c_present_add1','c_present_add1','input','obj-conditional ',$detail[0]['c_present_add1'],'','',30,200)?>
			</td>
			<td>
				<label for="c_present_add2">ADD 2 <span class='note'>(Subd/Brgy/Municipality/Town)</span></label>
				<?=input('c_present_add2','c_present_add2','input','',$detail[0]['c_present_add2'],'','',30,200)?>
			</td>

             <!--
            111913 revisions
            deleted address 3 excess field
			<td>
				<label for="c_present_add3">City/Province</label>
				<!--?=input('c_present_add3','c_present_add3','input',' ',$detail[0]['c_present_add3'],'','',30,30)?>
			</td-->

			<td>
				<label for="c_present_city">ADD 3 <span class='note'>(City/Province)</span ></label>
				<?=input('c_present_city','c_present_city','input',' obj-conditional ',$detail[0]['c_present_city'],'','',30,200)?>
			</td>
			<td>
				<label for="c_present_zip">zip</label>
				<?=input('c_present_zip','c_present_zip','input',' obj-conditional ',$detail[0]['c_present_zip'],'','',5,5)?>
			</td>
		</tr> 
		</table>
	</fieldset>

	<fieldset class='fs-info-child'>
	<legend class='child'>permanent home</legend>
		<table>  
		 <tr>
			<td>
				<label for="c_perma_add1"> ADD 1 <span class='note'>(Unit/Floor/Bldg Name/No./Street)</span></label>
				<?=input('c_perma_add1','c_perma_add1','input',' obj-conditional ',$detail[0]['c_perma_add1'],'','',30,200)?>
			</td>
			<td>
				<label for="c_perma_add2"> ADD 2 <span class='note'>(Subd/Brgy/Municipality/Town)</span></label>
				<?=input('c_perma_add2','c_perma_add2','input','',$detail[0]['c_perma_add2'],'','',30,200)?>
			</td>

            <!--
            111913 revisions
            deleted address 3 excess field
            <!--td>
               <label for="c_perma_add3">City/Province</label>
               <!--?=input('c_perma_add3','c_perma_add3','input',' ',$detail[0]['c_perma_add3'],'','',30,30)?>
			</td-->


           <td>
               <label for="c_perma_city">ADD 3 <span class='note'>(City/Province)</span></label>
               <?=input('c_perma_city','c_perma_city','input',' obj-conditional ',$detail[0]['c_perma_city'],'','',30,200)?>
           </td>

			<td>
				<label for="c_perma_zip">zip</label>
				<?=input('c_perma_zip','c_perma_zip','input',' obj-conditional',$detail[0]['c_perma_zip'],'','',5,5)?>
			</td>
			<td>
				<?=input('btn_same','btn_same','button','','same as present address')?>
			</td>
		</tr> 
		</table>
	</fieldset>  

	<fieldset class='fs-info-child '>
	<legend class='child'>home ownership</legend>
		<table>  
		 <tr>
			<td>
				<label for="c_year_stay">year of stay</label>
				<?=input('c_year_stay','c_year_stay','input','',$detail[0]['c_year_stay'])?>
			</td>
			<td>
				<label for="c_month_stay">month of stay</label>
				<?=input('c_month_stay','c_month_stay','input','',$detail[0]['c_month_stay'])?>
			</td>
             <td>
                 <label for="c_home_ownership">ownership</label>
                 <?=select('c_home_ownership','c_home_ownership','',$detail[0]['c_home_ownership'],null,null,$homeOwnership)?>
             </td>
		</tr>
        <tr>
            <td colspan=3>
                <label for="c_stayed_in_us">HAVE YOU STAYED IN THE USA FOR 180 DAYS IN THE LAST 3 YRS (YES/NO)</label>
                <?=select('c_stayed_in_us','c_stayed_in_us','',$detail[0]['c_stayed_in_us'],null,null,$yesno)?>
            </td>
        </tr>
		</table>	
	</fieldset >
</fieldset>


<fieldset class='fs-info fs-form fs-5'>
<legend>FINANCIAL STATUS / EMPLOYMENT</legend>
	<table>  
	 <tr>
		<td>
			<label for="c_employment">employment</label>
			<?=input('c_employment','c_employment','input','comp_details',$detail[0]['c_employment'],'')?>
		</td>
		<td colspan=3>
			<label for="c_company_name">company name</label>
			<?=input('c_company_name','c_company_name','input','comp_details',$detail[0]['c_company_name'],'','',50)?>
		</td> 
	</tr> 
	<tr>
        <!--
        111913 revisions
        change all label for address
        -->
		<td>
			<label for="c_comp_add1"> CO. ADD 1 <span class='note'>(Unit/Floor/Bldg Name/No./Street/Dept)</span></label>
			<?=input('c_comp_add1','c_comp_add1','input','comp_details ',$detail[0]['c_comp_add1'],'','',30,200)?>
		</td>
		<td>
			<label for="c_comp_add2">CO. ADD 2 <span class='note'>(Subd/Brgy/Municipality/Town)</span> </label>
			<?=input('c_comp_add2','c_comp_add2','input','',$detail[0]['c_comp_add2'],'','',30,200)?>
		</td>

        <!-- 111913 revision
            remove address 3 excess field
        -->
		<!--td>
			<label for="c_comp_add3">company address 3</label>
			<!--?=input('c_comp_add3','c_comp_add3','input',' ',$detail[0]['c_comp_add3'],'','',30,30)?>
		</td-->

		<td>
			<label for="c_comp_city">ADD 3 <span class='note'>(City/Province)</span></label>
			<?=input('c_comp_city','c_comp_city','input','comp_details ',$detail[0]['c_comp_city'],'','',30,200)?>
		</td> 
		<td>
			<label for="c_comp_zip">company zip</label>
			<?=input('c_comp_zip','c_comp_zip','input','comp_details ',$detail[0]['c_comp_zip'],'','',5,5)?>
		</td> 
	</tr> 
	 <tr>
		<td>
			<label for="c_comp_year_stay">year of stay</label>
			<?=input('c_comp_year_stay','c_comp_year_stay','input','',$detail[0]['c_comp_year_stay'],'')?>
		</td>
		<td>
			<label for="c_comp_month_stay">month of stay</label>
			<?=input('c_comp_month_stay','c_comp_month_stay','input','',$detail[0]['c_comp_month_stay'])?>
		</td> 
	</tr> 
	
	<tr>
		<td>
			<label for="c_comp_phone">office phone number</label>
			<?=input('c_comp_phone','c_comp_phone','input','comp_details ',$detail[0]['c_comp_phone'],'')?>
		</td> 
		<td>
			<label for="c_comp_fax">fax number</label>
			<?=input('c_comp_fax','c_comp_fax','input','',$detail[0]['c_comp_fax'],'')?>
		</td>  
	</tr> 
	<tr>
		<td>
			<label for="c_comp_nature_bus">nature of business</label>
			<?=input('c_comp_nature_bus','c_comp_nature_bus','input',' ',$detail[0]['c_comp_nature_bus'],'')?>
		</td> 
		<td>
			<label for="c_occupation_pos">occupation / position</label>
			<?=input('c_occupation_pos','c_occupation_pos','input','',$detail[0]['c_occupation_pos'],'')?>
		</td>  
		<td>
			<label for="c_tgai_souce_fund">total gross annual income / source of fund</label>
			<?=input('c_tgai_souce_fund','c_tgai_souce_fund','input','',$detail[0]['c_tgai_souce_fund'],'')?>
		</td>  
	</tr> 
	</table>

    <fieldset class='fs-info-child'>
        <legend>CAR OWNERSHIP</legend>
        <table>
            <tr>
                <td>
                    <label for="c_is_own_car">own a car?</label>
                    <?=select('c_is_own_car','c_is_own_car','',$detail[0]['c_is_own_car'],null,null,$yesno)?>
                </td>
                <td>
                    <label for="c_how_many_car">how many</label>
                    <?=input('c_how_many_car','c_how_many_car','input','',$detail[0]['c_how_many_car'],'')?>
                </td>
                <td>
                    <label for="c_car_ownership">car ownership</label>
                    <?=select('c_car_ownership','c_car_ownership','',$detail[0]['c_car_ownership'],null,null,$carOwnership)?>
                </td>
            </tr>
        </table>
    </fieldset>

</fieldset>  




<fieldset class='fs-info fs-form fs-7'>
<legend>SPOUSE INFORMATION</legend>
	<table> 
		 
		<tr>
			<td>
				<label for="c_sp_firstname">firstname</label>
				<?=input('c_sp_firstname','c_sp_firstname','input',' ',$detail[0]['c_sp_firstname'],'')?>
			</td>
			<td>
				<label for="c_sp_middlename">middlename</label>
				<?=input('c_sp_middlename','c_sp_middlename','input','',$detail[0]['c_sp_middlename'],'')?>
			</td>
			<td>
				<label for="c_sp_lastname">lastname</label>
				<?=input('c_sp_lastname','c_sp_lastname','input',' ',$detail[0]['c_sp_lastname'],'')?>
			</td>
		</tr>	
		  
		  
		<tr>
			<td>
				<label for="c_sp_dob">birthdate</label>
				<?=input('c_sp_dob','c_sp_dob','input','  dob',$detail[0]['c_sp_dob'],'',"placeholder='mm/dd/yyyy'")?> 
			</td> 
	 
			<td>
				<label for="c_sp_homeno">home phone no.</label>
				<?=input('c_sp_homeno','c_sp_homeno','input',' ',$detail[0]['c_sp_homeno'],'')?>
			</td> 
			<td>
				<label for="c_sp_mobile_no">mobile no.</label>
				<?=input('c_sp_mobile_no','c_sp_mobile_no','input','',$detail[0]['c_sp_mobile_no'],'')?>
			</td>  
		</tr> 
		
		 
		<tr>
			<td>
				<label for="c_sp_employment_sta">employment</label>
				<?=select('c_sp_employment_sta','c_sp_employment_sta','',$detail[0]['c_sp_employment_sta'],null,null,$spEmployment)?> 
			</td> 
			<td colspan=2>
				<label for="c_sp_employment_other">if other</label>
				<?=input('c_sp_employment_other','c_sp_employment_other','input',' ',$detail[0]['c_sp_employment_other'],'','',50,50)?>
			</td> 
		</tr>
		
		<tr>
			<td colspan=3>
				<label for="c_sp_company_name">company name</label>
				<?=input('c_sp_company_name','c_sp_company_name','input',' ',$detail[0]['c_sp_company_name'],'','',50,50)?>
			</td> 
		</tr>
		<tr>
			<td colspan=3>
				<label for="c_sp_company_add">company address</label>
				<?=input('c_sp_company_add','c_sp_company_add','input',' ',$detail[0]['c_sp_company_add'],'','',50,200)?>
			</td> 
		</tr>
		<tr>
			<td>
				<label for="c_sp_email_add">email address</label>
				<?=input('c_sp_email_add','c_sp_email_add','input',' email ',$detail[0]['c_sp_email_add'])?>
			</td> 
			<td>
				<label for="c_sp_occupation_pos">occupation / position</label>
				<?=input('c_sp_occupation_pos','c_sp_occupation_pos','input','',$detail[0]['c_sp_occupation_pos'],'')?>
			</td>  
			<td>
				<label for="c_sp_tgai_source_fund">total gross annual income / source of fund</label>
				<?=input('c_sp_tgai_source_fund','c_sp_tgai_source_fund','input','',$detail[0]['c_sp_tgai_source_fund'],'')?>
			</td>  
		</tr> 
	</table>
</fieldset> 

<fieldset class='fs-info fs-form fs-8'>
<legend>BILLING ADDRESS</legend>
	<table>  
	 <tr>
		<td> 
			<label for="c_bill_add">billing address?</label>
			<?=select('c_bill_add','c_bill_add',' obj-conditional',$detail[0]['c_bill_add'],null,null,$billingaddress)?>
		</td>
		<td colspan =2>
			<label for="c_landmark">location landmark</label>
			<?=input('c_landmark','c_landmark','input','obj-conditional',$detail[0]['c_landmark'],'','',50,200)?>
		</td> 
		
		<td>
			<label for="c_is_e_statement">e-statement via email?</label>
			<?=select('c_is_e_statement','c_is_e_statement',' obj-conditional',$detail[0]['c_is_e_statement'],null,null,$yesno)?>
		</td> 
	</tr>  
	</table>

    <fieldset class=' fs-info-child '>
		<legend>AUTHORIZED PERSON</legend>
		    <table>
				<tr>
					<td>
						<label for="c_auth_firstname">firstname</label>
						<?=input('c_auth_firstname','c_auth_firstname','input',' obj-conditional ',$detail[0]['c_auth_firstname'],'')?>
					</td>
					<td>
						<label for="c_auth_middlename">middlename</label>
						<?=input('c_auth_middlename','c_auth_middlename','input','',$detail[0]['c_auth_middlename'],'')?>
					</td>
					<td>
						<label for="c_auth_lastname">lastname</label>
						<?=input('c_auth_lastname','c_auth_lastname','input',' obj-conditional ',$detail[0]['c_auth_lastname'],'')?>
					</td>
				</tr>  	
				<tr>
					<td>
						<label for="c_auth_contact_no">contact no.</label>
						<?=input('c_auth_contact_no','c_auth_contact_no','input',' obj-conditional ',$detail[0]['c_auth_contact_no'],'')?>
					</td> 
					<td>
						<label for="c_auth_mob_no">mobile no.</label>
						<?=input('c_auth_mob_no','c_auth_mob_no','input','',$detail[0]['c_auth_mob_no'],'')?>
					</td>  
				</tr>
			</table>
		
		</fieldset>

    <fieldset class='fs-info-child'>
        <legend>WEB SHOPPER</legend>
        <table>
            <tr>
                <td >
                    <label for="c_is_web_shopper">is web shopper?</label>
                    <?=select('c_is_web_shopper','c_is_web_shopper','sel-prod',$detail[0]['c_is_web_shopper'],null,null,$yesno)?>
                </td>
				<td colspan=5>
					<div id='div_c_is_web_shopper' class='<?=(($detail[0]['c_is_web_shopper']!='2') ? 'hidden' : '') ?>'>
						<label for="c_mgm">NI for WEB SHOPPER</label>
						<?=select('c_is_web_shopper_ni','c_is_web_shopper_ni','',$detail[0]['c_is_web_shopper_ni'],null,null,$ni)?>
					</div>
				</td>
            </tr>
        </table>
    </fieldset>

    <fieldset class='fs-info-child'>
        <legend>E-SOA</legend>
        <table>
            <tr>
                <td >
                    <label for="c_esoa">Enroll for E-SOA?</label>
                    <?=select('c_esoa','c_esoa','sel-prod',$detail[0]['c_esoa'],null,null,$yesno)?>
                </td>
				<td colspan=5>
					<div id='div_c_esoa' class='<?=(($detail[0]['c_esoa']!='2') ? 'hidden' : '') ?>'>
						<label for="c_mgm">NI for E-SOA</label>
						<?=select('c_esoa_ni','c_esoa_ni','',$detail[0]['c_esoa_ni'],null,null,$ni)?>
					</div>
				</td>
            </tr>
			<tr>
				<td>
					<div id='div-esoa-email' class='<?=(($detail[0]['c_esoa']!='1') ? 'hidden' : '') ?>'>
						<label for="c_esoa">Enroll for E-SOA?</label>
						<?=input('c_esoa_email','c_esoa_email','input','email',$detail[0]['c_esoa_email'],'')?>
					</div>
				</td>
			</tr>
        </table>
    </fieldset>


</fieldset>



<fieldset class='fs-info fs-form fs-9'>
<legend>OTHERS</legend>
	<table>  
	 <tr>
		<td>
			<label for="c_no_of_dep">no. of dependents</label>
			<?=input('c_no_of_dep','c_no_of_dep','input','',$detail[0]['c_no_of_dep'],'')?>
		</td>
		<td>
			<label for="c_tin">tin</label>
			<?=input('c_tin','c_tin','input','',$detail[0]['c_tin'],'')?>
		</td> 
		<td>
			<label for="c_sss_gsis">sss/gsis</label>
			<?=input('c_sss_gsis','c_sss_gsis','input','',$detail[0]['c_sss_gsis'],'')?>
		</td>
		<td>
			<label for="c_email_address">email address</label>
			<?=input('c_email_address','c_email_address','input','email',$detail[0]['c_email_address'],'')?>
		</td>
		<td>
			<label for="c_education_attain">educational attainment</label>
			<?=select('c_education_attain','c_education_attain','',$detail[0]['c_education_attain'],null,null,$educAttain)?> 
		</td>
	</tr> 
	</table>
</fieldset>  

<fieldset class='fs-info fs-form fs-10'>
<legend>CARD DETAILS</legend>
	<div id='div-card-details'></div>
</fieldset>  


<fieldset class='fs-info fs-form fs-11'>
<legend>SUPPLEMENTARY CARDS</legend>
	<div id='div-supple'></div>
</fieldset>

<h1 class="divider"> remarks </h1>

 <?if($action==0){?>
	<table> 
		<tr>
			<td colspan=5>
				<label for="callresult">remarks</label>
				<?=textarea('remarks','remarks')?>
			</td>
		</tr>		
		<tr>
			<td colspan = 5>
				<label for="callresult">callresult</label>
				<?=select('callresult','callresult','required','',null,null,$callresult)?> 
				<div id='div-add-on-result'></div>
                <div id='div-ag-type' style='display:none'>
                    <?=select('ag_type','ag_type','required','',null,null,$ag_type)?>
                </div>
			</td>
		</tr>
		<tr>
			<td valign=top> 
					<input type='submit' id='btnSubmit' value=' update '> 
			</td>
		</tr>
	</table> 
<?}else{?>
		<input type='button' id='btnBack' value=' back to list '>
<?}?>
</form>



<br><br>
<!--All agreement will be automatically send an invitation / or manual click?-->
<br>
<div id='div-history'></div>


<div id='div-details' style=' position: fixed;bottom: 0;right: 0;max-height:300px;max-width:400px;overflow:auto'>
    <div id='div-detail-ctr' style=' float:right;position: relative;right: 0;cursor:pointer;color:orange;clear:both'>toggle details</div>
    <div id='div-tbl-details' style='clear:both;background-color:white'>
        <table border = 1>
            <?
            if(count($displayFields) == 0){
                echo "<span class='warning'>no display fields set</span>";
            }
            $oldGroup = '';
            $ctr = 0;
            foreach($displayFields as $col=>$data){
                if(!isset($detail[0][$col])){
                    continue;
                }
                $groupName = trim(strtolower($data['display_group']));
                if($oldGroup != $groupName){
                    echo "<tr class='header'><td colspan=5>{$groupName}</td></tr>";
                    $oldGroup = $groupName;
                    $ctr=0;
                }
                if($ctr==0)
                    echo "<tr class=''>";

                ?>

                <td>
                    <label for='spn-<?=$col?>' style='font-size:10px'><?=(($data['mask_name']!='')?$data['mask_name'] : $col)?></label>
					<?if($col==CC_FIELD){
						$cc = str_replace('-','',$detail[0][$col]);
						$subCC = substr($cc,-4);
						$strpadCC = str_pad($subCC,16,'*',STR_PAD_LEFT);
						$display = $strpadCC;
					}else{
						$display = $detail[0][$col];
					}?>
					<span name='spn-<?=$col?>'><?=$display?>
                </td>
                <?
                $ctr++;
                if($ctr==3){
                    $ctr=0;
                    echo "</tr>";
                }

            }?>
        </table>
    </div>
</div>
<script>  
	 
	function getHistory(){
		$.ajax({
			url:'<?=base_url()?>main/get_history/<?=$detail[0]['id']?>',
			beforeSend:function(){$("#div-history").html('please wait...')},
			success:function(data){
				$("#div-history").html(data);
			}
		})
	}
	
	function getCardDetails(){
		$.ajax({
			url:'<?=base_url()?>main/get_card_details/<?=$detail[0]['id']?>/<?=$action?>',
			beforeSend:function(){$("#div-card-details").html('please wait...')},
			success:function(data){
				$("#div-card-details").html(data);
			}
		})
	}
	
	function getSuppleCards(){
		$.ajax({
			url:'<?=base_url()?>main/get_supple/<?=$detail[0]['id']?>/<?=$action?>',
			beforeSend:function(){$("#div-supple").html('please wait...')},
			success:function(data){
				$("#div-supple").html(data);
			}
		})
	}
	
	$(document).ready(function(){
		getHistory();
		getCardDetails();
		getSuppleCards();

        $('.fs-form').hide();
        $('.fs-1').show();
        $('#rd-1').attr('checked',true);
        $('#rd-1').closest('td').addClass('td-navigator-chk-selected');

        $('.td-navigator').click(function(){
            var rdTargetId = $(this).attr('rd-target');
            $('.rd-paging').removeAttr('checked');
            $('#tbl-navigator td').removeClass('td-navigator-chk-selected');
            $('#'+rdTargetId).prop('checked','checked').closest('td').addClass('td-navigator-chk-selected');
            $('#'+rdTargetId).click();
        })

        $('.rd-paging').click(function(){
            var fsID= $(this).val();
            $('.fs-form').hide();
            $('.fs-'+fsID).show();
            $('#tbl-navigator td').removeClass('td-navigator-chk-selected');
            $(this).closest('td').addClass('td-navigator-chk-selected');
        })

        $("#div-detail-ctr").click(function(){
            $("#div-tbl-details").toggle('slow');
        })
		
		$("#btnBack").click(function(){
			$("#div-contact-list").load('<?=base_url();?>main/search_contact/');
		})
		
		$("#frm-edit-contact").submit(function(){
			var callresult = $('#callresult').val();

            /* no required field as per advised by Sir mark as of 2013-11-21
             if(callresult != '<?=AG_TAG?>')
             $(".obj-conditional").removeClass('required');
             else
             $(".obj-conditional").addClass('required');
             */
            $('.fs-form').show();
            if($(this).valid()){

				$.ajax({
					url:'<?=base_url()?>main/save/<?=$detail[0]['id']?>',
					data:$(this).serialize(),
					type:'POST',
					beforeSend:function(){$("#btnSubmit").val('please wait...').prop('disabled',true)},
					success:function(data){
						$("#btnSubmit").val(' update ').removeProp('disabled');
						//getHistory();
						alert('Saved!');
						$("#div-contact-list").load('<?=base_url();?>main/search_contact/');
					}
				});
			}
			return false;
		});
		 
		
		$("#c_bill_add").change(function(){
			var val = $(this).val();
			
			if(val == 'office')
				$(".comp_details").addClass('required')
			else
				$(".comp_details").removeClass('required')
			
		})
		
		$("#btn_same").click(function(){
			$("#c_perma_zip").val($("#c_present_zip").val());
			$("#c_perma_city").val($("#c_present_city").val());
			$("#c_perma_add3").val($("#c_present_add3").val());
			$("#c_perma_add2").val($("#c_present_add2").val());
			$("#c_perma_add1").val($("#c_present_add1").val()); 
		})
		
		$("#callresult").change(function(){
			var val = $(this).val();

            if(val == 'AG'){
                $('#div-ag-type').show();
                $('#ag_type').addClass('required');
                $("#div-add-on-result").html('');
            }else if(val == '<?=CB_TAG?>' || val == '<?=NI_TAG?>'){
                $('#div-ag-type').hide();
                $('#ag_type').removeClass('required');

				$.ajax({
					url:'<?=base_url()?>main/get_sub_callresult/'+val,
					dataType:'json',
					beforeSend:function(){$("#btnSubmit").val('please wait...').prop('disabled',true);},
					error:function(){
						$("#div-add-on-result").html('');
					},
					complete:function(){$("#btnSubmit").removeProp('disabled').val(' update ');},
					success:function(data){ 
							
							//append the select object
							$("#div-add-on-result").html('').append("<select name='sub_callresult' class='required' id='sub-callresult'></select>");
							
							//remove any option generated from the obj
							$('#sub-callresult option').remove(); 
							
							$('#sub-callresult').append("<option value=''>--select--</option>");
							
							$.each(data, function(key,value){  
								
								$('#sub-callresult').append($('<option>', { 
									value: key,
									text : value 
								})); 
								
							})  

							//if callresult then append the callback date and time
							if(val == '<?=CB_TAG?>'){
								$("#div-add-on-result").append("<input type='input' id='callbackdate' name='callbackdate' class='required dateISO' readonly>");
								$("#callbackdate").datepicker({'dateFormat':'yy-mm-dd', 'minDate': 0});
								
								$("#div-add-on-result").append("<input type='input' id='callbacktime' name='callbacktime' class='required datetime' >").mask("99:99:99");
								$("#callbacktime").mask("99:99:99");
							}
					}
				})
				
			}else{
                $('#div-ag-type').hide();
                $('#ag_type').removeClass('required');
                $("#div-add-on-result").html('');
            }

			
			
		})
		
		$('.sel-prod').change(function(){
			var val = $(this).val();
			var target_obj = $(this).prop('id');
			
			if(val=='2'){ //if no
				$('#div_'+target_obj).show();
				$('#'+target_obj+'_ni').addClass('required');
			}else{
				$('#div_'+target_obj).hide();
				$('#'+target_obj+'_ni').val('').removeClass('required');
			}
		})
		
		$('#c_esoa').change(function(){
			var val = $(this).val();
			if(val == '1'){ //if no
				$('#div-esoa-email').show();
				$('#c_esoa_email').val('').addClass('required');
			}else{
				$('#div-esoa-email').hide();
				$('#c_esoa_email').val('').removeClass('required');
			}
		})
		
		$("#screening_date").datepicker({'dateFormat':'yy-mm-dd', 'minDate': 0});
		$(".dob").mask("99/99/9999");
	})
</script>
