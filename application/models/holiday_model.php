<?php
class Holiday_model extends CI_Model {
	public function __construct()
	{
		$this->load->database();
	}
	
	public function save(){
		$user_name = $this->session->userdata('username');
		$pdata 		= $this->input->post();
		
		if(!$this->checkDuplicateHoliday()){ 
			
			$insert_arr = array('holiday'=>$pdata['holiday'],
								'target_date'=>$pdata['target_date'],
								'holiday_type'=>$pdata['holiday_type'],
								'modified_by'=>$user_name,
								'modified_date'=>date('Y-m-d H:i:s'),
								'created_by'=>$user_name,
								'date_entered'=>date('Y-m-d H:i:s'));
								
			$this->db->insert('holidays',$insert_arr);
		}
	}
	
	//TODO
	function checkDuplicateHoliday(){
		return false; //no duplicate
	}
	
}