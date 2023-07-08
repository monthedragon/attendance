<?php
class Script_model extends CI_Model {

    public $currentPageDetails = '';

    public function __construct()
	{
		$this->load->database();
	}
    public function get_script_list(){
        return $this->db->from('script')
                        ->where('is_active',1)
                        ->order_by('order','asc')
                        ->get()->result_array();
    }

    public function get_script($page){
        $this->db->select('*')->from('script')
                  ->where('is_active',1);

        if(empty($page)){
            $this->db->where('order',1);
        }else{
            $this->db->where('id',$page);
        }

        $result = $this->db->get()->result_array();
        $this->currentPageDetails = $result[0];
        return $result[0];
    }

    public function get_next_page(){
        $currentPage = $this->currentPageDetails['order'];
        $result = $this->db->from('script')
                ->where('is_active',1)
                ->where('order >',$currentPage)
                ->order_by('order','asc')
                ->limit(1)
                ->get()
                ->result_array();

        return (($result) ? $result[0] : '');
    }

    public function get_prev_page(){
        $currentPage = $this->currentPageDetails['order'];
        $result = $this->db->from('script')
            ->where('is_active',1)
            ->where('order <',$currentPage)
            ->order_by('order','DESC')
            ->limit(1)
            ->get()
            ->result_array();

        return (($result) ? $result[0] : '');

    }

    function save_script(){
        $pdata = $this->input->post();

        if(!empty($pdata['id'])){
            echo "HERE I AM";
            $wheredata = array('id'=>$pdata['id']);
            $this->db->update('script',$pdata,$wheredata);
        }else{
            ECHO "TEST";
            //soon i will be using one MVC for this!
            unset($pdata['id']);
            $this->db->insert('script',$pdata);
        }

    }


}