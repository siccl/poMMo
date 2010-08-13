<?php
/**
 * Copyright (C) 2005, 2006, 2007, 2008  Brice Burgess <bhb@iceburg.net>
 * 
 * This file is part of poMMo (http://www.pommo.org)
 * 
 * poMMo is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published 
 * by the Free Software Foundation; either version 2, or any later version.
 * 
 * poMMo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
 * the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with program; see the file docs/LICENSE. If not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 */
// include smarty template class
require_once(Pommo::$_baseDir.'lib/smarty/Smarty.class.php');

// wrapper class around smarty
class Pommo_Template extends Smarty
{

	var $_pommoTheme;

	function Pommo_Template()
	{
		// set theme -- TODO; extend this to the theme selector
		$this->_pommoTheme = 'default';

		// set smarty directories
		$this->_themeDir = Pommo::$_baseDir . 'themes/';
		$this->template_dir = $this->_themeDir . $this->_pommoTheme;
		$this->config_dir = $this->template_dir . '/inc/config';
		$this->cache_dir = Pommo::$_workDir . '/pommo/smarty';
		$this->compile_dir = Pommo::$_workDir . '/pommo/smarty';
		$this->plugins_dir = array (
				'plugins', // the default under SMARTY_DIR
				Pommo::$_baseDir . 'lib/smarty-plugins/gettext',
				Pommo::$_baseDir . 'lib/smarty-plugins/pommo');
				
		// set base/core variables available to all template
		$this->assign('url', array (
			'theme' => array (
				'shared' => Pommo::$_baseUrl . 'themes/shared/',
				'this' => Pommo::$_baseUrl . 'themes/' . $this->_pommoTheme . '/'
			),
			'base' => Pommo::$_baseUrl,
			'http' => Pommo::$_http
		));
		$this->assign('config', @array (
			'app' => array (
				'path' => Pommo::$_baseDir,
				'weblink' => '<a href="http://pommo.sourceforge.net/">'.
				Pommo::_T('poMMo Website') . '</a>',
				'dateformat' => Pommo_Helper::timeGetFormat()
				),
			'site_name' => Pommo::$_config['site_name'],
			'site_url' => Pommo::$_config['site_url'], 
			'list_name' => Pommo::$_config['list_name'],
			'admin_email' => Pommo::$_config['admin_email'],
			'demo_mode' => Pommo::$_config['demo_mode']));

		// set gettext overload functions (see block.t.php...)
		$this->_gettext_func = array('Pommo','_T'); // calls Pommo::_T($str)
		$this->_gettext_plural_func = array('Pommo','_TP');

		// assign page title
		$this->assign('title', '. ..poMMo.. .');

		// assign section (used for sidebar template)
		$this->assign('section', Pommo::$_section);
			
		}

	/*	display
	 *	falls back to "default" theme if theme file not found
	 *	also assigns any poMMo errors or messages
	 *
	 *	@param	string	$resource_name.- Template to load
	 *	@param	
	 *
	 *	@return	boolean	True on success
	 */
	function display($resource_name)
	{
		// attempt to load the theme's requested template
		if (!is_file($this->template_dir.'/'.$resource_name))
		{
			// template file not existant in theme, fallback to "default" theme
			if (!is_file($this->_themeDir . 'default/' . $resource_name))
			{
				// requested template file does not exist in "default" theme, die.
				Pommo :: kill(sprintf(Pommo::_T('Template file (%s) not found in
						default or current theme'), $resource_name));
			}
			else
			{
				$resource_name = $this->_themeDir . 'default/' . $resource_name;
				$this->template_dir = $this->_themeDir . 'default';
			}
		}
		if (Pommo::$_logger->isMsg())
		{
			$this->assign('messages', Pommo::$_logger->getMsg());
		}
		if (Pommo::$_logger->isErr())
		{
			$this->assign('errors', Pommo::$_logger->getErr());
		}

		return parent::display($resource_name);
	}
	
	function prepareForForm() {

		$this->plugins_dir[] = Pommo::$_baseDir . 'lib/smarty-plugins/validate';
		require_once(Pommo::$_baseDir.'inc/lib/class.smartyvalidate.php');
		require_once(Pommo::$_baseDir.
				'lib/smarty-plugins/validate/function.validate.php');
		$this->assign('vErr',array());
	}

	// Loads field data into template, as well as _POST (or a saved subscribeForm). 
	function prepareForSubscribeForm() {
		$dbo =& Pommo::$_dbo;
		require_once(Pommo::$_baseDir.'classes/Pommo_Fields.php');

		// Get array of fields. Key is ID, value is an array of the demo's info
		$fields = Pommo_Fields::get(array('active' => TRUE,'byName' => FALSE));
		if (!empty ($fields))
			$this->assign('fields', $fields);
			
		foreach ($fields as $field) {
			if ($field['type'] == 'date')
			$this->assign('datePicker', TRUE);
		}
			
		// process.php appends serialized values to _GET['input']
		// TODO --> look into this behavior... necessary?
		if (isset ($_GET['input'])) 
			$this->assign(unserialize($_GET['input']));
		elseif (isset($_GET['Email'])) 
			$this->assign(array('Email' => $_GET['Email']));
		
		$this->assign($_POST);
	}
	
	// returns an array of invalid fields, empty if none.
	// array key == invalid field, value == message
	// e.g. array(field => 'email', 'message' => 'Must be an Email Address');
	function getInvalidFields($form = SMARTY_VALIDATE_DEFAULT_FORM) {
		$o = array();
		
		foreach($_SESSION['SmartyValidate'][$form]['validators'] as $validator) {
			if(!$validator['valid'])
				array_push($o,array('field' => $validator['field'], 'message' => $validator['message']));
		}
		
		return $o;
	}
}

?>
