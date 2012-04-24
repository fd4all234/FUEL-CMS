<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the 
 * Codeigniter framework (http://codeigniter.com)
 *
 * @package		FUEL CMS
 * @author		David McReynolds @ Daylight Studio
 * @copyright	Copyright (c) 2012, Run for Daylight LLC.
 * @license		http://www.getfuelcms.com/user_guide/general/license
 * @link		http://www.getfuelcms.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * FUEL settings object
 *
 * @package		FUEL CMS
 * @subpackage	Libraries
 * @category	Libraries
 * @author		David McReynolds @ Daylight Studio
 * @link		http://www.getfuelcms.com/user_guide/libraries/fuel_sitevariables
 */

// --------------------------------------------------------------------

class Fuel_settings extends Fuel_base_library {

	protected $settings = array(); // Settings array
	
	function __construct($params = array())
	{
		parent::__construct($params);
		$this->fuel->load_model('settings', 'fuel_settings_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Returns an array of settings related to a particular module
	 *
	 * @access public
	 * @param string $module, Module name
	 * @param string $key, Key name
	 * @return array
	 */
	function get($module, $key = '')
	{
		if ( ! array_key_exists($module, $this->settings))
		{
			$this->settings[$module] = $this->CI->fuel_settings_model->options_list('fuel_settings.key', 'fuel_settings.value', array('module' => $module), 'key');
			foreach($this->settings[$module] as $k => $v)
			{
				$this->settings[$module][$k] = $this->CI->fuel_settings_model->unserialize_value($this->settings[$module][$k]);
			}

			//$this->settings[$module] = $this->CI->fuel_settings_model->fin_all_array_assoc('fuel_settings.key', array('module' => $module), 'key');
		}
		if ( ! empty($key) AND array_key_exists($key, $this->settings[$module]))
		{
			return $this->settings[$module][$key];
		}
		else
		{
			return $this->settings[$module];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Process the new settings & save it to fuel_settings
	 *
	 * @access public
	 * @param string $module, Module name
	 * @param array $settings, An array containing the defined settings
	 * @param array $new_settings, An array containing the new settings
	 * @param boolean $skip_empty_vals, Store empty vals
	 * @return boolean
	 */
	function process($module, $settings, $new_settings, $skip_empty_vals = FALSE)
	{
		/* if (isset($module, $settings, $new_settings)) */
		if ( ! empty($new_settings) AND ! empty($module) AND ! empty($settings))
		{
			// backup old settings
			$this->CI->fuel_settings_model->update(array('module' => "{$module}_backup"), array('module' => $module));
			
			// format data for saving
			$save = array();
			foreach ($settings as $key => $field_config)
			{
				$new_value = '';
				// set checkbox settings to 0 by default if unchecked
				if (array_key_exists('type', $field_config) AND ($field_config['type'] == 'checkbox') AND ! array_key_exists($key, $new_settings))
				{
					$new_value = 0;
				}
				else if (isset($new_settings[$key]))
				{
					//$new_value = trim($new_settings[$key]);
					$new_value = $new_settings[$key];
					if ($skip_empty_vals AND empty($new_value))
					{
						continue;
					}
				}
				$save[] = array(
					'module' => $module,
					'key'    => $key,
					'value'  => $new_value,
					);
			}
			if ( ! empty($save))
			{
				$this->CI->fuel_settings_model->save($save);
				
				// clear out old settings
				$this->CI->fuel_settings_model->delete(array('module' => "{$module}_backup"));
			
				return TRUE;
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	function get_validation()
	{
		$validation = &$this->CI->fuel_settings_model->get_validation();
		return $validation;
	}

}

/* End of file Fuel_settings.php */
/* Location: ./modules/fuel/libraries/Fuel_settings.php */