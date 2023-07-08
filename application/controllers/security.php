<?php
Class Security extends CI_Controller  { 
	public  function __construct(){
		parent::__construct();
		$this->load->model('security_model');
		$this->load->helper('url');
		$this->load->library('session');  
		$this->load->helper(array('form', 'url'));  
	}
	
	public function login(){
		$pdata = $this->input->post();
		
		$this->load->view('templates/header_login',array('title'=>'Security'));
		$data['message']= '';
		
		if($pdata){
			
			if($this->security_model->login())
				redirect('main');
			else
				$data['message']= 'Invalid login credentials!';
			
		} 
		
		$this->load->view('security/login',$data);
		#$this->load->view('templates/footer');
	}
	
	public function timein(){
		$this->security_model->do_time_proc(1);
		redirect('main');
	}
	public function timeout(){
		$this->security_model->do_time_proc(0);
		redirect('main');
	}
	
	public function time_logs(){
		
		$data['timedetails'] = $this->security_model->clock_details();  
		$this->load->view('templates/header',array('title'=>'Time Clock Logs','sub_title'=>'Time Clock Details'));
		$this->load->view('security/time',$data);
		$this->load->view('templates/footer');
		
	}
	
	public function logout(){
		$this->session->sess_destroy();
		redirect('security/login');
	}
	
	public function access_denied(){
		$this->load->view('templates/header',array('title'=>'Access denied'));
		$this->load->view('security/access_denied');
		$this->load->view('templates/footer');
	}
	
	public function cp()
	{
		$this->load->view('security/cp');
	}
	
	public function cp_save()
	{
		$result = $this->security_model->cp_save();
		if($result != 1)
			echo get_err_msgs($result);
		else
			echo "Saved!";
		
	}
}
?>