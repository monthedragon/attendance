<?php
Class Reports extends Auth_Controller{
	var $data = array(); 
	
	function __construct(){
		parent::__construct();
		$this->load->model('reports_model');
		$this->load->helper('url');
		$this->load->library('session');    
		$this->data = $this->get_privs();
	}
	
	public function index(){
		$this->set_header_data(PROJECT_NAME.':Reports','Reports');
		
		$this->load->view('reports/index');
		$this->load->view('templates/footer');
	}
	
	/**
	* *************************
	* START OF REPORT`
	* *************************
	**/
	
	function generate_obr_rpt(){
		$pdata = $this->input->get();
        $this->setConfig();
		
        $this->reports_model->getReportData($this->start_datetime,$this->end_datetime);
		
        $this->initExcel();
        $this->writeHeader();
        $this->writeBody();
        $this->writeLegend();
        $this->downloadExcel();
		
	}
	
    function getData(){
        $start_date = date("Y-{$this->target_report_month}-01");
        $end_date = date("Y-{$this->target_report_month}-t 23:59:59");

        $sql = "";
        $result = $this->conn_obj->query($sql)->result_array();
		
        $this->data_row = array();
        foreach($result as $row) {
            $cd = $row['calldate'];
            $cr = $row['final_dispo'];

            if(!isset($this->data_row[$cd][$cr])){
                $this->data_row[$cd][$cr] = 0;
            }
            $this->data_row[$cd][$cr] += 1;
        }
    }

    function setConfig(){

		$pdata = $this->input->get();
		$this->start_date 		= $pdata['start_date'];
		$this->end_date 		= $pdata['end_date'];
		$this->start_datetime 	= $pdata['start_date'] . ' 00:00:00' ;
		$this->end_datetime 	= $pdata['end_date'] . ' 23:59:59';
		
    }


    function initExcel(){
        include_once("include/Excel/Excel_Perf.php");
        $template_path =  'template/hr_report.xlsx';
        $this->excel_xml = new Excel($template_path);
		$this->setFileName();
        $this->excel_xml->setFileName($this->filename.'.xlsx');
    }
	
	//i.e: DEC 24, 2020 TO JAN 08, 2021 (HR) 
	function setFilename(){
		$start = date('M d, Y',strtotime($this->start_date));
		$end = date('M d, Y',strtotime($this->end_date));
		$filename = str_replace(',','',"{$start} TO {$end} (HR)"); //no comma for filename
		$this->filename = strtoupper($filename);
		
		$this->payroll_period_note = strtoupper("{$start} TO {$end}"); //with comma
	}

    function writeBody(){

		$row = 8;
		$ctr = 1;
		$date_style_id = $this->excel_xml->getCellStyle("D8");
		//leave style
		$leave_style_id = $this->excel_xml->getCellStyle("B40");
		//red/late font style/ FtM use by UT
		$red_style_id = $this->excel_xml->getCellStyle("B41");
		//SH style
		$sh_style_id = $this->excel_xml->getCellStyle("B42");
		//RD style
		$rd_style_id = $this->excel_xml->getCellStyle("B43");
		//TOTALIZATION style
		$total_style_id = $this->excel_xml->getCellStyle("B44");
		
		$this->totalization = array();
		
		foreach($this->reports_model->agent_arr as $agent_info){
			$user_id = $agent_info['user_id'];
			
			$att_info = $this->reports_model->data_per_user_day[$user_id];
			
			$fullname = $agent_info['lastname']. ', '. $agent_info['firstname'] . '  ' . $agent_info['middlename'];
			
			$this->excel_xml->setCellValue('A'.$row, $ctr);
			$this->excel_xml->setCellValue('B'.$row, $fullname);
			$this->excel_xml->setCellValue('C'.$row, $agent_info['emp_code']);
			
			$int_col = 4; //represent col D
				
			foreach($this->reports_model->target_days as $login_date){
				
				$value_to_disp = '';
				if(isset($att_info[$login_date])){
					$value_to_disp = $att_info[$login_date];
				}
				
				$col = $this->excel_xml->util->intToColumn($int_col);
				
				if($value_to_disp == ''){
					$day = DATE('N',strtotime($login_date)); //1 = monday, 7 = sunday
					if(isset($this->reports_model->holiday_arr[$login_date])){
						$value_to_disp = $this->reports_model->holiday_arr[$login_date]['holiday_type'];
					}elseif($day == 6 || $day == 7){ //sat or sunday
						$value_to_disp = 'RD'; //REST DAY
					}
				}
				
				$this->prepTotalization($login_date, $user_id, $value_to_disp);
				
				$this->excel_xml->setCellValue($col.$row, $value_to_disp);
				
				$style_id = $date_style_id;
				if(isset($this->reports_model->leave_arr[$login_date][$user_id])){
					$style_id = $leave_style_id;
				}elseif(isset($this->reports_model->late_arr[$login_date][$user_id])){
					$style_id = $red_style_id;
				}elseif(isset($this->reports_model->ut_arr[$login_date][$user_id])){
					$style_id = $red_style_id;
				}
				
				$this->excel_xml->setCellStyle_2($col.$row, $style_id);
				$int_col++;
			}
			
			$this->writeTotalization($user_id, $col, $row, $int_col, $total_style_id);
			
			$this->excel_xml->addRows($row,1);
			
			$ctr++;
		}
		// echoDebug($this->totalization);	
		// exit();
    }
	
	function writeTotalization($user_id, $col, $row, $int_col, $style_id){
		$totalization_arr = array('man_hour','reg_ot_hour','skip_for_unpaid_ot','rd_ot_hour','sh_hour','lh_hour','remarks');
		
		foreach($totalization_arr as $trgt_total){
			$col = $this->excel_xml->util->intToColumn($int_col);
			$value_to_disp = '';	
			
			if($trgt_total == 'skip_for_unpaid_ot' || $trgt_total == 'remarks'){
				//do nothing and set emmpty this is for manual input
				
			}elseif(isset($this->totalization[$user_id][$trgt_total])){
				$value_to_disp = $this->totalization[$user_id][$trgt_total];
			}
			
			$this->excel_xml->setCellValue($col.$row, $value_to_disp);
			$this->excel_xml->setCellStyle_2($col.$row, $style_id);
			$int_col++;
		}
	}
	
	function prepTotalization($login_date, $user_id, $value){
		if(!isset($this->totalization[$user_id]['man_hour'])){
			$this->totalization[$user_id] = array(		
													'man_hour' => 0, //weekday hour wihtout holiday
													'reg_ot_hour' => 0, //regular_ot
													'rd_ot_hour' => 0, //rest day OT
													'sh_hour' => 0, //special holiday 
													'lh_hour' => 0,
			);
		}
		
		if(strpos($value, '/') !== false){
			//meaning the value to be displayed on the report has '/' (slash)
			//If so it can be OT (regular or rd) OR HD
			list($man_hour,$ot_or_hd) = explode('/',$value);
			$this->totalization[$user_id]['man_hour'] += $man_hour;
			
			if(is_numeric($ot_or_hd)){
				//then this is OT 
				
				if(isset($this->reports_model->ot_arr[$login_date][$user_id])){
					
					$ot_type = $this->reports_model->ot_arr[$login_date][$user_id]['tagging'];
					
					if($ot_type == 'regular_ot'){
						$this->totalization[$user_id]['reg_ot_hour'] += $ot_or_hd;
						
					}
				}
				
				
			}else{
				//HD do nothin
			}
			
		}elseif(is_numeric($value)){
			
			//if numeric then it can be normal man hour, RD OT, OR  HOLIDAY (LH/SH)
			if(isset($this->reports_model->ot_arr[$login_date][$user_id])){
				
				//then this can be RD OT,	
				$ot_type = $this->reports_model->ot_arr[$login_date][$user_id]['tagging'];
				if($ot_type == 'restday_ot'){					
					$trgt_total = 'rd_ot_hour';
				}elseif($ot_type == 'special_hol_ot'){
					$trgt_total = 'sh_hour';
				}elseif($ot_type == 'reg_hol_ot'){
					$trgt_total = 'lh_hour';
				}
				
				$this->totalization[$user_id][$trgt_total] += $value;
				
			}else{
				
				$this->totalization[$user_id]['man_hour'] += $value;
				
			}
			
			
		}else{
			//so this case the value is a string
			//it can be leaves (SL, VL, EL)
			
		}
		
	}

    function downloadExcel(){
	   	$this->excel_xml->download();
    }


	/**
	 * method for writing the header
	 */
	function writeHeader(){
		
		$row = 4;
		$no_of_days = (count($this->reports_model->target_days)-1);
		//Add +4 since the beginning of the merge cell is D 
		$int_col 	= $no_of_days + 4;
		//convert the nunmber of days to the LETTER
		$merge_last_col = $this->excel_xml->util->intToColumn($int_col);
		$this->excel_xml->setCellValue('D'.$row,'TELEPRIME SOLUTIONS, INC. DTR SUMMARY');
        $this->excel_xml->mergeCell("D{$row}","{$merge_last_col}{$row}");
		
		$row = 5;
		$this->excel_xml->setCellValue('D'.$row,'PAYROLL PERIOD: '. $this->payroll_period_note);
        $this->excel_xml->mergeCell("D{$row}","{$merge_last_col}{$row}");
		
		$this->border_style_id = $this->excel_xml->getCellStyle("D4");
		
		//TOTAL COLUMNS
		$this->createLastCols($row, $int_col, 1, 'TOTAL MANHOURS');
		$this->createLastCols($row, $int_col, 2, 'REGULAR OT TOTAL HOURS');
		$this->createLastCols($row, $int_col, 3, 'UNPAID REGULAR OT TOTAL HOUR/S');
		$this->createLastCols($row, $int_col, 4, 'TOTAL RD OT');
		$this->createLastCols($row, $int_col, 5, 'TOTAL SH OT');
		$this->createLastCols($row, $int_col, 6, 'TOTAL LH OT');
		$this->createLastCols($row, $int_col, 7, 'REMARKS');
		
		//REMARK/S
		
		$row = 6;
		$col_start = 4; //Letter D
		$date_style_id = $this->excel_xml->getCellStyle("D6");
		
		
		foreach($this->reports_model->target_days as $date){
			
			//format: day-Month (i.e: 1-Feb, 2-Feb)
			$f_date = date('d-M',strtotime($date));
			
			$letter = $this->excel_xml->util->intToColumn($col_start);
			$this->excel_xml->setCellValue($letter.$row,$f_date);
			$this->excel_xml->setCellStyle_2($letter.$row, $date_style_id);
			
			$this->excel_xml->setCellStyle_2($letter.($row-2), $this->border_style_id); //FOr ROW of "TELEPRIME SOLUTION . . . "
			$this->excel_xml->setCellStyle_2($letter.($row-1), $this->border_style_id); //For ROW of "PAYROLL PERIOD . . . "
			$col_start++;
		}
				
        $this->excel_xml->addRows(1,6);
		
	}
	
	//Creates the last columns after the MERGE from "TELEPRIME SOLUTION . . . "
	function createLastCols($row, $int_col, $inc, $text){
		
		$arr = array(
			'cell' => array(
							'background'=>array('color'=>'92D050'),
							'border'=>array('all'=> array("style" => "thin","color" => "black"))
							),
			'text' => array('size' => 8, 'wrap-text'=> true, 
								"align-x" => "center",
								"align-y" => "center",
								"bold" => true,),
		);
		
		$new_style = $this->excel_xml->createNewStyle($arr);

		$trgt_col_int = $int_col+$inc; //set the last COL after merge + 1
		$trgt_col = $this->excel_xml->util->intToColumn($trgt_col_int);
		$trgt_cell = $trgt_col.$row;
		$this->excel_xml->setCellValue($trgt_cell,$text);
        $this->excel_xml->mergeCell($trgt_cell,$trgt_col.($row+1));
		$this->excel_xml->setCellStyle_2($trgt_cell, $new_style);
		
		
		$trgt_cell = $trgt_col.($row+1); //targetting the merged cell adding style as well
		$this->excel_xml->setCellStyle_2($trgt_cell, $new_style);
		
		$trgt_cell = $trgt_col.($row-1); //targetting the upper cell
		$this->excel_xml->setCellStyle_2($trgt_cell, $this->border_style_id);

	}
	

	/**
	 * method for writing the header
	 */
	function writeLegend(){
		
        $this->excel_xml->addRows(9,3);//empty spaces
        $this->excel_xml->addRows(13,15);
	}
	
}
?>
