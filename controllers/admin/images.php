<?php if (!defined('BASEPATH'))  exit('No direct script access allowed');
/**
 * SHOP			A full featured shopping cart system for PyroCMS
 *
 * @author		Salvatore Bordonaro
 * @version		1.0.0.051
 * @website		http://www.inspiredgroup.com.au/
 * @system		PyroCMS 2.1.x
 *
 */
class Images extends Admin_Controller
{
	/*
	 * Images does not have an admin view (only product tab admin view)
	 * So we dont really need the section property
	 *
	 * @var string
	 */
	//protected $section = 'products';
	private $data;


	public function __construct()
	{
		parent::__construct();
	}

	public function index(){}


	/**
	 * Upload images from the images tab
	 * site_url/admin/shop_images/images/upload/xxx
	 *
	 * @return [INT] [ID of the image uploaded]
	 */
	public function upload($product_id)
	{
		// Load libs
		$this->load->library('files/files');
		$this->load->model('files/file_folders_m');
		$this->load->model('shop_images/images_m');
		$this->load->model('shop_images/imagefolders_m');

		$assign_cover = FALSE;
		if(!$this->images_m->get_cover($product_id))
		{
			$assign_cover = (int) Settings::get('shop_auto_assign_cover');
		}


		$folder_id = (int) Settings::get('shop_images_products');


		//Check if exist
		$exists = $this->file_folders_m->exists($folder_id);

		if( ! $exists )
		{
			//if the id doesnt exist lets see if we can use a file folder that is available

			if( $this->imagefolders_m->get_available() )
			{
				$folder_id_a = $this->imagefolders_m->get_available();
				$folder_id = $folder_id_a->id;
			}
			else
			{
				$folder_id = $this->imagefolders_m->create();
			}

			//Assign the folder id
			Settings::set('shop_images_products',$folder_id);
		}




		//upload each image
		foreach($_FILES as $key => $_file)
		{
			//uploads images to files module
			$upload = Files::upload($folder_id, $_file['name'], $key );

			// Get the Image ID
	    	$image_id = $upload['data']['id'];


	    	// Assign the image to this product
			//$row_id = $this->images_m->add_local_image( $image_id, $product_id, $assign_cover);
			$row_id = $this->images_m->add_local_image2( $upload['data'], $product_id, $assign_cover);
			//only handle 1 at  a time, the uploader will create multiple ques
			echo json_encode(
					array(
						'status' =>'completed',
						'file_id' => $image_id,
						'row_id' => $row_id
						)
					);die;
		}

		die('Could not find any suitable image to upload.');


	}


	public function set_as_cover( $product_id=0, $image_id=0 )
	{
		$return_array = $this->getAjaxReturnObject();

		$this->load->model('shop_images/images_m');

		if($this->images_m->set_as_cover($product_id, $image_id))
		{
			$return_array['status'] = 'success';
		}

		$this->sendAjaxReturnObject($return_array);

	}

	/**
	 * removes a image from a product (ref) only
	 *
	 *
	 */
	public function remove($row_id, $file_id)
	{
		$return_array = $this->getAjaxReturnObject();

		$this->load->model('shop_images/images_m');


		if($this->images_m->delete($row_id))
		{
			//before we delete the physical file, lets make sure no other product is using it.
			if($this->images_m->isUsed($file_id))
			{

			}
			else
			{
				$this->load->library('files/files');
				$ar = Files::delete_file($file_id);
			}

			$return_array['status'] = 'success';
		}


		$this->sendAjaxReturnObject($return_array);
	}



	public function preview($file_id)
	{

		//die("<img id='cropbox'  src='".site_url()."files/thumb/".$file_id."/300'>");
		die("<div style='padding:10px;margin:10px;'><img id=''  src='".site_url()."files/thumb/".$file_id."/300' height='300'></div>");

	}
	/*
	public function preview($file_id)
	{

		$str ='<form action="admin/shop_images/images/crop/'.$file_id.'" method="post" onsubmit="return checkCoords();">';
		$str .='<input type="hidden" id="x" name="x" />';
		$str .='<input type="hidden" id="y" name="y" />';
		$str .='<input type="hidden" id="w" name="w" />';
		$str .='<input type="hidden" id="h" name="h" />';
		$str .='<input type="submit" value="Crop Image" class="button red" />';
		$str .='<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
		$str .='</form>';
		$str .= '<script>$("#cropbox").Jcrop({aspectRatio: 1, onSelect: updateCoords });</script>';

		die("<img id='cropbox' height='400' src='".site_url()."files/thumb/".$file_id."//400'>".$str);

	}
	*/



	public function crop($file_id)
	{
		$url_redir = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'shop/';

		if(! $this->input->post())
		{
			$input = $this->input->post();

			$this->load->library('shop_images/image_processing_library');

			$this->image_processing_library->process($file_id, $input);
		}

		redirect($url_redir);

	}

	private function getAjaxReturnObject()
	{
		$ret_array = array();
		$ret_array['status'] = 'error';
		$ret_array['message'] = '';

		return $ret_array;
	}

	private function sendAjaxReturnObject($array_object)
	{
		echo json_encode($array_object);die;
	}


}