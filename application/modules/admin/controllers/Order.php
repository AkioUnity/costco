<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends Admin_Controller {
    public $unset_array;
    public function __construct()
    {
        parent::__construct();
        $this->unset_array=array('costco_account','order_msg','created','ship_address_2','ship_service','etc');
        $this->load->model('wait_model');
        $data = $this->wait_model->update_all(array('status'=>'done'));

    }

	// Image CRUD - Cover Photos
	public function index()
	{
        $crud = $this->generate_crud('orders');
//        $crud->unset_columns($this->unset_array);
        $this->mPageTitle = 'All List';
        $this->mViewData['order_type'] = 'all';
//        $crud->columns('author_id', 'category_id', 'title', 'image_url', 'tags', 'publish_time', 'status');
        $this->render_crud();
	}

    public function new_orders()
    {
        $crud = $this->generate_crud('orders');
        array_push($this->unset_array,'track_number','costco_order_id','shipping_charge','tax','total');
        $crud->unset_columns($this->unset_array);
        $crud->where('costco_order_id',0);
        $crud->add_action("Place Order",'','/admin/invoice/view_invoice','fa fa-file-text-o smart_btn');
        $this->mPageTitle = 'New Orders';
        $this->mViewData['order_type'] = 'new';
        $this->render_crud();
    }

    public function placed()
    {
        $crud = $this->generate_crud('orders');
        $crud->unset_columns($this->unset_array);
        $this->mPageTitle = 'Placed Orders';
        $this->mViewData['order_type'] = 'placed';
        $crud->where('costco_order_id !=',0);
//        $crud->columns('author_id', 'category_id', 'title', 'image_url', 'tags', 'publish_time', 'status');
        $this->render_crud();
    }

    public function price()
    {
        $crud = $this->generate_crud('orders');
        $crud->unset_columns($this->unset_array);
        $this->mPageTitle = 'Price Check';
        $this->mViewData['order_type'] = 'price';
//        $crud->columns('author_id', 'category_id', 'title', 'image_url', 'tags', 'publish_time', 'status');
        $this->render_crud();
    }
}
