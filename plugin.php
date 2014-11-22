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
class Plugin_Shop_Images extends Plugin
{
	public $version = '1.0.0';
	public $name = array(
		'en' => 'NitroCart Images',
	);
	public $description = array(
		'en' => 'Access user and cart information for almost any part of SHOP.',
	);


	public function _self_doc()
	{
		$info = array(
			'images' => array(
				'description' => array(
					'en' => 'Display Gallery and cover images of products.'
				),
				'single' => false,
				'double' => true,
				'variables' => 'id|src|alt|height|width|file_id|local',
				'attributes' => array(
					'id' => array(
						'type' => 'int',
						'required' => true,
					),
					'max' => array(
						'type' => 'Integer',
						'default' => '0',
						'required' => false,
					),
					'include_cover' => array(
						'type' => 'Boolean',
						'default' => 'NO',
						'required' => false,
					),
					'include_gallery' => array(
						'type' => 'Boolean',
						'default' => 'YES',
						'required' => false,
					),
				),
			),




		);

		return $info;
	}


	/**
	 * Get all images related to products in the system. Careful now this could retrieve a lot of images
	 */
	function all()
	{
		if($this->db->table_exists('shop_images'))
		{
			$x = explode(',' , $this->attribute('x', '') );
			$limit = intval($this->attribute('max', '0'));
			$offset = intval($this->attribute('offset', '0'));

			$this->load->model('shop_images/images_m');

			($limit == '0') OR $this->images_m->limit( $limit );
			($offset == '0') OR $this->images_m->offset( $offset );

			$images = $this->images_m->get_all();

			//(in_array('PAGINATE', $x ));

			$ret = array();
			$ret[] = array( 'images'=> $images , 'count' => count($images) );
			//return results of images
			return $ret;
		}
		return '';
	}



	/**
	 * Get images for a product
	 *
	 * id=INT <required>
	 * x="RANDOMIZE,COVER,COUNT"
	 * max=INT
	 */
	function product()
	{
		if($this->db->table_exists('shop_images'))
		{
			$x = explode(',' , $this->attribute('x', '') );
			$id = $this->attribute('id', '0');
			$limit = intval($this->attribute('max', '0'));

			$this->load->model('shop_images/images_m');

			($limit == '0') OR $this->images_m->limit( $limit ) ;
			$this->db->where('product_id',$id)->where('cover',0);
			if(in_array('COVER', $x )) $this->db->or_where('product_id',$id)->where('cover',1);

			$images = $this->images_m->get_all();

			if(in_array('RANDOMIZE', $x )) shuffle( $images );
			$ret = array(); $ret[] = array( 'images'=> $images , 'count' => (in_array('COUNT', $x ) ? count($images) : '' ));

			return $ret;
		}
		return '';
	}

	/**
	 * Quick access to product cover
	 */
	function cover()
	{
		if($this->db->table_exists('shop_images'))
		{
			$id = intval($this->attribute('product_id', '0'));
			
			$height = $this->attribute('height', '');			
			$width = $this->attribute('width', '');		

			$this->load->model('shop_images/images_m');

			$results = $this->images_m->select('id')->where('product_id',$id)->where('cover',1)->get_all();
			foreach($results as $result)
			{
				return (array) $this->images_m->get( $result->id );
			}
			return (array) '' ;
		}
		return '';
	}

	/**
	 * x= "SRC|THUMB|PATH"
	 * @return [type] [description]
	 */
	function htmlcover()
	{
		$html = '';

		if($this->db->table_exists('shop_images'))
		{
			$id = intval($this->attribute('product', '0'));
			$width = $this->attribute('width', 'auto');
			$x = explode(',' , $this->attribute('x', 'PATH') );
			//$height = intval($this->attribute('height', 'DYN'));
			//$height = ($height=='DYN')? '' : " height='{$height}' ";
			$result = $this->db->where('product_id',$id)->where('cover',1)->get('shop_images')->row();

			if($result)
			{
				//pre check thumb for default height width
				$width = (in_array('THUMB', $x ) && ($width =='auto'))?  100 : $width ;


				//Width
				$width_att = (is_numeric($width) && $width > 0)? "width='{$width}px'" : '';
				$width_uri = (is_numeric($width) && $width > 0)? $width : '';

				// Height
				$height_uri = 'auto';

				// we also have fit/fill to use.
				//$fitfill = (in_array('FIT', $x )) ?  'fit' : '' ;
				//$fitfill = (in_array('FILL', $x )) ?  'fill' : $fitfill ;
				$fitfill = '';//0;


				$use_attribute = $result->path ;
				$use_attribute = (in_array('SRC', $x )) ?  $result->src : $use_attribute ;
				$use_attribute = (in_array('PATH', $x )) ?  $result->path : $use_attribute ;
				$use_attribute = (in_array('THUMB', $x )) ?  $result->thumb : $use_attribute ;
				//
				$html .= "<img src='{$use_attribute}/{$width_uri}/{$height_uri}/{$fitfill}' {$width_att} alt='{$result->alt}' />"  ;
			}

		}

		return $html;
	}
}
/* End of file plugin.php */