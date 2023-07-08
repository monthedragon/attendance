<?php
class Manage_model extends CI_Model {
	var $retVal = array();

	public function __construct()
	{
		$this->load->database();
	}
	
	public function save_cr($luID){
		$pdata = $this->input->post();
		$crExist = 0; //default to 0 none
		
		if($luID == 0)
			$crExist = $this->check_lookup($pdata['lu_code'],$pdata['lu_cat']);
		
		if(!$crExist){
			if($luID == 0)
				$this->db->insert('lookup',$pdata);
			else
				$this->db->update('lookup',$pdata,array('id'=>$luID));
				
			echo 1;
		}else{
			echo 0;
		}
	}
	
	function check_lookup($lucode,$lucat){
		$result = $this->db->select('count(*) as ctr')
				->from('lookup')
				->where('lu_code',$lucode)
				->where('lu_cat',$lucat)
				->get()
				->result_array();
		
		if($result[0]['ctr']>0)
			return 1; //if exist
		else
			return 0; //if not
	}
	
	function save_script(){
		$pdata = $this->input->post();
		$this->db->update('script',$pdata);
	}
	
	function save_faqs(){
		$pdata = $this->input->post();
		$this->db->update('faqs',$pdata);
	}

    public function get_active_column($displayFields){
        $retVal = array();
        $fields = $this->db->list_fields('contact_list');


        foreach($fields as $col){
            //if not yet in the display fields then show the column in selection
            if(!isset($displayFields[$col]))
                $retVal[] = $col;
        }

        return $fields;
    }

    public function save_fields(){
        $post = $this->input->post();
        $this->db->on_duplicate('field_display',$post);
    }
	    
}