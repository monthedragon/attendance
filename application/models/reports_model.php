<?php
Class Reports_model extends CI_Model{ 
	public function __construct()
	{
		$this->load->database();
	}
	
	public function getReportData($start_datetime, $end_datetime){
		$query = "SELECT 
					attendance.*,
					users.firstname, 
					users.lastname , 
					users.middlename, 
					users.emp_code 
				  FROM attendance
				  INNER JOIN users ON users.user_name = attendance.user_id
				  WHERE login >= '{$start_datetime}'
				  AND login <= '{$end_datetime}'
				  ORDER BY users.lastname, users.firstname,login";
				  
		$result = $this->db->query($query)->result_array();
		
		$this->setTargetDays($start_datetime, $end_datetime);
		$ret_val = array();
		$this->agent_arr = array();
		$this->data_per_user_day = array();
		$this->late_arr = array();
		
		$this->getOT($start_datetime, $end_datetime);
		$this->getUT($start_datetime, $end_datetime);
		$this->getHolidays($start_datetime, $end_datetime);
		
		foreach($result as $info_arr){
			$login_date = substr($info_arr['login'],0,10);
			
			$user_id = $info_arr['user_id'];
			$this->agent_arr[$user_id] = $info_arr;
			
			//TODO add computation for ATTENDANCE, OT and LEAVE
			
			if($info_arr['logout']){
				if($info_arr['tagging'] == 'absent'){
					$attendance  = 'A';
				}elseif($info_arr['tagging'] == 'suspended'){
					$attendance  = 'SUS';
				}else{
					
					if($info_arr['tagging'] == 'late'){
						$this->late_arr[$login_date][$user_id] = true;
						$datetime1 	= date_create($info_arr['login']);	
						
						$add_lunch_time = 0;
						if($datetime1->format("H") > 12){
							$add_lunch_time = 1;
						}
						
						
						
						//set end_time as 9AM as this is the standard login time for all employee
						//TODO check if need to be dynamic in future
						$end_time 	= date('Y-m-d 09:00:00',strtotime($info_arr['login']));
						$datetime2 	= date_create($end_time);
						$late_interval 	= date_diff($datetime1, $datetime2);
						
						$late_hour  = (int)$late_interval->format("%H");	
						$late_mins  = (int)$late_interval->format("%I");	
						//case: (i.e: 2hrs and 15 mins late then ( 15/60 = 0.25 + 2 hrs = 2.25 deduction from 8 hours = 5.75 will be reported)
						$total_work_hrs = (8 - (($late_hour) + number_format($late_mins /60,2))) + $add_lunch_time;
						
						list($hour, $mins) = explode('.',$total_work_hrs);
						
						//interval from login and logout
						$datetime2 	= date_create($info_arr['logout']);
						$interval 	= date_diff($datetime1, $datetime2);
						
						$attendance = $this->applyOTandUT($login_date, $user_id, $interval, $info_arr, $hour, $mins);
						
					}else{
						
						//IF NOT LATE then matic SET as 9AM
						$start_time = date('Y-m-d 09:00:00',strtotime($info_arr['login']));
						$datetime1 	= date_create($start_time);	
						
						$datetime2 	= date_create($info_arr['logout']);
						
						$interval = date_diff($datetime1, $datetime2);
						$attendance = $this->applyOTandUT($login_date, $user_id, $interval, $info_arr);
						
					}
					

					
				}
			}else{
				$attendance = 'n/a';
			}
			
			$this->data_per_user_day[$user_id][$login_date] = $attendance;
		}
		
		
		$this->getLeaves($start_datetime, $end_datetime);
		
		return $result;
	}
	
	//set the range of date accourding to the selected start and end from the screen
	function setTargetDays($start_datetime, $end_datetime){
		$this->target_days = array();
		$end_date 	= substr($end_datetime,0,10);
		$start_date = substr($start_datetime,0,10);
		
		while($start_date <= $end_date){
			
			$this->target_days[$start_date] = $start_date;
			$start_date = date('Y-m-d',strtotime($start_date. '+1 day'));
		}
	}
	
	function getLeaves($start_datetime, $end_datetime){
		$vl_legend = array(
							'maternity'	=>'ML',
							'emergency'	=>'EL',
							'half_day'	=>'HDP',
							'sick'		=>'SLP',
							'vacation'	=>'VLP',
							'vl_wout_p'	=>'VL',
							'hd_wout_p'	=>'HD',
							'sl_wout_p' => 'SL');
		
		$query = "SELECT 
					leave_file.*,
					users.firstname, 
					users.lastname , 
					users.middlename, 
					users.emp_code 
				  FROM leave_file 
				  INNER JOIN users ON users.user_name = leave_file.user_id
				  WHERE target_date >= '{$start_datetime}'
				  AND target_date <= '{$end_datetime}'
				  AND file_status = 'approved'
				  ORDER BY target_date";
				  
		$result = $this->db->query($query)->result_array();
		$this->leave_arr = array();
		
		foreach($result as $info_arr){
			
			$user_id = $info_arr['user_id'];
			
			if(!isset($this->agent_arr[$user_id])){
				$this->agent_arr[$user_id] = $info_arr;
			}
		
			$target_date = substr($info_arr['target_date'],0,10);
			
			$this->leave_arr[$target_date][$user_id] = $info_arr;
			
		}
		
		foreach($this->target_days as $target_date){
			
			if(isset($this->leave_arr[$target_date])){
				foreach($this->leave_arr[$target_date] as $user_id=>$details){
				
					$tagging = $vl_legend[$details['tagging']];
					if(!isset($this->data_per_user_day[$user_id][$target_date])){
						//if not attendance display the leave type
						$this->data_per_user_day[$user_id][$target_date] = $tagging;
					}else{
						//if with attendancen display it together with the leave type
						
						if($tagging == 'HDP' || $tagging == 'HD' ){
							
							//As of 2021-07-18 once tagged as Half day with pay or without pay set automatiicaly 4 hours for working hours
							$cur_value = $this->data_per_user_day[$user_id][$target_date] ;
							if(strpos($cur_value, '/') !== false){
								//IF found then we need to get both values meaning value has already OT on it
								//i.e 8/1 meaning 8 working hour with 1hr OT
								list($man_hour,$ot) = explode('/',$cur_value);
								$val_to_set = "4/{$ot}";	
							}else{
								$val_to_set = '4';	
							}
							
							$this->data_per_user_day[$user_id][$target_date] = $val_to_set."/{$tagging}";
							
						}else{
							$this->data_per_user_day[$user_id][$target_date] .= '/'.$tagging;	
						}
						
					}	
				}
			}
			
		}
	}
	
	function getOT($start_datetime, $end_datetime){
		
		$query = "SELECT *
				  FROM overtime 
				  WHERE target_date >= '{$start_datetime}'
				  AND target_date <= '{$end_datetime}'
				  AND file_status = 'approved'
				  ORDER BY target_date";
				  
		$result = $this->db->query($query)->result_array();
		$this->ot_arr = array();
		
		foreach($result as $info_arr){
			$target_date = substr($info_arr['target_date'],0,10);
			
			$user_id = $info_arr['user_id'];
			$this->ot_arr[$target_date][$user_id] = $info_arr;
			
		}
	}
	
	//Get UNDERTIME records
	function getUT($start_datetime, $end_datetime){
		
		$query = "SELECT *
				  FROM undertime 
				  WHERE target_date >= '{$start_datetime}'
				  AND target_date <= '{$end_datetime}'
				  AND file_status = 'approved'
				  ORDER BY target_date";
				  
		$result = $this->db->query($query)->result_array();
		$this->ut_arr = array();
		
		foreach($result as $info_arr){
			$target_date = substr($info_arr['target_date'],0,10);
			
			$user_id = $info_arr['user_id'];
			$this->ut_arr[$target_date][$user_id] = $info_arr;
			
		}
	}
	
	function getHolidays($start_datetime, $end_datetime){
		
		$query = "SELECT *
				  FROM holidays 
				  WHERE target_date >= '{$start_datetime}'
				  AND target_date <= '{$end_datetime}'
				  AND is_active = 1
				  ORDER BY target_date";
				  
		$result = $this->db->query($query)->result_array();
		$this->holiday_arr = array();
		
		foreach($result as $info_arr){
			$target_date = substr($info_arr['target_date'],0,10);
			
			$this->holiday_arr[$target_date] = $info_arr;
			
		}
		
		// echo '<pre>';
		// var_dump($this->holiday_arr);
		// exit();
		
	}
	
	
	function applyOTandUT($target_date, $user_id, $interval_obj, $info_arr, $hour = '', $mins = ''){
		$ot_hrs = '';
		
		if($hour === '')
			$hour  = (int)$interval_obj->format("%H");	
		
		if($mins === '')
			$mins  = (int)$interval_obj->format("%I");	
		
		if($hour >= 8){
			//as requested by Sir Vince dont display more than 8 hrs from the payroll report
			//if more than 8 hours them set only 8
			$hour = 8;
			$mins = '';
		}
		
		$working_hr = $hour.(($mins) ? '.'.$mins : '');
		
		//defualt value to be reported
		//will be altered later once OT and UT is available
		$attendance_hour = $working_hr;
		
		if(isset($this->ot_arr[$target_date][$user_id])){
			//HAS OT append the OT details
			
			$ot_info = $this->ot_arr[$target_date][$user_id];
			
			
			if($ot_info['tagging'] == 'regular_ot'){
				
				//as we discussed OT HOURS will be based on the approved max_ot_hr from the OT FORM
				//regardless of login and logout of the user
				$ot_hrs = $ot_info['max_ot_hr'];
				$attendance_hour = $working_hr.'/'.$ot_hrs;
				
			}else{
				
				//OT with max_ot_hr will be the time to be reported
				if($ot_info['max_ot_hr'] != 'na' && $ot_info['max_ot_hr'] != ''){
					$attendance_hour = $ot_info['max_ot_hr'];
				}else{
					
					//which means every minute counts
					$attendance_hour = $working_hr;
						
					if($ot_info['tagging'] == 'restday_ot'){
						//BUT if OT is RD (weekend)
						//as disussed @2021-03-06 when calculating OT, minutes should be removed AFTER 2PM
						//no matter it is before or after the ALLOWED ot hour
						//During Saturday remove/truncate the minutes of log out (i.e 2:05 reported will be 2:00, 2:35 reported will be 2)	

						//As of 2021-07-18 once RD OT applied, truncate all the MINUTES
						//So the idea from 2021-03-06 about 2pm onwards will be disregards
						
						//as of 2022-03-05 as discussed the logint time will be the actual time he logs in
						//So the idea from 2021-07-18 about truncating the MINUTES will only be possible if 
						//the user logged out in between 2:00 ~ 2:29PM
						$datetime1 	= date_create($info_arr['login']);
						
						//as of 2022-03-05 if the logout time is 2:30 and above every minute will be counted
						$logout_time = $info_arr['logout'];
						
						$logout_hm = date('H:i',strtotime($info_arr['logout']));
						if($logout_hm >= '14:00' && $logout_hm < '14:30'){
							//in relation to the update from 2022-03-05
							//if logout time is within 2:00 ~ 2:29 then minutes will be disregarded
							//i.e: out = 2:25 then logout will be 2pm ONLY, 25 mins will be disregarded
							$logout_time = date('Y-m-d H:00:00',strtotime($logout_time));
						}
						
						$datetime2 	= date_create($logout_time);
						$interval 	= date_diff($datetime1, $datetime2);
						
						$hrs = (int)$interval->format("%H");
						$mins  = (int)$interval->format("%I");	
						
						//actual_in_out is for testing
						$actual_in_out = $info_arr['login'] . '~' . $logout_time;
						
						$attendance_hour =  $hrs. (($mins) ? '.'.$mins : '');
						
					}
				}
				
			}
		}
		
		if(isset($this->ut_arr[$target_date][$user_id])){
			//HAS UT then do the same logic as OT set the max_ut_hr as the log time
			
			$ut_info = $this->ut_arr[$target_date][$user_id];
			
			$ut_hrs = $ut_info['max_ut_hr'];
			$attendance_hour = $ut_hrs . '/UT';
			
		}
		
		return $attendance_hour;
		
	}
	
}
?>