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
class Events_Shop_Images
{

	protected $ci;

	public $mod_details = array(
			      'name'=> 'Images', //Label of the module
			      'namespace'=>'shop_images',
			      'product-tab'=> true, //This is to tell the core that we want a tab
			      'prod_tab_order'=> 25, //This is to tell the core that we want a tab
			      'cart'=> FALSE,
			      'has_admin'=> FALSE,
	);

	/**
	 * Singleton
	 * @param  [type] $var [description]
	 * @return [type]      [description]
	 */
	public function __get($var)
	{
		if (isset(get_instance()->$var))
		{
			return get_instance()->$var;
		}
	}


	/**
	 * Initialize the SHOP_Images events
	 */
	public function __construct()
	{
		Events::register('SHOPEVT_AdminProductGet', array($this, 'shopevt_admin_product_get'));
		Events::register('SHOPEVT_AdminProductDelete', array($this, 'shopevt_admin_product_delete'));
		Events::register('SHOPEVT_AdminProductDuplicate', array($this, 'shopevt_admin_product_duplicate'));
	}

	/**
	 * This will be fired as soon as a product has been deleted
	 *
	 * @param  [type] $deleted_product_id [description]
	 * @return [type]                     [description]
	 */
	public function shopevt_admin_product_delete($deleted_product_id)
	{
		$this->load->model('shop_images/images_m');

		$this->images_m->delete_product( $deleted_product_id );

	}

	/**
	 * This is fired as soon as a product is duplicated
	 *
	 * @param  array  $duplicateData [description]
	 * @return [type]                [description]
	 */
	public function shopevt_admin_product_duplicate($duplicateData = array())
	{
		$or_id  = $duplicateData['OriginalProduct'];
		$new_id = $duplicateData['NewProduct'];

		$this->load->model('shop_images/images_m');

		$this->images_m->duplicate( $or_id ,$new_id );

	}


	/**
	 * This will be called when the admin product data has been requested.
	 * It will inform all other modules to fetch any data that may be associated
	 * The ID of the product is passed (always by ID and Never by SLUG)
	 *
	 * @param  [type] $product [description]
	 * @return [type]          [description]
	 */
	public function shopevt_admin_product_get($product)
	{
		// Send data back
		$this->load->model('shop_images/images_m','shop_images_m');


		$product->modules['shop_images'] = $this->shop_images_m->get_images( $product->id );
		$product->module_tabs[] = (object) $this->mod_details;

		// Load assets
		//load the js lib (this will be loaded from shop core js folder)
		$this->template->append_js('shop::admin/plugins/dropzone.js');
		$this->template->append_js('shop::admin/plugins/jquery.Jcrop.min.js');
		$this->template->append_css('shop::admin/jcrop/jquery.Jcrop.min.css');

	}

}
/* End of file events.php */