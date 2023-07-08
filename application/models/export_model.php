<?php
class Export_model extends CI_Model {
    var $retVal = array();

    public function __construct()
    {
        $this->load->database();
    }

    public function get_column($table){
        return $this->db->list_fields($table);
    }

    public function set_default_condition(){
        $post = $this->input->get();

        if(!empty($post['start_calldate']))
            $this->db->where('contact_list.calldate >=',"{$post['start_calldate']} 00:00:00");

        if(!empty($post['end_calldate']))
            $this->db->where('contact_list.calldate <=',"{$post['end_calldate']} 23:59:59");

        if(isset($post['agents']))
            $this->db->where_in('contact_list.assigned_agent',$post['agents']);

        if(isset($post['lead_identities']))
            $this->db->where_in('contact_list.lead_identity',$post['lead_identities']);


    }

    public function do_export(){
        $post = $this->input->get();

        //select * in default
        $selectedCols = '*';

        //set selected columns
        if(isset($post['columns']))
            $selectedCols = implode(',',$post['columns']);

        $this->db->select($selectedCols.',id as contact_list_id')->from('contact_list');

        $this->set_default_condition();

        $result  =  $this->db->get()->result_array();


        return $result;
    }

    public function get_no_of_supple(){
        $retValAarr = array();
        $post = $this->input->get();

        $this->db->select('contact_list.id,count(*) as CTR')->from('contact_list')
            ->join('supplementary',"supplementary.baserecid = contact_list.id AND callresult ='AG'",'INNER');
        $this->set_default_condition();


        $result  =  $this->db->group_by('contact_list.id')->get()->result_array();


        foreach($result as $detail){
            $retValAarr[$detail['id']] = $detail['CTR'];
        }

        return $retValAarr;
    }

    public function get_no_of_touched(){
        $retValAarr = array();
        $post = $this->input->get();

        $this->db->select('contact_list.id,count(*) as CTR')->from('contact_list')
            ->join('trans_log',"trans_log.contact_id = contact_list.id ",'INNER');
        $this->set_default_condition();


        $result  =  $this->db->group_by('contact_list.id')->get()->result_array();


        foreach($result as $detail){
            $retValAarr[$detail['id']] = $detail['CTR'];
        }

        return $retValAarr;
    }
		
		/**
		* The result from this method will be only used when the GENERATED DATA has empty CALLRESULT and WILL replace the EMPTY CR by tl_callresult
		* And it also counts the number of touched :: this get_no_of_touched() is no longer used
		*/
		public function get_last_disposition(){
				$retValAarr = array(); 
				$this->set_default_condition(); 
				
				$custom_where = '';
				if($this->db->ar_where){
					$custom_where = ' WHERE '. implode("\n", $this->db->ar_where);
					$this->db->ar_where = array(); //reset the ar_where manually
				}
								
				$query = "
					SELECT *,COUNT(*) as CTR 
						FROM (
							SELECT 
								contact_list.id AS contact_list_id,
								trans_log.callresult AS tl_callresult,
								trans_log.sub_callresult AS tl_sub_callresult
							FROM (`contact_list`)
							INNER JOIN trans_log ON trans_log.`contact_id` = contact_list.id
							{$custom_where}
							ORDER BY trans_log.contact_id,trans_log.time_stamp DESC
					) AS new_tbl 
					GROUP BY contact_list_id
				
				";

				$result = $this->db->query($query)->result_array();
				
        foreach($result as $detail){
            $retValAarr[$detail['contact_list_id']] = $detail;
        }
				 
        return $retValAarr;
		}

	function get_remarks(){
	
        $post = $this->input->get();
		if(!isset($post['include_remarks'])) return '';
		
		$this->db->select('contact_list.id,remarks,user_id')->from('contact_list')
            ->join('trans_log',"trans_log.contact_id = contact_list.id ")
			->order_by('contact_list.id');
        $this->set_default_condition();
		
		$result  =  $this->db->get()->result_array();
		 
        foreach($result as $detail){
			if(!isset($retValAarr[$detail['id']])) $retValAarr[$detail['id']] = '';
			
			if(!empty($detail['remarks'])){
				if(!empty($retValAarr[$detail['id']])) $retValAarr[$detail['id']] .= '<br>';
				$retValAarr[$detail['id']] .= '['.$detail['user_id'].']'.nl2br($detail['remarks']);
			}
        }

        return $retValAarr;
	}
}
