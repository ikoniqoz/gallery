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
class Image_processing_library
{

	protected $jpeg_quality = 90;
	protected $png_quality = 9;


	public function __construct()
	{
		log_message('debug', "Class Initialized");
	}

	/**
	 * Get the CI instance into this object
	 *
	 * @param unknown_type $var
	 */
	public function __get($var)
	{
		if (isset(get_instance()->$var))
		{
			return get_instance()->$var;
		}
	}

	public function uninstall_module()
	{
		if($this->db->table_exists('shop_images'))
		{
			$this->load->model('shop_images/images_m');
			$this->load->library('files/files');

			$images = $this->images_m->get_all();
			foreach($images as $image)
			{
				$ar = Files::delete_file($image->file_id);
			}
		}
	}



	/**
	 * @return the $product
	 */
	public function process($file_id, $input)
	{

		// Load Lib
		$this->load->model('shop_images/shop_image_files_m');


		// Get the image data
		$image = $this->shop_image_files_m->get($file_id);


		$image_type = $image->extension;
		if($image_type == '.jpeg') $image_type = '.jpg';
		//var_dump($image);die;

		//we need to get this path somehow
		$src = FCPATH . "/uploads/default/files/".$image->filename;


		$targ_w = $targ_h = 150;  //what is this ?? is it the defaults ?



		if(!list($targ_w, $targ_h) = getimagesize($src)) return "Unsupported picture type!";


		// Create the sample
		$img_r = $this->createFrom($image_type, $src);



		//TrueColor
		//$src = imagecreatetruecolor($targ_w, $targ_h);


		// preserve transparency
		//$src = $this->preseveTrans($image_type, $src);


		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );



		imagecopyresampled($dst_r,$img_r,0,0,$input['x'],$input['y'], $targ_w, $targ_h, $input['w'], $input['h'] );



		//Process and save image
		$this->exportImage($image_type, $dst_r, $src);



		//clear cache
		$this->pyrocache->delete_all('image_files');

	}

	private function preseveTrans($image_type, $src)
	{
		if($image_type == ".gif" or $image_type == ".png")
		{
			imagecolortransparent($src, imagecolorallocatealpha($src, 0, 0, 0, 127));
			imagealphablending($src, false);
			imagesavealpha($src, true);
		}

		return $src;

	}

	private function createFrom($image_type, $src)
	{
		$img_r = NULL;

		switch($image_type)
		{
			case '.bmp': $img_r = imagecreatefromwbmp($src); break;
			case '.gif': $img_r = imagecreatefromgif($src); break;
			case '.jpg': $img_r = imagecreatefromjpeg($src); break;
			case '.png': $img_r = imagecreatefrompng($src); break;
			default : return "Unsupported picture type!";
		}

		return$img_r;
	}

	private function exportImage($image_type, $dst_r, $src)
	{
		switch($image_type)
		{
			case '.bmp': imagewbmp($dst_r, $src); break;
			case '.gif': imagegif($dst_r, $src); break;
			case '.jpg': imagejpeg($dst_r, $src, $this->jpeg_quality); break;
			case '.png': imagepng($dst_r, $src, $this->png_quality); break;
		}
	}


	private function reduceImageSize()
	{
		//http://www.apptha.com/blog/how-to-reduce-image-file-size-while-uploading-using-php-code/
	}







}
