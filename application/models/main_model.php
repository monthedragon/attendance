<?php
class Main_model extends CI_Model {
	var $retVal = array();
	
	//These are the fields to be used to validate if the HEALTH DEC is ALLOW to LOG or NOT
	//Answerable by YES OR NO
	public $target_allow_fields = array(
										'c_sore_throat',
										'c_body_pain',
										'c_headache',
										'c_fever',
										'c_worked_stayed_positive',
										'c_has_contact',
										'c_travel_ph');

	//variables being used on main LIST
	//These variables will be used to save on SESSION for easy usage of the screen
	public 	$sw_target_fields = array('firstname','lastname','from_date','to_date','search_type','pending_only','file_reason');

    //Handles the half_day leave for displaying remarks
    public $hdForRemarks = [];
	
	public function __construct()
	{
		$this->load->database();
	}
	
	public function change_conn($cd){
		$config['hostname'] = $cd['hostname'];
		$config['username'] = $cd['username'];
		$config['password'] = $cd['password'];
		$config['database'] = $cd['campaign_db'];
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;

		return $this->load->database($config,true);
	}
	
	function doLogin(){
		$user_name = $this->session->userdata('username');
		//Everytime the user log-in, the target_login that being set on users.target_login 
		//will be saved as well to attendance.target_login
		//
		$target_login = $this->session->userdata('target_login');
		
		if(!$this->checkActionDone('login')){
			
			$tagging = 'present'; //default
			
			if(!$this->skipLateInWeekends()){
				if($target_login){ //if has assigned target_login
					if($target_login < date('H:i:s')){ 
						$tagging = 'late';
					}
				}
			}
			
			
			$insert_arr = array('user_id'=>$user_name,
								'login'=>date('Y-m-d H:i:s'),
								'date_entered'=>date('Y-m-d H:i:s'),
								'target_login'=>$target_login,
								'tagging'=>$tagging,
								'modified_by'=>$user_name,
								'modified_date'=>date('Y-m-d H:i:s'));
								
			$this->db->insert('attendance',$insert_arr);
		}
		
	}

	/**
	 * When tagging as late, don't include weekends
	 * 2024-06-09 (mon)
	 */
	function skipLateInWeekends(){
		$curDay = date('w');
		$weekendsArr = array(0,6); 
		return in_array($curDay, $weekendsArr);
	}
	
	function doLogout(){
		$user_name = $this->session->userdata('username');
		
		$attendance_id = $this->checkActionDone('logout');
		if($attendance_id !== true){ //$attendance_id shoudl have a value from attendance table
			
			$insert_arr = array('logout'=>date('Y-m-d H:i:s'),
								'modified_by'=>$user_name,
								'modified_date'=>date('Y-m-d H:i:s'));
								
			$where_arr = array('id'=>$attendance_id);
			$this->db->update('attendance',$insert_arr, $where_arr);
		}
		
	}
	
	function getTodayLog(){
		$user_name = $this->session->userdata('username');
		
		$row = $this->db->select('*')
					 ->from('attendance')
					 ->where('DATE(date_entered)', date('Y-m-d'))
					 ->where('user_id',$user_name)
					 ->get()
					->result_array();
		if($row){
			return $row[0];
		}
		return false; //none
	}
	
	function checkActionDone($action){
		$row = $this->getTodayLog();
					
		// echo $this->db->last_query();
		
		if($action == 'login'){
			
			if($row){ //already have then do nothing
				return true;
			}else{ //if nothing foudn then do login
				return false;
			}
			
		}elseif($action == 'logout'){
			//logout
			if($row){ //during LOGOUT data should be existing
				
				if($row['logout'] == ''){
				
					//if the field attendance.logout has no value yet then do LOGOUT process
					//return the ID of the record to be updated
					return $row['id'];
					
				}else{
					
					//But if has value then do nothing
					return true;
					
				}
				
			}else{ //if not existing then do nothing LOGIN should be done first
				return true;
			}
			
		}
		
		return true; //set true so to be safe, returning this will tell that system done on login.logout process
		
	}
	
	/**
	 * Set where date conditions
	 */
	public function getDateConditions(){
		
		$attendance_date_where=$trgt_date_where=$break_where = '';

		$pdata 		= $this->input->post();

		$from_date 	= $pdata['from_date'];
		$to_date 	= $pdata['to_date'];

		if($from_date){
			$attendance_date_where .= " AND login >= '{$from_date} 00:00:00' ";		
									   
			$trgt_date_where .= " AND target_date >= '{$from_date} 00:00:00'";	
									
			$break_where .= " AND break_in >= '{$from_date} 00:00:00' ";	
		}

		if($to_date){
		
			$attendance_date_where .= " AND login <= '{$to_date} 23:59:59'";		
									   
			$trgt_date_where .= " AND target_date <= '{$to_date} 23:59:59'";	
									
			$break_where .= " AND break_in <= '{$to_date} 23:59:59' ";		
		}

		return array($attendance_date_where, $trgt_date_where, $break_where);
	}

	/**
	* get agent attendance
	*/
	function get_agent_attendance(){
		
		$user_type = $this->session->userdata('user_type');
		
		$target_agent_id = '';
		if($user_type != ADMIN_CODE && $user_type != TL_CODE){
			$target_agent_id = $this->session->userdata('username');
		}
		
		$limitation_query = '';
		//Display only AGENT and TL data
		if($user_type == TL_CODE){
			$limitation_query = " AND users.user_type IN ('agent','tl') ";
		}
		
		$pdata 		= $this->input->post();
		// var_dump($pdata);
		
		$f_name 	= trim($pdata['firstname']);
		$l_name 	= trim($pdata['lastname']);
		$f_reason 	= trim($pdata['file_reason']);
		
		if($pdata['from_date'] != '' && $pdata['to_date']){
			$from_date 	= $pdata['from_date'];
			$to_date 	= $pdata['to_date'];
		
			$attendance_date_where = " AND login >= '{$from_date} 00:00:00' 
									   AND login <= '{$to_date} 23:59:59'";		
									   
			$trgt_date_where 	= " AND target_date >= '{$from_date} 00:00:00' 
									AND target_date <= '{$to_date} 23:59:59'";	
									
			$break_where 	= " AND break_in >= '{$from_date} 00:00:00' 
								AND break_in <= '{$to_date} 23:59:59' 
								AND breaks.break_type = '{$pdata['search_type']}'";		
		}

		list($attendance_date_where, $trgt_date_where, $break_where) = $this->getDateConditions();

		if($pdata['search_type']){
			$break_where .= " AND breaks.break_type = '{$pdata['search_type']}'";		
		}

		$where_query = '';		
		if($target_agent_id){
			//this is only applicable to non-admin user like AGENTS,
			//so that the only data to be displayed will be only from the
			$where_query .= " AND user_id = '{$target_agent_id}' ";
		}
		
		if($f_name){
			$where_query .= " AND firstname like '%{$f_name}%' ";
		}
		
		if($l_name){
			$where_query .= " AND lastname like '%{$l_name}%' ";
		}
		
		$non_pending_tbl=$leave_status=$ot_status= '';
		if(isset($pdata['pending_only'])){
			
			if($pdata['search_type'] == 'leave' || $pdata['search_type'] == 'overtime' ){
				//to prevent displaying non leave and ot from the least once the pending_only is clicked
				$non_pending_tbl= " AND 1 = 2 ";
			}
			
			$file_status_where = " AND file_status = 'pending' ";
		}
		
		$file_reason_query = '';
		if($f_reason && $pdata['search_type'] == 'leave'){
			$file_reason_query = " AND file_reason LIKE '%{$f_reason}%'";
		}
		
		$attendance_query = "SELECT 
									attendance.id,
									'attendance' AS data_type,
									user_id, 
									login, 
									logout, 
									tagging ,
									firstname,
									lastname,
									'' as status,
							'' AS max_ot_hr
								FROM attendance
								INNER JOIN users ON users.user_name = attendance.user_id
								WHERE 1 = 1 {$where_query} 
								{$attendance_date_where}
								{$non_pending_tbl}
								{$limitation_query}";
									
		$leave_query = "SELECT 
							leave_file.id,
							'leave' as data_type,
							user_id, 
							target_date AS login, 
							NULL AS logout, 
							tagging,
							firstname,
							lastname,
							file_status as status,
							'' AS max_ot_hr
						FROM leave_file
						INNER JOIN users ON users.user_name = leave_file.user_id
						WHERE 1 = 1 {$where_query}
						{$trgt_date_where}
						{$file_status_where}
						{$limitation_query}
						{$file_reason_query}";
									
		$ot_query = "SELECT 
							overtime.id,
							'ot' as data_type,
							user_id, 
							target_date AS login, 
							NULL AS logout, 
							tagging,
							firstname,
							lastname,
							file_status as status,
							max_ot_hr
						FROM overtime
						INNER JOIN users ON users.user_name = overtime.user_id
						WHERE 1 = 1 {$where_query}
						{$trgt_date_where}
						{$file_status_where}
						{$limitation_query}";
									
		$ut_query = "SELECT 
							undertime.id,
							'ut' as data_type,
							user_id, 
							target_date AS login, 
							NULL AS logout, 
							tagging,
							firstname,
							lastname,
							file_status as status,
							max_ut_hr
						FROM undertime
						INNER JOIN users ON users.user_name = undertime.user_id
						WHERE 1 = 1 {$where_query}
						{$trgt_date_where}
						{$file_status_where}
						{$limitation_query}";
		
		$break_query = "
						SELECT 
							breaks.id,
							'break' AS data_type,
							user_id, 
							break_in AS login,
							break_out AS logout, 
							breaks.break_type AS tagging,
							firstname,
							lastname,
							'' as status,
							'' AS max_ot_hr
						FROM breaks
						INNER JOIN users ON users.user_name = breaks.user_id
						WHERE 1 = 1 {$where_query}
						{$break_where}
						{$non_pending_tbl}
						{$limitation_query}";
		
		
		
		if(!isset($pdata['search_type']) || $pdata['search_type'] == '' || $pdata['search_type'] == 'all'){
			$query = "SELECT * FROM (
							{$attendance_query}
							UNION
							{$leave_query}
							UNION
							{$ot_query}
							UNION
							{$ut_query}
							UNION
							{$break_query}
					) AS new_tbl ORDER BY login;";

            $this->setHalfDayForRemarks($leave_query);
		}elseif($pdata['search_type'] == 'attendance'){
			$query = $attendance_query;
            $this->setHalfDayForRemarks($leave_query);
		}elseif($pdata['search_type'] == 'leave'){
			$query = $leave_query;
		}elseif($pdata['search_type'] == 'overtime'){
			$query = $ot_query;
		}elseif($pdata['search_type'] == 'undertime'){
			$query = $ut_query;
		}elseif($pdata['search_type'] == 'bathroom' || $pdata['search_type'] == 'coffee' ){
			$query = $break_query;
			$query .= " ORDER BY users.lastname,break_in ";
		}

		$data = $this->db->query($query)->result_array();

		$user_type = $this->session->userdata('user_type');
		$this->getTodayBreakTime();
		if($user_type == ADMIN_CODE){
			// echo $query;
			// exit();
		}
		// echo $this->db->last_query();

		return $data;
				
	}

    /**
     * 2025-10-04
     * Get half-day leaves; will be used later for adding remarks
     * This method is only called when 'attendance' is present in the search
     * @param $leave_query
     */
    public function setHalfDayForRemarks($leave_query){
        $hdWhereCond = " AND leave_file.tagging = 'half_day'
                        AND leave_file.file_status = 'approved' ";
        $leave_query .= $hdWhereCond;
        $dataArr = $this->db->query($leave_query)->result_array();

        foreach($dataArr as $info){
            $date = substr($info['login'],0,10);
            $this->hdForRemarks[$info['user_id']][$date] = true;
        }

    }
	
	function getPendingFilesCTR($file_type){
		$user_type = $this->session->userdata('user_type');
		
		$target_agent_id = '';
		if($user_type != ADMIN_CODE && $user_type != TL_CODE){
			$target_agent_id = $this->session->userdata('username');
		}
		
		if($file_type == 'leave'){
			$table = 'leave_file';
		}elseif($file_type == 'ot'){
			$table = 'overtime';
		}
		
		$this->db->select('count(*) as CTR')
				->from($table)
				->join('users'," users.user_name = {$table}.user_id")
				->where('file_status','pending');
		
		if($user_type == TL_CODE){
			$this->db->where_in('user_type',array('agent','tl'));
		}
				
		//Display only AGENT and TL data
		if($target_agent_id){
			$this->db->where('user_id',$target_agent_id);
		}
		
		$row = $this->db->get()->result_array();
		
		// echo $this->db->last_query();
		return  $row[0]['CTR'];
	}
	
	function getPendingLeaves(){
		return $this->getPendingFilesCTR('leave');
		
	}
	
	function getPendingOT(){
		return $this->getPendingFilesCTR('ot');
	}
	
	function getTodayBreakTime(){
		$query = "SELECT * FROM breaks
				  WHERE DATE(date_entered) = DATE(NOW())
			      ORDER BY user_id,date_entered,break_type";
				  
		$data = $this->db->query($query)->result_array();
		
		$this->user_break_arr 	= array(); //hold the total BREAK for the current day per USER and BREAK TYPE
		$this->on_break 		= array(); //hold the currently on BREAK users
		
		foreach($data as $details){

			$user_id   	= $details['user_id'];
			$break_type	= $details['break_type'];
			
			if($details['break_out']){
				
				$break_out 	=  $details['break_out'];	
			}else{ //If no breakout value it means the user still on BREAK
				$break_out = date('Y-m-d H:i:s');
				$this->on_break[$user_id][$break_type] = true; //set TRUE so that it will be displayed on the list
			}
			$datetime1 	= date_create($details['break_in']);
			$datetime2 	= date_create($break_out);

			$interval = date_diff($datetime1, $datetime2);
			$current_break  = $interval->format("%H:%I:%S");			
			
			// echo $current_break .'<br>';
			
			if(!isset($this->user_break_arr[$user_id][$break_type])){
				$this->user_break_arr[$user_id][$break_type] = $current_break;
			}else{
				
				//THIS is  important in calculting the TIME 
				$current_break_str = strtotime($current_break) -strtotime("00:00:00");
				
				
				// echo "> {$break_type} :: {$this->user_break_arr[$user_id][$break_type]} + {$current_break}< = ";
				$this->user_break_arr[$user_id][$break_type] = date("H:i:s",(strtotime($this->user_break_arr[$user_id][$break_type]) + $current_break_str));
				// echo $this->user_break_arr[$details['user_id']][$break_type] . '<br>';
			}
		}
		
		// echo '<pre>';
		// print_r($this->user_break_arr);
		// print_r($this->break_status);
		// echo '</pre>';
				  
	}
	
	function getAttendanceInfo($record){
		$ret_val = $this->db->select('*')
						->from('attendance')
						->join('users',' users.user_name = attendance.user_id')
						->where('attendance.id', $record)
						->get()->result_array();
						
		return $ret_val[0];
		
	}
	
	function save($record){
		$user_name = $this->session->userdata('username');
		$pdata = $this->input->post();
		
		
		//Convert to Y-m-d H:i:s (24hr Format)
		$login 	= date('Y-m-d H:i:s', strtotime($pdata['log_date'] . ' ' . $pdata['login_hr'].':'.$pdata['login_minute'] . ' ' . $pdata['login_meridiem']));
		
		if(empty($pdata['logout_hr']) || empty($pdata['logout_minute']) || empty($pdata['logout_meridiem']) ){
			$logout = null; //set to NULL to show LOG OUT BUTTON on AGENT side
		}else{
			//Convert to Y-m-d H:i:s (24hr Format)
			$logout = date('Y-m-d H:i:s', strtotime($pdata['log_date'] . ' ' . $pdata['logout_hr'].':'.$pdata['logout_minute'] . ' ' .$pdata['logout_meridiem']));
		}
		
		$tagging = $pdata['tagging'];
		$absent_sub_tagging = isset($pdata['absent_sub_tagging']) ? $pdata['absent_sub_tagging'] : '';
		
		$data_update = array('login'=>$login,
							'logout'=>$logout,
							'tagging'=>$tagging,
							'modified_by'=>$user_name,
							'modified_date'=>date('Y-m-d H:i:s'),
							'sub_tagging' => $absent_sub_tagging,);
		
		$where = array('id'=>$record);
		$this->db->update('attendance',$data_update, $where);
	}
	
	
	public function save_file_form(){
		$pdata = $this->input->post();
		
		$user_login	= $this->session->userdata('username');
		$start_date = $pdata['from_date'];
		$to_date 	= $pdata['to_date'];
		$user_name	= $pdata['user_id'];
		
		//these are the type of leaves need the use to have remaining leaves 
		$leaves_need_credit = array('vacation','half_day','sick');
		$sub_tagging = $pdata['leave_sub_tagging'];
		
		try{
			DB_START_TRANSACTION($this->db);
			
			while($start_date <= $to_date){
				
				if($pdata['tagging'] == 'ot'){
					
					$this->processOT($start_date, $user_name, $pdata['ot_sub_tagging'],$pdata['reasons']);
					
				}elseif($pdata['tagging'] == 'ut'){
					
					$this->processUT($start_date, $user_name, 'undertime',$pdata['reasons']);
					
				}elseif($pdata['tagging'] == 'leave'){
					
					$remaining_leaves = $this->getRemainingLeaves($user_name);
					
					if(in_array($sub_tagging,$leaves_need_credit)){
						
						$err = true;
						
						if($sub_tagging == 'half_day' && 
							$remaining_leaves >= 0.5){
							$err = false;

						}else if($remaining_leaves >= 1){
							$err = false;
						}
						
						if($err){
							throw new Exception('Please check the remaining leaves if still enough');
						}
					}
					
					$this->processLeave($start_date, $user_name, $pdata['leave_sub_tagging'],$pdata['reasons']);
					
				}else{
					if(!$this->checkIfAttendanceExists($start_date, $user_name)){
						
				
						$insert_arr = array('user_id'		=>$user_name,
											'login'			=>$start_date,
											'logout'		=>$start_date,
											'date_entered'	=>date('Y-m-d H:i:s'),
											'tagging'		=>$pdata['tagging'],
											'sub_tagging'	=>$pdata['absent_sub_tagging'],
											'modified_by'	=>$user_login,
											'modified_date'	=>date('Y-m-d H:i:s'),
											'file_reason'	=>$pdata['reasons'],);
											
						$this->db->insert('attendance',$insert_arr);
					}else{
						throw new Exception('Attendance conflict: '. $start_date);
					}
				}

				$start_date = date('Y-m-d',strtotime($start_date . ' +1 day '));
			}
			
			DB_COMMIT_TRANSACTION($this->db);
			
			return true; //successfully saved
        }catch(Exception $e){
			
            DB_ROLLBACK_TRANSACTION($this->db);
			return $e->getMessage();
			
        }
		
	}
	
	function processOT($start_date, $user_name, $tagging, $reason){
		$this->processFileForm('ot',$start_date, $user_name, $tagging, $reason);
	}
	
	function processUT($start_date, $user_name, $tagging, $reason){
		$this->processFileForm('ut',$start_date, $user_name, $tagging, $reason);
	}
	
	function processLeave($start_date, $user_name, $tagging, $reason){
		$this->processFileForm('leave',$start_date, $user_name, $tagging, $reason);
	}
	
	function processFileForm($file_type, $start_date, $user_name, $tagging, $reason){
		$pdata = $this->input->post();
		
		if($file_type == 'ot'){
			$table = 'overtime';
			$err_msg = 'OVERTIME';
		}elseif($file_type == 'leave'){
			$table = 'leave_file';
			$err_msg = 'LEAVE';
		}elseif($file_type == 'ut'){
			$table = 'undertime';
			$err_msg = 'UNDERTIME';
		}
		
		if(!$this->checkIfDateExists($start_date, $user_name, $table)){

			$file_status = ($pdata['file_status']) ? $pdata['file_status'] : 'pending';
			
			$insert_arr = array('user_id'		=>$user_name,
								'target_date'	=>$start_date,
								'date_entered'	=>date('Y-m-d H:i:s'),
								'tagging'		=>$tagging,
								'modified_by'	=>$this->session->userdata('username'),
								'modified_date'	=>date('Y-m-d H:i:s'),
								'file_reason'	=>$reason,
								'file_status'	=>$file_status,);
			
			if($file_type == 'ot'){
				$insert_arr['max_ot_hr'] = $pdata['max_ot_hr'];
			}
			
			if($file_type == 'ut'){
				$insert_arr['max_ut_hr'] = $pdata['max_ut_hr'];
			}
								
			$this->db->insert($table,$insert_arr);
		}else{
			throw new Exception("{$err_msg} conflict: ". $start_date);
		}
	}
	
	function checkIfDateExists($target_date, $user_name, $table){
		$result = $this->db->select('id')
					->from($table)
					->where("DATE(target_date)", $target_date)
					->where('user_id',$user_name)
					->get()->result_array();
		if($result){
			// echo $this->db->last_query();
			return true;
		}
		
		return false;
		
	}
	
	//Check if the date to file a leave or etc. .  exists from the attendance of the agetn
	//if Found then dont proceed
	function checkIfAttendanceExists($target_date, $user_name){
		$result = $this->db->select('id')
					->from('attendance')
					->where("DATE(login)", $target_date)
					->where('user_id',$user_name)
					->get()->result_array();
		if($result){
			return true;
		}
		
		return false;
	}
	
	//break_type = bathroom and coffee
	//$action = in or out
	function doBreak($break_type, $action){
		$user_name = $this->session->userdata('username');
		$status = $this->checkBreakStatus($break_type, $action);
			
			
		if($status ===  true){
			if($action == 'in'){
				
				$data_arr = array('user_id'=>$user_name,
									'break_type'=>$break_type,
									'break_in'=>date('Y-m-d H:i:s'),
									'date_entered'=>date('Y-m-d H:i:s'),
									);
									
				$this->db->insert('breaks',$data_arr);
			}else{
				
				$data_arr = array('user_id'=>$user_name,
									'break_type'=>$break_type,
									'break_out'=>date('Y-m-d H:i:s'),
									);
									
				$where = array('id'=>$this->break_id);
				$this->db->update('breaks',$data_arr,$where);
			}
			
			return true;
		}else{
			return $status;
		}
		
	}
	
	//get the latest break base on the $break_type
	function getBreakStatus($break_type){
		
		$user_name = $this->session->userdata('username');
		$result = $this->db->select('*')
						->from('breaks')
						->where('user_id',$user_name)
						->where('break_type',$break_type)
						->where('date_entered >=',date('Y-m-d 00:00:00'))
						->where('date_entered <=',date('Y-m-d 23:59:59'))
						->order_by('date_entered DESC')
						->limit(1)
						->get()
						->result_array();
		if($result){
			return $result[0];
		}else{
			return false;
		}				
	}
	
	//Check the latest break base on the '$break_type' of the user
	function checkBreakStatus($break_type, $action){
		$this->break_id = '';
		$result = $this->getBreakStatus($break_type);
						
		if($result){
			
			if($action == 'in' && empty($result['break_out'])){
				//IF action is IN and the last break_out is NOT EMPTY then not allowed
				//meaning the user is currently on break in
				return 'Currently break IN';
			}elseif($action == 'out' && !empty($result['break_out'])){
				//If break out but the last data's break_out is not empty 
				//meaning the user is already break out, 
				//do break in first
				return 'Break in first';
			}else{
				if($action == 'in'){
					return true; //do break in
				}else{
					$this->break_id = $result['id'];
					return true; //do break out
				}
			}
			
		}else{
			if($action == 'in'){
				//If no data within the day then BREAK IN will be done
				return true; //do IN
			}else{
				//if no data within the day and OUT is being pressed then show error message
				return 'Break in first';
			}
		}
		
	}
	
	//get leave info
	function getLeave($id){
		$data = $this->db->select('*')->from('leave_file')->where('id',$id)->get()->result_array();
		
		return $data[0];
	}
	
	//get OT information
	function getOT($id){
		$data = $this->db->select('*')->from('overtime')->where('id',$id)->get()->result_array();
		
		return $data[0];
	}
	
	//get UT information
	function getUT($id){
		$data = $this->db->select('*')->from('undertime')->where('id',$id)->get()->result_array();
		
		return $data[0];
	}
	
	//Save single leave or ot file
	function saveSingleFile($id){
		$pdata = $this->input->post();
		$file_type = $pdata['tagging'];
		
		if($file_type == 'ot'){
			$table = 'overtime';
			$err_msg = 'OVERTIME';
			$status_field = 'ot_status';
			$tagging = $pdata['ot_sub_tagging'];
		}elseif($file_type == 'ut'){
			$table = 'undertime';
			$err_msg = 'UNDERTIME';
			$status_field = 'ot_status';
			$tagging = 'undertime';
		}elseif($file_type == 'leave'){
			$table = 'leave_file';
			$err_msg = 'LEAVE';
			$tagging = $pdata['leave_sub_tagging'];
		}else{
			return ; //do nothing
		}
		
		$file_status = ($pdata['file_status']) ? $pdata['file_status'] : 'pending';
		
		
		$update_arr  = array('file_status'	=>$file_status,
							'modified_by'	=>$this->session->userdata('username'),
							'modified_date'	=>date('Y-m-d H:i:s'),
							'file_reason'	=>$pdata['reasons'],
							'tagging'		=>$tagging,);
							
		if($file_type == 'ot'){
			$update_arr['max_ot_hr'] = $pdata['max_ot_hr'];
		}
		if($file_type == 'ut'){
			$update_arr['max_ut_hr'] = $pdata['max_ut_hr'];
		}
							
		$where_arr = array('id'=>$id);
		$this->db->update($table,$update_arr, $where_arr);
		
		return true;
		
	}
	
	//ONCE HOME link is click with param of action=r 
	//Reset the session so that it will be back to original STATE
	function resetSWSession(){
		
		$target_fields = $this->sw_target_fields;
		$pdata = $this->input->post();
		
		foreach($target_fields as $field){
			$value = '';
			
			if($field == 'search_type') $value = 'attendance'; //TO limit the displayed
			
			$f_field =  'p_'.$field; //this is to avoid overriding some important session field
			$this->session->set_userdata($f_field, $value);
		}
	}
	
	//Store the searched param from main SW FORM to SESSION
	//This is to make the transition and flow of the system much smoother
	function setSeachPdataSession(){
		
		$target_fields = $this->sw_target_fields;
		$pdata = $this->input->post();
		
		foreach($target_fields as $field){
			
			$value = '';
			if(isset($pdata[$field])){
				$value = $pdata[$field];
			}
			
			$f_field =  'p_'.$field; //this is to avoid overriding some important session field
			// echo $f_field . ' ' .$value .'<br>';
			$this->session->set_userdata($f_field, $value);
		}
	}
	
	//Get the SESSION value of the main SW form
	function getSearchPdataSession($field){
		
		$field = 'p_'.$field;
		return $this->session->userdata($field);
	}
	
	//SET the INITIAL VALUE of the main SW form from the SESSION stored via setSeachPdataSession
	function getSearchInitValue(&$data){
		$target_fields = $this->sw_target_fields;
		
		foreach($target_fields as $field){
			$value = $this->getSearchPdataSession($field);
			
			if($value == ''){
				if($field == 'from_date' || $field == 'to_date' ){
					$value = date('Y-m-d'); //set to current date
				}
			}else{
				if($field == 'pending_only'){
					$value = 'checked=checked';
				}
			}
			
			$data[$field] = $value;
			
		}
		
	}
	
	function getUserInfo($user_id){
		
		$result = $this->db->select('*')
					->from('users')
					->where('user_name',$user_id)
					->get()
					->result_array();
					
		return $result[0];
				
		
	}
	
	function getSummaryInfo($user_id, $trgt_year){
		
		$this->summary_key = array();
		$this->summary_info = array();
		$this->getSummaryleaves($user_id, $trgt_year);
		$this->getSummaryOT($user_id, $trgt_year);
		$this->getSummaryAttendance($user_id, $trgt_year);
		
		return $leaves;
		
	}
	
	function getSummaryleaves($user_id, $trgt_year){
		$query = "	SELECT 
						'leave' AS summary_type,
						DATE_FORMAT(target_date,'%c') AS target_month,
						user_id,
						tagging,
						file_status,
						COUNT(*) AS CTR 
					FROM leave_file
					WHERE file_status IN ('approved')
					AND user_id = '{$user_id}'
					AND DATE_FORMAT(target_date, '%Y') = '{$trgt_year}'
					GROUP BY user_id,tagging,file_status, target_month";
					
		$result = $this->db->query($query)->result_array();
		// $leave_lu = $this->getLookup('leave_sub',1);
		
		foreach($result as $data){
			
			extract($data);
			$this->summary_key[$tagging] = $tagging;
			$this->summary_info[$tagging][$target_month] = $data;
		}
		
		return $result;
		
	}
	
	function getSummaryOT($user_id, $trgt_year){
		$query = "	SELECT 
						'overtime' AS summary_type,
						DATE_FORMAT(target_date,'%c') AS target_month,
						user_id,
						tagging,
						file_status,
						COUNT(*) AS CTR 
					FROM overtime
					WHERE file_status IN ('approved')
					AND user_id = '{$user_id}'
					AND DATE_FORMAT(target_date, '%Y') = '{$trgt_year}'
					GROUP BY user_id,tagging,file_status, target_month";
					
		$result = $this->db->query($query)->result_array();
		// $leave_lu = $this->getLookup('leave_sub',1);
		
		foreach($result as $data){
			
			extract($data);
			$this->summary_key[$tagging] = $tagging;
			$this->summary_info[$tagging][$target_month] = $data;
		}
		
		return $result;
		
	}
	function getSummaryAttendance($user_id, $trgt_year){
		$query = "	SELECT 
						'attendance' AS summary_type,
						DATE_FORMAT(login,'%c') AS target_month,
						user_id,
						tagging,
						COUNT(*) AS CTR 
					FROM attendance
					WHERE tagging IN ('suspended','absent','late')
					AND user_id = '{$user_id}'
					AND DATE_FORMAT(login, '%Y') = '{$trgt_year}'
					GROUP BY user_id,tagging, target_month";
					
		$result = $this->db->query($query)->result_array();
		// $leave_lu = $this->getLookup('leave_sub',1);
		
		foreach($result as $data){
			
			extract($data);
			$this->summary_key[$tagging] = $tagging;
			$this->summary_info[$tagging][$target_month] = $data;
		}
		
		return $result;
		
	}
	
	function getRemainingLeaves($user_id){
		$cur_year = date('Y');
		$query = "SELECT 
					SUM(CASE tagging WHEN 'half_day' THEN 0.5 ELSE 1 END)  AS CTR
				  FROM leave_file 
				  WHERE file_status IN('approved' ,'pending')
				  AND tagging NOT IN ('vl_wout_p','hd_wout_p','sl_wout_p')
				  AND user_id = '{$user_id}'
				  AND DATE_FORMAT(target_date, '%Y') = '{$cur_year}' ";
				  
		$result = $this->db->query($query)->result_array();
		
		$user_info = $this->getUserInfo($user_id);
		
		$remaining_leaves = $user_info['leaves'];
		
		if($result && $remaining_leaves){
			
			$used_leave = $result[0]['CTR'];
			if($used_leave > $remaining_leaves){
				$remaining_leaves = 'O';
			}else{
				$remaining_leaves -= $used_leave;
			}
		}
		
		return $remaining_leaves;
	}

	/**
	 * 2023-07-22
	 * Cancellation of request either OT or leave
	 */
	public function cancelRequest($req_id, $req_type){
		$table = $req_type == 'leave' ? 'leave_file' : 'overtime';
		$update_arr = array('file_status' => 'canceled', 'modified_by' => $this->session->userdata('username'));
		$where_arr = array('id' => $req_id);

		$this->db->update($table,$update_arr, $where_arr);
		// echo $this->db->last_query();
	}
	
}