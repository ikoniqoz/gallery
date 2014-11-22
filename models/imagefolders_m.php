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
class Imagefolders_m extends MY_Model {


	public $_table = 'file_folders';

	public function __construct()
	{
		parent::__construct();
	}


	public function create()
	{

		$to_insert = array(
				'parent_id' => 0,
				'slug' => 'shop_product_images', //generate_slug()
				'name' => 'ProductImages',
				'location' => 'local',
				'remote_container' => '',
				'date_added' => now(),
				'sort' => now(), //will implement the ordering in later version
				'hidden' => 1,
		);


		return $this->insert($to_insert); //returns id

	}


	public function get_available()
	{
		return  $this->db->where('parent_id',0)->where('slug','shop_product_images')->where('name','ProductImages')->where('hidden',1)->get('file_folders')->row();
	}


}