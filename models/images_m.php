<?php if (!defined('BASEPATH'))  exit('No direct script access allowed');
/**
 * SHOP         A full featured shopping cart system for PyroCMS
 *
 * @author      Salvatore Bordonaro
 * @version     1.0.0.051
 * @website     http://www.inspiredgroup.com.au/
 * @system      PyroCMS 2.1.x
 *
 */
class Images_m extends MY_Model {


    public $_table = 'shop_images';

    public function __construct()
    {
        parent::__construct();
    }


    public function delete_product( $product_id )
    {

        $items_to_delete = $this->where('product_id',$product_id)->get_all();

        foreach($items_to_delete AS $image)
        {
            $this->delete($image->id);
        }

        return TRUE;
    }




    public function duplicate( $or_id ,$new_id )
    {
        //fetch all rows where prod id = $or_id
        $original_product_images = $this->where('product_id',$or_id)->get_all();

        foreach($original_product_images AS $image)
        {
            //create the input
            $to_insert = array(
                    'product_id'    => $new_id ,
                    'width'         => $image->width,
                    'height'        => $image->height,
                    'src'           => $image->src,
                    'filename'      => $image->filename,
                    'path'          => $image->path,
                    'alt'           => $image->alt ,
                    'order'         => $image->order,
                    'cover'         => $image->cover ,
                    'file_id'       => $image->file_id,
                    'thumb'         => $image->thumb,
                    'local'         => $image->local,
            );

            //Add record
            $this->insert($to_insert); //returns id

        }

        return TRUE;
    }


    public function save($image)
    {

        $update_record = array(
            'src' => $image->src,
            'alt' => $image->alt,
        );

        return $this->update($image->id, $update_record);

    }


    public function set_as_cover($product_id, $id)
    {

        if($this->where('product_id',$product_id)->update_all(array('cover'=>0)))
        {
            $update_record = array(
                'cover' => 1,
            );

            if($this->update($id, $update_record))
            {
                return TRUE;
            }
        }

        return FALSE;

    }


    public function remove_product_image_reference($product_id,$image_id)
    {
        return $this->where('product_id',$product_id)->where('file_id',$image_id)->delete($this->$_table);
    }

    public function add_local_image2($image_data, $product_id, $cover_image = 0)
    {

        $to_insert = array(
                'alt'       => $image_id,
                'product_id'=> $product_id,
                'width'     => $image_data['width'],
                'height'    => $image_data['height'],
                'order'     => 10, //will implement the ordering in later version
                'cover'     => $cover_image,
                'local'     => 1,
                'file_id'   => $image_data['id'],
                'filename'  => $image_data['filename'],
                'src'       => '{{url:site}}files/thumb/'.$image_data['id'],
                'thumb'     => '{{url:site}}files/thumb/'.$image_data['filename'],
                'path'      => $image_data['path'],
        );

        $i_id = $this->insert($to_insert); //returns id

        if($i_id)
        {
            //do we do this? should we ??
            $this->db->where('id', $product_id);
            $this->db->update('shop_products', array('updated' => date("Y-m-d H:i:s") )  );
            return $i_id;
        }

        return FALSE;

    }
    /**
     * Add image via URL
     *
     */
    public function add_url_image($url, $product_id)
    {

        $to_insert = array(
                'src'               => $url,
                'alt'               => '',
                'product_id'        => $product_id,
                'width'             => 0,
                'height'            => 0,
                'order'             => 10, //will implement the ordering in later version
                'cover'             => 0,
                'local'             => 0,
                'file_id'           => '',
                'filename'          => '',
                'path'              => '',
                'thumb'             => '',

        );

        $i_id = $this->insert($to_insert); //returns id


        if($i_id)
        {

            $this->db->where('id', $product_id);
            $this->db->update('shop_products', array('updated' => date("Y-m-d H:i:s") )  );

            return $i_id;
        }

    }

    /**
     * check to see if a file image is used
     */
    public function isUsed($file_id)
    {
        $result = $this->limit(1)->where('file_id',$file_id)->get_all();

        if (count($result))
            return TRUE;

        return FALSE;
    }

    /**
     * Check if Image Exist
     * @param  [type]  $image_id   [description]
     * @param  integer $product_id The product_id is optional if you want to see if the image exist and is linked to a product
     * @return [type]              [description]
     */
    public function image_exist( $image_id , $product_id = 0, $local = TRUE)
    {

        // Do we rtest using check by local image - this will attempt to check by the
        if( $local )
        {

            $result = $this->limit(1)->where('product_id',$product_id)->where('local',1)->where('file_id',$image_id)->get_all();

            if (count($result))
                return TRUE;

            return FALSE;

        }

        return FALSE;

    }



    /**
     * Shared
     * @param INT $id Product ID
     * @return unknown
     */
    public function get_images($product_id)
    {
        return $this->where('product_id',$product_id)->get_all();
    }

    public function get_cover($product_id)
    {

        $result = $this->db->where('product_id',$product_id)->where('cover',1)->get($this->_table)->row();

        return ($result) ? $result : FALSE;

    }
    /*
    public function get_cover($product_id)
    {

        $results = $this->limit(1)->where('product_id',$product_id)->where('cover',1)->get_all();

        foreach($results as $result)
        {
            return $result;
        }

        return FALSE;
    }
    */
}