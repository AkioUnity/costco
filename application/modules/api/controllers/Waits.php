<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Waits extends API_Controller {

	public function event_get()
	{
		$this->load->model('get_model');
        $query = "select * from waits where status<>'done'";
        $data = $this->get_model->get_list($query);
		$this->response($data);
	}

    public function reset_get()
    {
        $this->load->model('wait_model');
        $data = $this->wait_model->update_all(array('status'=>'done'));
        $this->response($data);
    }

    /**
	 * @SWG\Get(
	 * 	path="/blog/{id}",
	 * 	tags={"blog"},
	 * 	summary="Look up a blog post",
	 * 	@SWG\Parameter(
	 * 		in="header",
	 * 		name="X-API-KEY",
	 * 		description="API Key",
	 * 		required=true,
	 * 		type="string"
	 * 	),
	 * 	@SWG\Parameter(
	 * 		in="path",
	 * 		name="id",
	 * 		description="Blog Post ID",
	 * 		required=true,
	 * 		type="integer"
	 * 	),
	 * 	@SWG\Response(
	 * 		response="200",
	 * 		description="Blog Post object",
	 * 		@SWG\Schema(ref="#/definitions/BlogPost")
	 * 	),
	 * 	@SWG\Response(
	 * 		response="404",
	 * 		description="Invalid ID"
	 * 	)
	 * )
	 */
	public function id_get($id)
	{
		$this->load->model('blog_post_model', 'posts');
		$data = $this->posts->get($id);
		empty($data) ? $this->error_not_found() : $this->response($data);
	}
	
	/**
	 * @SWG\Get(
	 * 	path="/blog/categories",
	 * 	tags={"blog"},
	 * 	summary="List out blog categories",
	 * 	@SWG\Parameter(
	 * 		in="header",
	 * 		name="X-API-KEY",
	 * 		description="API Key",
	 * 		required=true,
	 * 		type="string"
	 * 	),
	 * 	@SWG\Response(
	 * 		response="200",
	 * 		description="Blog Category array",
	 * 		@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/BlogCategory"))
	 * 	)
	 * )
	 */
	public function categories_get()
	{
		$this->load->model('blog_category_model', 'categories');
		$data = $this->categories->get_all();
		$this->response($data);
	}

	/**
	 * @SWG\Get(
	 * 	path="/blog/tags",
	 * 	tags={"blog"},
	 * 	summary="List out blog tags",
	 * 	@SWG\Parameter(
	 * 		in="header",
	 * 		name="X-API-KEY",
	 * 		description="API Key",
	 * 		required=true,
	 * 		type="string"
	 * 	),
	 * 	@SWG\Response(
	 * 		response="200",
	 * 		description="Blog Tag array",
	 * 		@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/BlogTag"))
	 * 	)
	 * )
	 */
	public function tags_get()
	{
		$this->load->model('blog_tag_model', 'tags');
		$data = $this->tags->get_all();
		$this->response($data);
	}
}
