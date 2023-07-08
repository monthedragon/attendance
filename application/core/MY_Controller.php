<?
class Auth_Controller extends CI_Controller {

    function __construct()
    { 
        parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');   
        if ( ! $this->session->userdata('logged_in'))
        { 
            redirect('security/login');
        }
    }

	function set_header_data($title='ACQUI',$sub_title=''){
		$data['timestatus'] = $this->check_time_status();
		$data['title'] = $title;
		
		if(!empty($sub_title))
			$data['sub_title'] = $sub_title;
			
		$data['user_id'] = $this->session->userdata('username');
		$data['user_name'] = $this->session->userdata('full_name');
		$data['user_type'] = $this->session->userdata('user_type');
		$data['is_logged'] = $this->session->userdata('logged_in');
		$data['privs'] = $this->data;
		$this->load->view('templates/header',$data); 
	}
	
	//check if the current user has permission to selected right
	function has_permission($rightID)
	{
		$privs = $this->get_privs();
		if(!isset($privs[$rightID]))
			redirect('security/access_denied');
	}
	 
	function get_privs()
    {
		return $this->session->userdata('privs');
	}
	 
	function get_username()
	{
		return $this->session->userdata('username');
	}
	
	function get_user_fullname(){
		return $this->session->userdata('fullname');
	}
	
	function check_time_status(){
		$username = $this->get_username();
		
		$result = $this->db->select('log_status')->from('users')->where('user_name',$username)->get()->result_array();
		
		return $result[0]['log_status'];
	}
	
	//multiple retrieval of users
	function get_users_grp_by_id(){
		$retVal = array();
		$result = $this->db->select('*')->from('users')->get()->result_array();
		
		foreach($result as $d)
			$retVal[$d['user_name']] = $d['firstname'] . '  ' . $d['lastname'];
		
		return $retVal;
	}
	
	
	//single retrieval of user
	function get_user_grp_by_id($userid){
		$retVal = array();
		$result = $this->db->select('*')->from('users')->where('user_name',$userid)->get()->result_array();
		
		foreach($result as $d)
			$retVal[$d['user_name']] = $d['firstname'] . '  ' . $d['lastname'];
		
		return $retVal;
	}
	
	function get_users($all=0,$construct_array = false, $user_type = ''){
		$retVal = array();
        $this->db->select('*')->from('users');

        if($all==0)
            $this->db->where('is_active',1);
		
		if($user_type){
			$this->db->where_in('user_type', $user_type);
		}

		$result = $this->db->order_by('is_active,lastname')->get()->result_array();
		
		//Index will be the users.user_name
		if($construct_array){
			$arr_assoc = array();
			foreach($result as $details){
				$arr_assoc[$details['user_name']] = $details['firstname']. ' ' . $details['lastname'];
			}
			return $arr_assoc;
		}
		
		return $result;
	}
	
	//iden =0 regular array; iden = 1 index of lu_code
	//all = 1 get all even its not active
	function getLookup($lu_cat,$iden=0,$all=1){
		$data = array();
		$this->db->select('*')->from('lookup')
					->where('lu_cat',$lu_cat);
		
		if(!$all) //if not all then get only all active
			$this->db->where('is_active',1);
		
		$result = $this->db->order_by('order_by')
					->get()
					->result_array();
					
		if($iden == 0)		
			$data = $result;
		else{
			
			foreach($result as $d)
				$data[$d['lu_code']] = $d['lu_desc'];
				
		}
		
		return $data;
	}
	
	function getAllLookUp()
	{
		$data = array();
		$result =  $this->db->select('*')->from('lookup')
					->where('is_active',1)
					->order_by('lu_cat,order_by')
					->get()
					->result_array(); 
			
		foreach($result as $d)
			$data[$d['lu_code']] = $d['lu_desc'];
				
		  
		return $data;
	}
	
	function get_leads_details($all=0)
	{
		$this->db->select('*')->from('leads_details');
			
		if(!$all)
			$this->db->where('is_active',1);
		
				
		return $this->db->order_by('is_active','DESC')->get()->result_array(); 
		
	}
	
	function get_cr_with_sub(){
		return explode(',',CR_WITH_SUB_CR);
	}
	
	function get_script(){
		$result = $this->db->select('*')->from('script')->get()->result_array();
		
		if(isset($result[0]['script']))
			return $result[0]['script'];
		else
			return '';
	}
	
	function get_faqs(){
		$result = $this->db->select('*')->from('faqs')->get()->result_array();
		
		if(isset($result[0]['faqs']))
			return $result[0]['faqs'];
		else
			return '';
	}

    function get_displayed_fields(){
        $retVal = array();
        $result =  $this->db->select('*')
            ->from('field_display')
            ->where('is_active',1)
            ->order_by('display_group')
            ->order_by('column_name')
            ->get()
            ->result_array();

        foreach($result as $data)
            $retVal[$data['column_name']] = $data;

        return $retVal;
    }
}
?>