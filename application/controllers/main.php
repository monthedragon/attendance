<?php 
//include_once('Spreadsheet/Excel/Writer.php');
//include_once('Spreadsheet/Excel/Reader/reader.php');
Class Main extends Auth_Controller  { 
	var $data = array();
	//use `Auth_Controller` if you want the page to be validated if the user is logged in or not, if you want to disable this then use `CI_Controller` instead
	public  function __construct(){
		error_reporting(0);
		parent::__construct();
		$this->load->model('main_model');
		$this->load->helper('url');
		$this->load->library('session');  
		$this->load->helper(array('form', 'url'));  
		$this->has_permission(178);
		$this->data = $this->get_privs();
		/**load your own library
			or add in application/config/autoload/ under libraries
		**/
		//$this->load->library('MY_auth_lib');
	} 
	
	public function index($action = '', $break_type = '', $break_action = ''){
		$this->load->helper('form');
		$this->set_header_data(PROJECT_NAME);
			
		$data['session'] = $this->session->all_userdata();
		$data['privs'] = $this->data;
		$data['all_userdata'] = $this->session->all_userdata();
		
		if($action == 'login'){
			$this->main_model->doLogin();
		}elseif($action == 'logout'){
			$this->main_model->doLogout();
		}elseif($action == 'break'){
			$result = $this->main_model->doBreak($break_type, $break_action);
			//I decided not to show the break messeage, since the page will be loaded once click
			//once it referesh the approriate button will be shown
		}elseif($action == 'r'){
			$this->main_model->resetSWSession();
		}
		
		$data['bb_status'] = $this->main_model->getBreakStatus('bathroom');
		$data['cb_status'] = $this->main_model->getBreakStatus('coffee');
		
		$user_id = $this->session->userdata('user_id');
		$is_log_in 	= $this->main_model->checkActionDone('login');
		$is_log_out = $this->main_model->checkActionDone('logout');
				
		$data['is_log_in']  = $is_log_in;
		$data['is_log_out'] = $is_log_out;
		$data['user_full_name'] = $this->session->userdata('full_name');
		$data['today_att_info'] = $this->main_model->getTodayLog();
		$data['search_type_lu'] = $this->getLookup('search_type',1);
		
		//check if there is any SESSION saved for the main SW form
		$this->main_model->getSearchInitValue($data);
		
		$this->load->view('main/index',$data);
		
		$this->load->view('templates/footer'); 	
	}
	
	function search_agent(){
		$pdata = $this->input->post();
		
		//everytime search is being process, save all the params to the SESSION
		$this->main_model->setSeachPdataSession();

        $data['attendance_list'] = $this->main_model->get_agent_attendance();
        $data['hd_list_for_remarks'] = $this->main_model->hdForRemarks;
		$data['pending_leave_ctr'] = $this->main_model->getPendingLeaves();
		$data['pending_ot_ctr'] = $this->main_model->getPendingOT();
		$data['on_breaks'] 	= $this->main_model->on_break;
		$data['user_break_arr'] = $this->main_model->user_break_arr;
		$data['privs'] = $this->data;
		$data['taggingList'] = $this->getLookup('attendance',1);
		$data['leave_sub'] = $this->getLookup('leave_sub',1);
		$data['ot_sub'] = $this->getLookup('ot_sub',1);
		$data['max_ot_hr_tagging'] = $this->getLookup('ot_interval',1);
		$data['max_ut_hr_tagging'] = $this->getLookup('ut_interval',1);
		$data['break_type'] = $this->getLookup('breaks',1);
		$data['file_status'] = $this->getLookup('file_status',1);
		$data['user_type'] = $this->session->userdata('user_type');
		
		$data['search_type'] = $pdata['search_type'];

		//2023-07-22 get the username of the logged in user
		$data['cur_user'] = $this->session->userdata('username');
		
		$this->load->view('main/attendance_list',$data);	
	}

	public function edit($record){
		$attendance_info = $this->main_model->getAttendanceInfo($record);
		$fullname = $attendance_info['firstname']  . ' ' . $attendance_info['lastname'];
		
		$this->load->helper('form');
		$this->set_header_data(PROJECT_NAME,$fullname);
			
		$data['session'] = $this->session->all_userdata();
		$data['privs'] = $this->data;
		$data['all_userdata'] = $this->session->all_userdata();
		$data['attendance_info'] = $attendance_info;
		
		$logintime = date('h:i:A',strtotime($attendance_info['login'])); //convert to 12-hour format (hour:minute:meridiem)
		$logtime_expl = explode(':',$logintime); //make it an array;
		$data['login_time'] = $logtime_expl;
		
		if($attendance_info['logout']){
			$logouttime = date('h:i:A',strtotime($attendance_info['logout'])); //convert to 12-hour format (hour:minute:meridiem)
			$data['logout_time'] = explode(':',$logouttime);//make it an array
		}
		
		
		$data['absent_sub'] = $this->getLookup('absent_sub',1);
		$data['tagging'] = $this->getLookup('attendance',1);
		$data['hour_lu'] = $this->getLookup('hour',1);
		$data['minute_lu'] = $this->getLookup('minute',1);
		$data['meridiem'] = $this->getLookup('meridiem',1);
		$data['record'] = $record;
		
		$this->load->view('main/edit',$data);	
		
		$this->load->view('templates/footer'); 	
	}
	
	public function save($record){
		$this->main_model->save($record);
		$this->index();
	}
	
	//Filling leaves, absent etc . . . 
	public function file($init_tag = '', $init_data = array(), $record = ''){
		$this->load->helper('form');
		
		$data = $init_data;
		
		if($init_tag){
			$data['tagging'] = $init_tag;
		}
		
		$this->set_header_data(PROJECT_NAME,'FORMS');
		
		$username		= $this->session->userdata('username');
		$no_of_leave 	= $this->main_model->getRemainingLeaves($username);
		
		$data['session'] 		= $this->session->all_userdata();
		$data['privs'] 			= $this->data;
		$data['all_userdata'] 	= $this->session->all_userdata();
		$data['user_type'] 		= $this->session->userdata('user_type');
		$data['lu_tagging'] 	= $this->getLookup('filling',1);
		$data['absent_sub'] 	= $this->getLookup('absent_sub',1);
		$data['ot_sub'] 		= $this->getLookup('ot_sub',1);
		$data['leave_sub'] 		= $this->getLookup('leave_sub',1);
		$data['file_status_sub'] 	= $this->getLookup('file_status',1);
		$data['max_ot_hr_tagging'] 	= $this->getLookup('ot_interval',1);
		$data['max_ut_hr_tagging'] 	= $this->getLookup('ut_interval',1);
		$data['record'] 			= $record;
		$data['username'] 			= $username;
		
		if($data['user_type'] == ADMIN_CODE || $data['user_type'] == TL_CODE){
			$user_type = ($data['user_type'] == TL_CODE) ? array('agent','tl') : '';
			
			//Display all the users
			$data['user_list'] = $this->get_users(0,true,$user_type);
			//DEFAULT value either NONE or the previous selected comming from the submitted form
			
		}else{
			//Display limited filling type for non-admin
			$data['lu_tagging'] = $this->getLookup('filling_non_admin',1);
			//ONLY the agent itself willbe available on the pulldown
			$data['user_list'] = array($username=>$username);
			$data['user_id'] = $username;
			
			if($no_of_leave == 0.5){
				unset($data['leave_sub']['vacation']);
				unset($data['leave_sub']['sick']);
			}
			if($no_of_leave == 0){
				unset($data['leave_sub']['vacation']);
				unset($data['leave_sub']['sick']);
				unset($data['leave_sub']['half_day']);
			}
		}
				
		$data['no_of_leave'] = $no_of_leave;
		$data['user_list_for_disp'] = $this->get_users(0,true);
				
		$this->load->view('main/file',$data);	
		$this->load->view('templates/footer'); 	
	}
	
	
	public function save_file_form($record=''){
		
		if($record){
			$result = $this->main_model->saveSingleFile($record);	
		}else{
			$result = $this->main_model->save_file_form();	
		}
		
		$pdata = $this->input->post();
		$data['error_msg'] = '';
		$data['success_msg'] = '';
		
		if($result !== true){
			$data = $pdata;
			$data['error_msg'] = $result;
		}else{
			$data['success_msg'] = 'Successfully created';
		}
		
		if($record){ //if with record show edit screen
			$msg = 'Successfully Updated';
			$this->e_file($record,$pdata['tagging'], $msg);
		}else{
			$this->file($pdata['tagging'],$data);
		}
	}
	
	//edit/view the file 
	public function e_file($record,$file_type, $msg = ''){
		if($file_type == 'leave'){
			$data = $this->main_model->getLeave($record);
			$data['leave_sub_tagging'] = $data['tagging']; //set the value from tagging to the equivalent field name from the form
		}
		if($file_type == 'ot'){
			$data = $this->main_model->getOT($record);
			$data['ot_sub_tagging'] = $data['tagging']; //set the value from tagging to the equivalent field name from the form
		}
		if($file_type == 'ut'){
			$data = $this->main_model->getUT($record);
		}
		
		if($msg){
			$data['success_msg'] = $msg;
		}
		
		$this->file($file_type,$data, $record);
	}
	
	//update the file leave
	public function file_update($id){
		$data = $this->main_model->getLeave($id);
		$data['leave_sub_tagging'] = $data['tagging']; //set the value from tagging to the equivalent field name from the form
		$this->file('leave',$data);
	}
	
	
	//display summary details of leaves, OT, abset etc of selected user
	public function summary($user_id, $year = ''){
		
		//2022-03-05 added $year param
		if(!$year){
			$year = date('Y');
		}
		$data['year'] = $year;
		
		$user_type = $this->session->userdata('user_type');
		$data['user_type'] = $user_type;
		
		if($user_type == ADMIN_CODE || $user_type == TL_CODE){
			
			$user_type = ($data['user_type'] == TL_CODE) ? array('agent','tl') : '';
			//Display all the users
			$user_list = $this->get_users(0,true,$user_type);

		}else{
			
			//safe guard not to display other info to other user (non-admin and non-tl))
			$user_id = $this->session->userdata('username');
			$user_list = array($user_id=>$user_id);
			
		}
		$data['user_list'] = $user_list;
		$user_info = $this->main_model->getUserInfo($user_id);
		$data['user_id'] = $user_id;
		
		$this->set_header_data(PROJECT_NAME,'Summary Info: ' . $user_info['firstname'] . ' ' .$user_info['lastname']);
		
		$this->main_model->getSummaryInfo($user_id, $year);
		$data['summary_info'] = $this->main_model->summary_info;
		$data['summary_key'] = $this->main_model->summary_key;
		$data['leave_sub'] = $this->getLookup('leave_sub',1);
		$data['ot_sub'] = $this->getLookup('ot_sub',1);
		$data['att_sub'] = $this->getLookup('attendance',1);
		
		$data['year_list'] = $this->getYearPD();
		
		$this->load->view('main/summary',$data);	
		
		$this->load->view('templates/footer'); 	
		
	}
	
	//2022-03-05
	function getYearPD(){
		$start_year = 2021;
		$cur_year = date('Y');
		
		$year_pd = array();
		
		for($i = $start_year; $i <= $cur_year; $i++){
			$year_pd[$i] = $i;
		}
		
		return $year_pd;
	}
	
	function get_rem_leave_ajax($user_id){
		
		$no_of_leave = $this->main_model->getRemainingLeaves($user_id);
		ob_clean();
		echo $no_of_leave;
		exit();
		
	}

	function e_f_cancel($req_id, $req_type){
		$this->main_model->cancelRequest($req_id, $req_type);

	}

	/**
	 * Added @ 2023-09-09
	 * Approve all pending OT
	 */
	function approvePendingOT(){
		
		if($this->session->userdata('user_type') != ADMIN_CODE) 
			show_error('Unauthorized access', 403);

		$user_name = $this->session->userdata('username');

		$update_arr = array(
			'file_status' => 'approved',
			'modified_by' => $user_name,
			'modified_date'=>date('Y-m-d H:i:s')
		);

		$where_arr = array(
			'file_status' => 'pending',
		);

		$this->db->update('overtime',$update_arr, $where_arr);
	}
}
?>