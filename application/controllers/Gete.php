<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');

class Gete extends CI_Controller
{
	
	
	public function __construct()
    {
        parent::__construct();
		ini_set('max_execution_time', 0);
        set_time_limit(0);
        $this->load->model('get_model', '', TRUE);
        //$this->load->helper('Costco');
    }
	
	public function make_post(){
		
		$id= $this->input->get("id");
		$username='gtscostco+50@gmail.com';
		$pass='Costco12345';
		
		//$data=array(
		//"logonId"=> $username,
		//"logonPassword"=>$pass
		//);
		
		//$url="https://m.costco.com/Logon";
		//$id= $this->makepost($url,$data);
		$query="select * from requests where id=$id";
		$qresult = $this->get_model->get_list($query);
		
		foreach($qresult as $qr){
			
					//var_dump($qr);
					//echo $qr->id;
					//$qr->url;
					$data=(unserialize($qr->data));
					//var_dump($data);
						$str= '<form action="'. $qr->url.'" method="post" id="fomy" >';
						foreach ($data as $key => $value){
							
							$str.='<input type="text" name="'.$key.'" value="'.$value.'" />';
							
						}
					$str.='<input type="submit"   value="submit" />';
			echo $str;
			
		}
		
		
		
		
		
	}
	
	public function makesubmit($session){
		
	$page=$session->getPage();
	//var_dump($page);
	$SignInForm=$page->find('named', array('id',"fomy" ));
	$SignInForm->submit();
	
	
	}
	
	public function make_sim(){
		
			require_once "vendor/autoload.php";

		$driver = new \Behat\Mink\Driver\Selenium2Driver('chrome');

			$session = new \Behat\Mink\Session($driver);

		$session->start();
		$session->visit("https://m.costco.com");
		$session->visit(base_url()."gete/make_post?id=1");
		$this->makesubmit($session);
	}
	
	
	public function makepost($url,$dat){

		$data=array(
		"url"=> $url,
		"data"=>	serialize($dat)
		);
		
		
		return $this->get_model->insert_item("requests",$data);
		
	}
}