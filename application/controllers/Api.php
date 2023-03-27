<?php

defined('BASEPATH') OR exit('No direct script access allowed');



require APPPATH . 'libraries/RestController.php';
require APPPATH . 'libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class Api extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Authorization_Token');
        $this->load->model("users_model");
        $this->load->helper('cookie');
        
    }

    public function register_post(){
        $array=array('status'=>'ok','data'=>'post api');
        $_POST = json_decode($this->input->raw_input_stream, true);  //to get json data
       $data=array(
        'first_name'=>$this->input->post("first_name"),
        'last_name'=>$this->input->post("last_name"),
        'email'=>$this->input->post("email"),
        "username"=>$this->input->post("username"),
        "password"=>$this->input->post("password")
       );

       //check if email already exists
       if(($this->users_model->check_user($data['email']))) {
        $this->response(array(
            "status"=>403,
            "message"=>"email already exists"
        ));
       }

       $hashed_password=password_hash($data['password'],PASSWORD_BCRYPT);
       $data['password']=$hashed_password;

       
       //setting new user
       $res=$this->users_model->set_user($data);

       if($res){
        $this->response(array(
            "status"=>201,
            "message"=>"user registered Success!!..",
            "data"=>$data
           ));
       }else{
        $this->response(array(
            "status"=>403,
            "message"=>"could not register user, server error"
        ));
       }
      

    
    }

    public function verify_post(){
        $header_token=$this->input->get_request_header('Authorization');
        $decoded_token=$this->authorization_token->validateToken($header_token);

        $this->response($decoded_token);
    }


    public function login_post(){

        $_POST = json_decode($this->input->raw_input_stream, true);  //to get json data
       
        try{
            $email=$this->input->post("email");
            $password=$this->input->post("password");

            if(!$this->users_model->check_user($email)) {
                $this->response(array(
                    "status"=>403,
                    "message"=>"invalid email"
                ));
            }
            
            $password_hashed=$this->users_model->get_hashed_password($email);
            if(password_verify($password,$password_hashed)){

                    $token_data['email']=$email;
                    $token_data=$this->authorization_token->generateToken($token_data);
                    $final=array();
                    $final['message']='User logged in';
                    $final['token']=$token_data;
                    $final['status']='ok';


                    //setting token in cookie
                    $cookie=array(
                        'name'=>"token",
                        'value'=>$token_data,
                        'expire'=>3600,
                        'secure'=>TRUE
                    );

                    set_cookie($cookie);


                    $this->response($final);
            }else{
                $this->response(array(
                    "status"=>403,
                    "message"=>"invalid password"
                ));
            } 
           
        }
        catch(Excecption $e)
        {
            $this->response(array(
                "status":501,
                "error":$e
            ));
        }

    }


    public function dashboard_get(){
       $token=get_cookie("token",TRUE);
        $decoded_token=$this->authorization_token->validateToken($token);

        $email=$decoded_token['data']->email;

        $data=$this->users_model->get_user($email);
        $this->response(array(
            "status"=>200,
            "message"=>"user details succuess",
            "data"=>$data
        ));
    }

    public function logout_post(){
        
        //deleting cookie
        delete_cookie("token");

        $this->response(array(
            "status"=>200,
            "message"=>"user logged out"
        ));
        
    }
}