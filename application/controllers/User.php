<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $userdata = $this->session->userdata('backend020_admin');
        if(!$userdata || $userdata['level']==0)
            redirect(base_url('admin/login'));
        $this->load->model('get_model', '', TRUE);
    }
    public function get_list(){
        $result = array();
        $query = "select * from users where status!=2 order by name";
        $qresult = $this->get_model->get_list($query);
        foreach($qresult as $item){
            if($item->level==1)
                $level = "Admin";
            else
                $level = "Sales";
            $action =  "<button class='btn btn-success btn-sm' onclick='editUser(".$item->id.");'><i class='fa fa-pencil-square'></i></button>";
            if($item->status==0)
                $action .= "<button class='btn btn-warning btn-sm' onclick='updateUserStatus(".$item->id.", 1);'><i class='fa fa-ban'></i></button>";
            else
                $action .= "<button class='btn btn-info btn-sm' onclick='updateUserStatus(".$item->id.", 0);'><i class='fa fa-circle-o'></i></button>";
            $action .= "<button class='btn btn-danger btn-sm' onclick='updateUserStatus(".$item->id.", 2);'><i class='fa fa-times'></i></button>";
            $result[] = array(
                "id" => $item->id,
                "no" => '',
                "name" => $item->name,
                "email" => $item->email,
                "level" => $level,
                "action" => $action
            );
        }
        echo json_encode($result);
    }
    public function get_data(){
        $id = $this->input->post('id');
        $query = "select * from users where id='$id'";
        $qresult = $this->get_model->get_item($query);
        echo json_encode($qresult);
    }
    public function updateStatus(){
        $id = $this->input->post('id');
        $id = implode(",", $id);
        $status = $this->input->post('status');
        $query = "update users set status='$status' where id in ($id)";
        $this->get_model->exec_query($query);
        echo json_encode(true);
    }

    public function update(){
        $data = $this->input->post("data");
        $id = $data["id"];
        unset($data["id"]);
        if($this->get_model->checkExist("users", "name", $data["name"], $id)){
            echo json_encode(array("success"=>false, "msg"=>"already existing name"));
            return;
        }
        if($this->get_model->checkExist("users", "email", $data["email"], $id)){
            echo json_encode(array("success"=>false, "msg"=>"already existing email"));
            return;
        }
        if(strlen($data['passwd']))
            $data['passwd'] = md5($data['passwd']);
        if($id==0){
            $id = $this->get_model->insert_item('users', $data);
        }else{
            if(!strlen($data['passwd']))
                unset($data['passwd']);
            $this->get_model->update_item('users', $id, $data);
        }
        echo json_encode(array("success"=>true, "msg"=>$id));
    }
}