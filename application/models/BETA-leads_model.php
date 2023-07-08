<?php
class Leads_model extends CI_Model {
	var $retVal = array();

	public function __construct()
	{
		$this->load->database();
	}
	    
	function is_restricted_field($col){
		//add restricted field here
		$restrictedFields  = array('id','lead_identity');
		 
		if(in_array($col,$restrictedFields))
			return 1;
		else
			return 0;
	}
	
	function check_column_alter($col){
		if(!$this->is_restricted_field($col)){
		
			$fields = mysql_list_fields(MAIN_DB, CONTACT_LIST);
			$columns = mysql_num_fields($fields);
			for ($i = 0; $i < $columns; $i++) {$field_array[] = mysql_field_name($fields, $i);}

			if (!in_array($col, $field_array)){
				
				$sql = 'ALTER TABLE '.CONTACT_LIST.
						' ADD `' . $col . '` CHAR(60) NOT NULL';
				$this->db->query($sql);
			}
			
		}
	}
		  
	function structure_column($col){
		$col = str_replace("'","",$col);
		$col = str_replace(' ','_',$col);
		$col = preg_replace('/[^A-Za-z0-9\_-]/', '', $col);
		return strtolower(trim($col));
	}
	
	function do_batch_disable(){
		$udata = $this->upload->data();  
		$pdata = $this->input->post();
		
		$allowedTypes = array('application/msword','application/vnd.ms-excel');
		 
		$validValues = array('id','is_active');
		
		if(!in_array($udata['file_type'],$allowedTypes))
			return 2;
		else{
		
			//read the db xls
			$dataXls = new Spreadsheet_Excel_Reader();
			$dataXls->read($udata['full_path']); 
			
			for ($j = 1; $j < $dataXls->sheets[0]['numRows']; $j++){
				//var to hold data of valid record
				$updateData = array();
				$whereArr = array();
				
				for($a=1;$a<=$dataXls->sheets[0]['numCols'];$a++){
					
					$xlsCurCol =  $this->structure_column($dataXls->sheets[0]['cells'][1][$a]);
					
					if(in_array($xlsCurCol,$validValues)){
						$val = isset($dataXls->sheets[0]['cells'][$j+1][$a]) ? $dataXls->sheets[0]['cells'][$j+1][$a] : '';
							
						if($val != '')
							$val = trim(str_replace("'","''",$val));
						
						if($xlsCurCol == 'id')
							$whereArr['id'] = $val;
						
						if($xlsCurCol == 'is_active')
							$updateData['is_active'] = $val;
							
					}else{
						return 3;
					}					
				} 
				$this->db->update('contact_list',$updateData,$whereArr);
			}
			
			return 1;
		}
	
	}
	
	function do_batch_upload(){
		$udata = $this->upload->data();  
		$pdata = $this->input->post();
		
		$allowedTypes = array('application/msword','application/vnd.ms-excel');
		 
		$validValues = array('firstname',
								'middlename',
								'lastname',
								'telno',
								'mobileno',
								'officeno',
								'credit_limit',
								'ava_cred_limit',
								'bill_cycle',
								'double_cl');
		
		
		
		if(!in_array($udata['file_type'],$allowedTypes))
			return 2;
		else{
		
			//read the db xls
			$dataXls = new Spreadsheet_Excel_Reader();
			$dataXls->read($udata['full_path']); 
			
			for ($j = 1; $j < $dataXls->sheets[0]['numRows']; $j++){
				//var to hold data of valid record
				$insertData = array();
				$insertData['lead_identity'] = $pdata['lead_identity'];
				for($a=1;$a<=$dataXls->sheets[0]['numCols'];$a++){
					
					$xlsCurCol =  $this->structure_column($dataXls->sheets[0]['cells'][1][$a]);
					
					$this->check_column_alter($xlsCurCol);
					
					#if(in_array($xlsCurCol,$validValues)){
					$val = isset($dataXls->sheets[0]['cells'][$j+1][$a]) ? $dataXls->sheets[0]['cells'][$j+1][$a] : '';
						
					if($val != '')
						$val = trim(str_replace("'","''",$val));
					
					$insertData[$xlsCurCol] = $val;
						
					#} 
				}  
				$this->db->insert('contact_list',$insertData);
			}
			
			$this->db->insert('leads_details',array('lead_identity'=>$pdata['lead_identity'])); //insert lead_identity in the list
			
			return 1;
		}
	
	}
	
	public function get_card_details($id)
	{
		return $this->db->select('*')->from('card_details')->where('baserecid',$id)->get()->result_array();
	}
	
	public function get_card_details_by_ID($cardID)
	{ 
		return $this->db->select('*')->from('card_details')->where('id',$cardID)->get()->result_array();
	}
	
	public function get_supple($id)
	{
		return $this->db->select('*')->from('supplementary')->where('baserecid',$id)->get()->result_array();
	} 
	
	public function get_supple_details_by_ID($suppleID)
	{
		return $this->db->select('*')->from('supplementary')->where('id',$suppleID)->get()->result_array();
	}
	
	public function save_supple($id,$suppleID)
	{
		$pdata = $this->input->post();
		
		if($suppleID == 0) //insert
			$this->db->insert('supplementary',array_merge(array('baserecid'=>$id),$pdata));
		else //update
			$this->db->where('id',$suppleID)->update('supplementary',$pdata);
	}
	
	public function save_card($id,$cardID)
	{
		$pdata = $this->input->post();
		
		if($cardID == 0) //insert
			$this->db->insert('card_details',array_merge(array('baserecid'=>$id),$pdata));
		else //update
			$this->db->where('id',$cardID)->update('card_details',$pdata);
	} 
	
	public function get_allocated_leads($userid)
	{ 
		$retVal = array();
		$result = $this->db->select('lead_identity,
								count(case when calldate is null then 1 else null end ) as VIRGIN')
				->from('contact_list')
				->where('is_active',1)
				->where('assigned_agent',$userid)
				->group_by('lead_identity')
				->get()
				->result_array();
				
		foreach($result as $r)
			$this->retVal[$r['lead_identity']]['V'] = $r['VIRGIN'];
			
		$this->get_allocated_leads_touched($userid);
		
		
		return $this->retVal;
	}
	
	function get_allocated_leads_touched($userid){
		$retVal  = array();
		$query = "SELECT 
					lead_identity,callresult,count(*) as CTR
				FROM contact_list
				WHERE is_active = 1 
				AND calldate is not null
				AND assigned_agent = '{$userid}'
				GROUP BY lead_identity,callresult";
		
		$result =  $this->db->query($query)->result_array();
		
		foreach($result as $r)
			$this->retVal[$r['lead_identity']][$r['callresult']] = $r['CTR'];
	
		return $this->retVal;
	}
	 
	
	public function get_unallocated_leads()
	{
		$retVal =array();
		$sql = "Select 
					lead_identity,callresult,count(*) as CTR 
				from contact_list 
				where is_active = 1 
				and assigned_agent = '' 
				and calldate is not null
				group by callresult,lead_identity";
				
		$result =  $this->db->query($sql)->result_array();
		
		foreach($result as $r)
			$retVal[$r['lead_identity']][$r['callresult']] = $r['CTR'];
		
		
		return $retVal;
	}
	
	public function get_unallocated_virgin_leads()
	{
		$retVal =array();
		$sql = "Select lead_identity,count(*) as CTR 
				from contact_list 
				where calldate is null  
				and is_active = 1 
				and assigned_agent = '' 
				group by lead_identity";
		
		$result =  $this->db->query($sql)->result_array();
		 
		foreach($result as $r)
			$retVal[$r['lead_identity']] = $r['CTR'];
		
		
		return $retVal;	
	}
	
	public function allocate_leads(){
		$post = $this->input->post();
		$agentID = $this->session->userdata('agentID');
		
		//Allocation for removing leads
		if($post['allocType'] == 1){
			$sql = "UPDATE contact_list
					SET 
						assigned_agent = '',
						callresult = ''
					WHERE is_active = 1
					AND assigned_agent = '{$agentID}'";
			
			if($post['callresult'] == VIRGIN_CODE)//if de-allocation for virgin
				$sql .= " AND calldate is null ";
			else
				$sql .= " AND callresult = '{$post['callresult']}' ";
		}else{
			
			$sql = "UPDATE contact_list
				SET assigned_agent = '{$agentID}'
				WHERE is_active = 1 
				and assigned_agent = '' ";
			
			if($post['callresult'] == VIRGIN_CODE)//if allocation for virgin
				$sql .= " AND calldate is null ";
			else
				$sql .= " AND callresult = '{$post['callresult']}' ";
		}
		
		$sql .= " AND lead_identity = '{$post['li']}'
		          AND is_locked = 0
				  AND forcedpop = 0 
				 limit {$post['value']} ";

		$this->db->query($sql);
	}

    //get lead identity in users_lead_identity
    public function get_lead_identity_assigned($userID){
        $retVal = array();

        $result = $this->db->select('*')
                        ->from('users_lead_identity')
                        ->where('is_assign',1)
                        ->where('user_id',$userID)
                        ->get()
                        ->result_array();

        foreach($result as $data){
            $retVal[$data['lead_identity']] = $data['lead_identity'];
        }

        return $retVal;
    }

    public function lead_iden_activator(){
        $agentID = $this->session->userdata('agentID');
        $post = array_merge($this->input->post(),array('user_id'=>$agentID));
        $this->db->on_duplicate('users_lead_identity',$post);
    }
	
	public function check_lead_identity(){
		$post = $this->input->post();
		
		$result = $this->db->select('count(*) as ctr')
				->from('leads_details')
				->where('lead_identity',$post['li'])
				->get()
				->result_array();
		
		if($result[0]['ctr'] > 0)
			echo  1; //existing then alert user this is not allowed!
		else
			echo  0;
	}
	
	public function li_activator(){
		$post = $this->input->post();
		$this->db->update('leads_details',array('is_active'=>$post['is_active']),array('id'=>$post['id']));
	}
}