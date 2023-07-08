<?

	if($pending_leave_ctr > 0 || $pending_ot_ctr > 0){
		$html = "<span class='warning' >Pending ";
		
		if($pending_leave_ctr > 0){
			$pending = "<a href='#' id='a_pending_leave'>leaves: {$pending_leave_ctr}</a>";
		}
		
		if($pending_ot_ctr > 0 ){
			$pending .= (!empty($pending)) ? ' / ' : '';
			
			$pending .= "<a href='#' id='a_pending_ot'>ot: {$pending_ot_ctr}</a>";
		}
		
		$html .= $pending;
		$html .= '</span>';
		
		echo $html;
	}
?>
<fieldset>
    <legend>Main List</legend>

    <div id='selector'></div>

    <table class='tbl-lead-views' id='tbl-lead-views'>
        <?
        if(count($attendance_list) <= 0){
            ?>
            <tr>
                <td colspan=10>
                    <center><span class='warning'>no record found</span>
                </td>
            </tr>
        <?
        }else{
		?>
            <tr>
                <td>&nbsp;</td>
                <td>fullname</td>
                <td>time in/out</td>
                <td>today break</td>
                <td>note</td>
                <td></td>
            </tr>
        <?
        }
        $i=1;
        $ctr=0;
		// print_r($attendance_list);
		$current_date = date('Y-m-d');
		
        foreach($attendance_list as $detail){
			$user_id = $detail['user_id'];
			$time_in = $time_out = '';
			
			
			$on_break = '';
			$running_break  = '';
			if($detail['login']){
					
				if($detail['tagging'] == 'absent' || $detail['tagging'] == 'suspended' ||
					$detail['data_type'] == 'leave' || $detail['data_type'] == 'ot' || $detail['data_type'] == 'ut'){
						
					$time_in = date('F d, Y (l)',strtotime($detail['login']));
					
				}else{
					$time_in = date('F d, Y g:i A ',strtotime($detail['login']));
					
					if($detail['data_type'] == 'attendance' ){
						//show the current break in ICON per user
						if(isset($on_breaks[$user_id]) && $current_date == substr($detail['login'],0,10)){
							
							$on_break .= isset($on_breaks[$user_id]['coffee']) 		? img(array('src'=>'assets/images/coffee.png',	'title'=>'Coffee break'		),FALSE,20) : '';
							$on_break .= isset($on_breaks[$user_id]['bathroom']) 	? img(array('src'=>'assets/images/bathroom.png','title'=>'Bathroom break'	),FALSE,15) : '';
						}
					}
					
					if(($detail['data_type'] == 'attendance' || $search_type == 'coffee'|| $search_type == 'bathroom') && 
						!isset($break_sum_per_student[$user_id]) &&
							$current_date == substr($detail['login'],0,10)){
						
						$break_sum_per_student[$user_id] = true;
						//set the total running break time of the user for the current day
						if(isset($user_break_arr[$user_id])){
							if(isset($user_break_arr[$user_id]['coffee'])){
								list($h,$m) = explode(':',$user_break_arr[$user_id]['coffee']);
								$h = (int)$h;
								$m = (int)$m;
								$running_break .= "C: {$h}h {$m}m" ;
							}
							
							if(isset($user_break_arr[$user_id]['bathroom'])){
								list($h,$m) = explode(':',$user_break_arr[$user_id]['bathroom']);
								$h = (int)$h;
								$m = (int)$m;
								$running_break .= !empty($running_break) ? '<br>' : '';
								$running_break .= "B: {$h}h {$m}m" ;
							}
							
						}
					}
				}
			}
			
			if($detail['logout'] && $detail['tagging'] != 'absent' && $detail['tagging'] != 'suspended'){
				$time_out =  " ~ " . date('g:i A (l)',strtotime($detail['logout']));
			}
			
			if($detail['data_type'] == 'attendance'){
				$tag_val = $tagging[$detail['tagging']];
				$link = "<a href='".base_url()."main/edit/{$detail['id']}'>edit</a>";
			}elseif($detail['data_type'] == 'leave'){
				$tag_val = $leave_sub[$detail['tagging']];
				
				$font_color = ($detail['status'] == 'pending') ? 'red' : '';
				
				$tag_val .= "<span style='color:{$font_color}'>(".$file_status[$detail['status']] .')</span>';
				$link = "<a href='".base_url()."main/e_file/{$detail['id']}/{$detail['data_type']}'>edit</a>";
			}elseif($detail['data_type'] == 'break'){
				$tag_val = $break_type[$detail['tagging']];
				$link = "";
			}elseif($detail['data_type'] == 'ot' || $detail['data_type'] == 'ut'){
				
				if($detail['data_type'] == 'ut'){
					
					$tag_val = 'Undertime';
					if($detail['max_ut_hr']){
						$tag_val .= ' [' .$max_ut_hr_tagging[$detail['max_ut_hr']] .'] ';
					}

				}else{
					$tag_val = $ot_sub[$detail['tagging']];
					
					if($detail['max_ot_hr']){
						$tag_val .= ' [' .$max_ot_hr_tagging[$detail['max_ot_hr']] .'] ';
					}
				}
				
				$font_color = ($detail['status'] == 'pending') ? 'red' : '';
				
				$tag_val .= "<span style='color:{$font_color}'>(".$file_status[$detail['status']] .')</span>';
				$link = "<a href='".base_url()."main/e_file/{$detail['id']}/{$detail['data_type']}'>edit</a>";
			}
			$one_row = "<tr class='tr-list'>
							<td>{$on_break}</td>
							<td>{$detail['firstname']} {$detail['lastname']}</td>
							<td>{$time_in} {$time_out}</td>
							<td>{$running_break}</td>
							<td>".$tag_val."</td>
							<td></td>";
			
			if(hasAccess($privs,201)){
				$one_row .= "<td style='text-align:center'>{$link}</td>";
			}
			
			$one_row .= "</tr>";
			echo $one_row;
			
		}
		?>
	</table>
</fieldset>

<script>
function togglePending(search_type){
	
	$('#frm-search-agent *').filter(':input').each(function(){
		//your code here
		if($(this).attr('type') == 'input'){
			$(this).val(''); //set to empty
		}
		
	});

	$('.input_date').removeClass('required');
	$('#search_type').val(search_type);
	$('#pending_only').prop('checked',true);
	$("#frm-search-agent").submit();
}

$(function(){
	$('#a_pending_leave').click(function(){
		togglePending('leave');
	})
	$('#a_pending_ot').click(function(){
		togglePending('overtime');
	})
	
	
	$(".tr-list").mouseover(function(){$(this).addClass('tr_highlight');})
	$(".tr-list").mouseout(function(){$(this).removeClass('tr_highlight');})
})
</script>