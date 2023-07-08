<?
header('Content-Type: application/vnd.ms-excel'); //mime type
header('Content-Disposition: attachment;filename="'.$filename.'.xls"'); //tell browser what's the file name
header('Cache-Control: max-age=0');

$header = '<td>';
$body ='';
foreach($data as $keys=>$details){
    if($header == '<td>'){
        $detailsHeader = $details;
        unset($detailsHeader['contact_list_id']);
        $detailsHeader += array('supple count'=>0,'no of touch'=>0);
		if(isset($pdata['include_remarks'])){
			$detailsHeader += array('remarks'=>0);
		}
        $header .= implode('</td><td>',array_keys($detailsHeader));
    }

    $body.="<tr>";
    foreach($details as $col=>$detail){
        if($col=='contact_list_id'){
            $contact_list_id = $detail;
            continue;
        }
				
				$use_tl_cr = false;
				$tl_detail_arr = array();
				if(empty($details['callresult']) && isset($tran_log_det[$details['contact_list_id']])){
					$tl_detail_arr = $tran_log_det[$details['contact_list_id']];
					$use_tl_cr = true;
				}

        if($col=='sub_callresult'){
					
					if($use_tl_cr){ //if use trans_log details
						
						$detail = $tl_detail_arr['tl_sub_callresult'];
						$value = '';
						
						if(in_array($tl_detail_arr['tl_callresult'],$inSubCR)){
							$value = (isset($subCr[$detail]) ? $subCr[$detail] : $detail);
						}
						
					}else{
						
						$value = '';
						
						if(in_array($details['callresult'],$inSubCR)){
							$value = (isset($subCr[$detail]) ? $subCr[$detail] : $detail);	
						}
						
					}
            
        }elseif($col == 'ag_type' && $details['callresult'] == 'AG'){
            $value = (isset($ag_type[$detail]) ? $ag_type[$detail] : '');
        }elseif($col == 'callresult'){
					
					if($use_tl_cr){ //if use trans_log details
						$detail = $tl_detail_arr['tl_callresult'];
					}
						
					$value = (isset($cr[$detail]) ? $cr[$detail] : $detail);
					
        }elseif($col == 'c_other_request'){
            $value = nl2br($detail);
        }elseif($col == CC_FIELD){
            $cc = preg_replace("/[^A-Za-z0-9 ]/", '', $detail);
            $detail = implode("-", str_split($cc, 4));
            $value = "$detail";
        }else{
            $value = $detail;
        }


        $body .= "<td >$value</td>";

    }
		
    $suppleCTR = (isset($dataSupple[$contact_list_id]) ? $dataSupple[$contact_list_id] : 0);
    $dataCTRTouch = (isset($tran_log_det[$contact_list_id]) ? $tran_log_det[$contact_list_id]['CTR'] : 1);
    $body .= "<td>$suppleCTR</td><td>$dataCTRTouch</td>";

	if(isset($pdata['include_remarks'])){
		$remarks = (isset($agent_remarks[$contact_list_id]) ? $agent_remarks[$contact_list_id] : '');
		$body .= "<td>{$remarks}</td>";
	}
	
    $body.='</tr>';

}?>
<table border=1>
    <?=$header?>
    <?=$body?>
</table>