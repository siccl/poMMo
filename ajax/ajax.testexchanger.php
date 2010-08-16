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

/**********************************
	INITIALIZATION METHODS
*********************************/
require ('../bootstrap.php');
Pommo::init();
$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();
$smarty->prepareForForm();

$exchanger = current(Pommo_Api::configGet(array ('list_exchanger')));

SmartyValidate :: connect($smarty);
if (!SmartyValidate :: is_registered_form('exchanger') || empty ($_POST)) {
	// ___ USER HAS NOT SENT FORM ___
	SmartyValidate::register_form('exchanger', true);
	
	SmartyValidate :: register_validator('email', 'email', 'isEmail', false, false, false, 'exchanger');
    
	$vMsg = array();
	$vMsg['email'] = Pommo::_T('Invalid email address');
	$smarty->assign('vMsg', $vMsg);
	
	$dbvals = array('exchanger' => $exchanger, 'email' => Pommo::$_config['admin_email']);
	$smarty->assign($dbvals);
	
} else {
	// ___ USER HAS SENT FORM ___
	
	/**********************************
		JSON OUTPUT INITIALIZATION
	 *********************************/
	require_once(Pommo::$_baseDir.'classes/Pommo_Json.php');
	$json = new Pommo_Json();
	
	if (SmartyValidate :: is_valid($_POST, 'exchanger')) {
		// __ FORM IS VALID
		require_once(Pommo::$_baseDir.'classes/Pommo_Helper_Messages.php');
		
		$msg = (Pommo_Helper_Messages::testExchanger($_POST['email'],$exchanger)) ? 
			Pommo::_T('Mail Sent.') :
			Pommo::_T('Error Sending Mail');
			
		$json->success($msg);
	
	}
	else {
		// __ FORM NOT VALID
		
		$json->addMsg(Pommo::_T('Please review and correct errors with your submission.'));
		$json->add('fieldErrors',$smarty->getInvalidFields('exchanger'));
		$json->fail();
	}
	
}
$smarty->assign($_POST);
$smarty->display('admin/setup/config/ajax.testexchanger.tpl');
Pommo::kill();
			
