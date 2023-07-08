<?php
Class Script extends Auth_Controller  {
    public  function __construct(){
        parent::__construct();
        $this->load->model('script_model');
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->helper(array('form', 'url'));
        $this->data = $this->get_privs();
    }

    function init(){
        $this->load->view('script/init');
    }

    function render($page='',$modal=0){
        $script = $this->script_model->get_script($page);
        $data['script'] = $script;
        $data['prevpage'] = $this->script_model->get_prev_page();
        $data['nextpage'] = $this->script_model->get_next_page();
        $data['modal'] = $modal;
        $this->load->view('script/render',$data);
    }

    function view_list(){
        $this->set_header_data('','Manage Script');
        $data['scripts'] = $this->script_model->get_script_list();
        $this->load->view('script/list',$data);
        $this->load->view('templates/footer.php');
    }

    function edit($id){
        $pdata = $this->input->post();
        if(isset($pdata['script'])){
            $this->script_model->save_script();
            $this->view_list();

        }else{
            $this->set_header_data('','Edit Script');
            $data['script']  = $this->script_model->get_script($id);;
            $this->load->view('script/edit',$data);
            $this->load->view('templates/footer.php');
        }

    }

    function add(){
        $pdata = $this->input->post();
        if(isset($pdata['script'])){
            $this->script_model->save_script();
            $this->view_list();

        }else{
            $this->set_header_data('','Add Script');
            $this->load->view('script/add');
            $this->load->view('templates/footer.php');
        }

    }
}
?>