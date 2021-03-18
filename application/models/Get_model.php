<?php
class Get_model extends CI_Model {
	function getCurTime(){
		$time = "0000-00-00 00:00:00";
		$query = $this->db->query("SELECT CURRENT_TIMESTAMP as timestamp");
		foreach($query->result() as $row){
			$time = $row->timestamp;
			break;
		}
		return $time;
	}
	function insert_table($table, $data){
		$this->db->insert($table, $data);
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}
	function update_table($table, $id, $data){
		$this->db->where("id", $id);
		$this->db->update($table, $data);
		return true;
	}
    function update_table_field($table,$field, $value, $data){
        $this->db->where($field, $value);
        $this->db->update($table, $data);
        return true;
    }
	function get_list($strquery){
		$query = $this->db->query($strquery);
		if($query->num_rows()>0){
			$data= $query->result_object();
		}else{
			$data= array();
		}
		return $data;
	}
	function get_item($strquery){
		$query = $this->db->query($strquery);
		if($query->num_rows()>0){
			$data= $query->first_row();
		}else{
			$data= false;
		}
		return $data;
	}
    function exec_query($strquery){
        $this->db->query($strquery);
        return true;
    }
    function insert_item($table, $data){
        $this->db->insert($table, $data);
        if(!$this->db->affected_rows()){
            return false;
        }
        return $this->db->insert_id();
    }
    function update_item($table, $id, $data){
        if ($id) {
            $this->db->where("id", $id);
        }
        $this->db->update($table, $data);
        return $this->db->affected_rows();
    }
    function getSettings(){
        $query = $this->db->get("settings");
        return $query->first_row();
    }
    function checkExist($table, $field, $value, $id=0){
        $this->db->where($field, $value);
        if($id)
            $this->db->where("id!=", $id);
        if($table == 'users')
		    $this->db->where("status!=", 2);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
            return true;
        }else{
            return false;
        }

    }

    function checkExist2($table, $field, $value, $field2, $value2){
	    $this->db->select("id");
        $this->db->where($field, $value);
        $this->db->where($field2, $value2);
        $query = $this->db->get($table);
        if($query->num_rows()>0){
            return $query->row()->id;
        }else{
            return false;
        }

    }
}