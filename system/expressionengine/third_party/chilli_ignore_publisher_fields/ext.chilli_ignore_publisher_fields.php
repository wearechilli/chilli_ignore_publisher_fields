<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package     ExpressionEngine
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license     http://expressionengine.com/user_guide/license.html
 * @link        http://expressionengine.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Cover and disable ignored publisher fields
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Extension
 * @author      Gaetan Lafaut
 * @link        http://www.chilli.be
 */

class Chilli_ignore_publisher_fields_ext
{
    public $settings        = array();
    public $description     = 'Cover and disable ignored publisher fields';
    public $docs_url        = 'http://www.chilli.be';
    public $name            = 'Chilli ignore publisher fields';
    public $settings_exist  = 'n';
    public $version         = '1.0';

    public $script          = '';

    private $EE;

    /**
     * Constructor
     *
     * @param mixed Settings array or empty string if none exist.
     * @return void
     */
    public function __construct( $settings = '' )
    {
        $this->EE       = &get_instance();
        $this->settings = $settings;
    }

    // ----------------------------------------------------------------------

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        // Setup custom settings in this array.
        $this->settings = array();

        $this->EE->db->insert( 'extensions', array(
             'class'    => __CLASS__
            ,'method'   => 'cp_menu_array'
            ,'hook'     => 'cp_menu_array'
            ,'settings' => serialize( $this->settings )
            ,'version'  => $this->version
            ,'enabled'  => 'y'
        ) );
    }

	// ----------------------------------------------------------------------

    /**
     * cp_menu_array
     *
     * @return void
     */
     public function cp_menu_array($menu)
    {

         //check to see if Publisher is an installed add-on
        ee()->db->select( 'module_id' );
        ee()->db->where( 'module_name', 'Publisher' );

        $q = $this->EE->db->get( 'modules' );

        $publisher_installed = ( $q->num_rows() > 0 );

        $q->free_result();

        if ( $publisher_installed )
        {

            if ( !ee()->publisher_language->is_default_language() )
            {
                ee()->load->model('publisher_model');
                
                // get all the ignorde fields and re-arrange them like field_id_xx
                $fields =  ee()->publisher_setting->ignored_fields(1);
                foreach( $fields as $key => $field )
                {
                    $fields[$key] = "field_id_" . $field;
                }
                $encoded_fields = "";
                $encoded_fields = json_encode( $fields );
                $script = "
                    var fields = ".$encoded_fields.";
                    jQuery.each( fields, function( i, val ) {

                        name_val = 'name='+ val ;

                        element_by_id = $( '#' + val  ) ;
                        element_by_name = $('['+ name_val +']') ;

                        if ( element_by_id.length != 0 )
                        {
                            Publisher.add_field_cover (  element_by_id  );
                            element_by_id.prop( 'readonly', 'readonly' );
                        }
                        else if ( element_by_name !== false )
                        {
                            Publisher.add_field_cover (  element_by_name );
                        }

                    });
                ";

                ee()->javascript->output( $script );

            }
        }
        if (ee()->extensions->last_call !== FALSE)
        {
            $menu = ee()->extensions->last_call;
        }

		return  $menu;
    }

    // ----------------------------------------------------------------------

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {
        $this->EE->db->where( 'class', __CLASS__ );
        $this->EE->db->delete( 'extensions' );
    }

    // ----------------------------------------------------------------------

    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return mixed void on update / false if none
     */
    function update_extension( $current = '' )
    {
        if ( $current == '' OR $current == $this->version )
        {
            return FALSE;
        }
    }

    // ----------------------------------------------------------------------
}

