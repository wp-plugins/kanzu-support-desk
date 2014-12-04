<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @file      class-ksd-controller.php
 */

$plugindir = plugin_dir_path( __FILE__ );

class KSD_Controller 
{
	protected $_model = null;
	protected $_model_name = null;
	
	/*
	* Load model class if provided
	*/
	public function __construct(){
		if( $this->_model_name != ""){			
			include_once( KSD_PLUGIN_DIR. "includes/models/class-ksd-" . strtolower( $this->_model_name ) . "-model.php" );
			$classname = "KSD_".$this->_model_name . "_Model";
			$this->_model = new $classname();
		}
	}
}
?>
