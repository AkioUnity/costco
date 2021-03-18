<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ShowPage extends MY_Controller {
	public function index($page = 'home')
	{
        redirect('admin', 'refresh');

    }
    public function test(){

    }
}
