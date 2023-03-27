<?php

class Users_model extends CI_Model{
    
    
    public function __construct(){
    }

    public function check_user($email){
        if($email){
            $this->db->select("*");
            $this->db->from("users");
            $this->db->where("email",$email);
            $query = $this->db->get();


            if($query->num_rows()==1) 
            {
                return TRUE;
            }
            else{
                return FALSE;
            } 
        }

    }

    public function get_hashed_password($email){
        $this->db->select("*");
        $this->db->from("users");
        $this->db->where("email",$email);

        $query=$this->db->get();
        $row=$query->row();

        if($row->password){
           return $row->password;
        }else{
            return null;
        }
    }

    public function set_user($data){

        return $this->db->insert('users',$data);
    }

    public function get_user($email){
        $this->db->select("*");
        $this->db->from("users");
        $this->db->where("email",$email);

        $query=$this->db->get();
        return $query->row();
    }


}