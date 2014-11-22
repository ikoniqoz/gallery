<?php if (!defined('BASEPATH'))  exit('No direct script access allowed');
/**
 * SHOP         A full featured shopping cart system for PyroCMS
 *
 * @author      Salvatore Bordonaro
 * @version     1.0.0.051
 * @website     http://www.inspiredgroup.com.au/
 * @system      PyroCMS 2.2.x
 *
 */
class Module_Shop_Images extends Module
{

    /**
     * New dev version uses YMD as the final decimal format.
     * Only for dev builds
     *
     * @var string
     */
    public $version = '2.2.1';

    public $mod_details = array(
                    'name'=> 'Images', //Label of the module
                    'namespace'=>'shop_images',
                    'product-tab'=> true, //This is to tell the core that we want a tab
                    'prod_tab_order'=> 2, //This is to tell the core that we want a tab
                    'cart'=> FALSE,
                    'has_admin'=> FALSE,

    );

    //List of tables used
    protected $module_tables = array(

            'shop_images' => array(
                'id'            => array('type' => 'INT', 'constraint' => '11', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'primary' => TRUE),
                'product_id'    => array('type' => 'INT', 'constraint' => '11', 'null' => TRUE, 'unsigned' => TRUE),
                'width'         => array('type' => 'INT', 'constraint' => '11', 'unsigned' => TRUE, 'null' => TRUE, 'default' => NULL),
                'height'        => array('type' => 'INT', 'constraint' => '11', 'unsigned' => TRUE, 'null' => TRUE, 'default' => NULL),
                'src'           => array('type' => 'VARCHAR', 'constraint' => '500', 'null' => TRUE, 'default' => NULL ),
                'alt'           => array('type' => 'VARCHAR', 'constraint' => '500', 'null' => TRUE, 'default' => NULL ),
                'order'         => array('type' => 'INT', 'constraint' => '4', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0),
                'cover'         => array('type' => 'INT', 'constraint' => '1', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0),
                'local'         => array('type' => 'INT', 'constraint' => '1', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 1),
                'file_id'       => array('type' => 'CHAR', 'constraint' => 15),
                'filename'      => array('type' => 'VARCHAR', 'constraint' => '500',    'null' => TRUE, 'default' => NULL ),
                'thumb'         => array('type' => 'VARCHAR', 'constraint' => '1000',   'null' => TRUE, 'default' => NULL ),
                'path'          => array('type' => 'VARCHAR', 'constraint' => '1000',   'null' => TRUE, 'default' => NULL ),

            ),
    );


    public function __construct()
    {
        $this->load->library('shop/nitrocore_library');    
        $this->ci = get_instance();
    }


    /**
     * info()
     * @description: Creates 2 arrays to diplay for the module naviagtion
     *             One array is returned based on the user selection in the settings
     *
     */
    public function info()
    {

        $info =  array(
            'name' => array(
                'en' => 'NitroCart Images',
            ),
            'description' => array(
                'en' => 'NitroCart <i>A full featured shopping cart system for PyroCMS!</i>',
            ),
            'skip_xss' => TRUE,
            'frontend' => TRUE,
            'backend' => TRUE,
            'menu' => FALSE,
            'author' => 'Salvatore Bordonaro',
            'roles' => array(
                'admin_manage'
                ),
            'sections' => array()
        );


        // Support for sub 2.2.0 menus
        if ( CMS_VERSION < '2.2.0' ) {
            $info['is_backend'] = TRUE;
            $info['menu']       = 'SHOP Images';
        }


        return $info;

    }


    /*
     * The menu is handled by the main SHOP module
     */
    public function admin_menu(&$menu)
    {

    }


    public function install()
    {

        if(!$this->isRequiredInstalled())
        {
            return FALSE;
        }

        $tables_installed = $this->install_tables( $this->module_tables );

        if( $tables_installed  )
        {
            if($this->install_settings())
            {
                //register this module with SHOP
                Events::trigger("SHOPEVT_RegisterModule", $this->mod_details);
                return TRUE;
            }
        }

        return FALSE;

    }

    private function install_settings()
    {

        $settings = array(

            'shop_images_products' => array( /*distribution location ISO 2 letter country code*//*http://www.iso.org/iso/country_codes.htm*/
                'title' => 'Upload folder id for product images ',
                'description' => 'Assign a folder to upload product images',
                'type' => 'text',
                'default' => '0',
                'value' =>  '0',
                'options' => '',
                'is_required' => TRUE,
                'is_gui' => FALSE,
                'module' => 'shop_images', //dont use shop - it will expose it and we need this protected
                'order' => 100
            ),
            'shop_auto_assign_cover' => array( /*distribution location ISO 2 letter country code*//*http://www.iso.org/iso/country_codes.htm*/
                'title' => 'Automatically assign a cover image to a product when no Cover is set',
                'description' => 'When uploading an image to a product, if set to YES the module will automatically assign the first uploaded image to the product.',
                'type' => 'select',
                'default' => '0',
                'value' =>  '0',
                'options' => '0=No thanks|1=Yes please',
                'is_required' => TRUE,
                'is_gui' => FALSE,
                'module' => 'shop', //dont use shop - it will expose it and we need this protected
                'order' => 100
            ),
        );

        foreach ($settings as $slug => $setting)
        {
            //set the settings name
            $setting['slug'] = $slug;

            if (!$this->db->insert('settings', $setting))
            {
                return FALSE;
            }
        }

        return TRUE;

    }

    /*
     */
    public function uninstall()
    {

        $this->ci->load->library('shop_images/image_processing_library');

        $this->image_processing_library->uninstall_module();


            foreach($this->module_tables as $table_name => $table_data)
            {
                $this->dbforge->drop_table($table_name);
            }


        // Remove All settings for this module
        $this->db->delete('settings', array('module' => 'shop_images'));

        $this->db->delete('settings', array('slug' => 'shop_auto_assign_cover'));

        //Remove categories from the core module DB
        Events::trigger("SHOPEVT_DeRegisterModule", $this->mod_details);

        return TRUE;

    }



    /*
     */
    public function upgrade($old_version)
    {

        switch ($old_version)
        {
            case '1.0.1':
                break;
            default:
                break;

        }


        return TRUE;

    }


    public function help()
    {
        return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
    }



    private function init_templates()
    {
         return TRUE;
    }



    private function init_settings()
    {
        return TRUE;
    }
    public function isRequiredInstalled()
    {

        $this->ci->load->model('module/module_m');
        $module_core = $this->ci->module_m->get_by('slug', 'shop' );

        if( $module_core && $module_core->installed == TRUE)
        {
            $module = $this->ci->module_m->get_by('slug', 'shop' );
            if( $module && $module->installed == TRUE)
            {
                //we can now install this shop module
                return TRUE;
            }
        }

        return FALSE;
    }

}
/* End of file details.php */