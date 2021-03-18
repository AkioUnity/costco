<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OldOrder extends Admin_Controller {

	// Image CRUD - Cover Photos
	public function index()
	{
        $this->load->model('user_model', 'users');
        $target = $this->users->get_by('email',"gtscostco25@gmail.com");
        $this->mViewData['multiple_order'] = $target->multiple_order;
        $this->render('order');
	}
}
