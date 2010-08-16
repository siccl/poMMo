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
require_once(Pommo::$_baseDir.'classes/Pommo_Fields.php');
require_once(Pommo::$_baseDir.'classes/Pommo_Mailing.php');

Pommo::init(array('keep' => TRUE));
$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;
	
/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();
$smarty->prepareForForm();

$current = Pommo_Mailing::isCurrent();


if (!SmartyValidate :: is_registered_form() || empty ($_POST)) {
	// ___ USER HAS NOT SENT FORM ___

	SmartyValidate :: connect($smarty, true);

	SmartyValidate :: register_validator('email', 'email', 'isEmail', false, false, 'trim');
	$vMsg = array ();
	$vMsg['email'] = Pommo::_T('Invalid email address');
	$smarty->assign('vMsg', $vMsg);
	
} else {
	// ___ USER HAS SENT FORM ___
	SmartyValidate :: connect($smarty);

	if (SmartyValidate :: is_valid($_POST) && !$current) {
		// __ FORM IS VALID
		require_once(Pommo::$_baseDir.'inc/classes/mailctl.php');
		require_once(Pommo::$_baseDir.'classes/Pommo_Subscribers.php');
		require_once(Pommo::$_baseDir.'classes/Pommo_Validate.php');
		
		// get a copy of the message state
		// composition is valid (via preview.php)
		$state = Pommo::$_session['state']['mailing'];
		
		// create temp subscriber
		$subscriber = array(
			'email' => $_POST['email'],
			'registered' => time(),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'status' => 0,
			'data' => $_POST['d']);
		Pommo_Validate::subscriberData($subscriber['data'],array('active' => FALSE, 'ignore' => TRUE, 'log' => false));
		$key = Pommo_Subscribers::add($subscriber);
		if (!$key)
			$logger->addErr('Unable to Add Subscriber');
		else { // temp subscriber created
			$state['tally'] = 1;
			$state['group'] = Pommo::_T('Test Mailing');
			
			if($state['ishtml'] == 'off') {
				$state['body'] = $state['altbody'];
				$state['altbody'] = '';
			} 
			
			// create mailing
			$mailing = Pommo_Mailing::make(array(), TRUE);
			$state['status'] = 1;
			$state['current_status'] = 'stopped';
			$state['command'] = 'restart';
			$state['charset'] = $state['list_charset'];
			$mailing = Pommo_Helper::arrayIntersect($state, $mailing);
			$code = Pommo_Mailing::add($mailing);
			
			// populate queue
			$queue = array($key);
			if(!PommoMailCtl::queueMake($queue))
				$logger->addErr('Unable to Populate Queue');
			
			// spawn mailer
			else if (!PommoMailCtl::spawn(Pommo::$_baseUrl.'admin/mailings/mailings_send4.php?test=TRUE&code='.$code))
				$logger->addErr('Unable to spawn background mailer');
			else 
				$smarty->assign('sent',$_POST['email']);
		}
	} elseif ($current) {
		$logger->addMsg(Pommo::_T('A mailing is currently taking place. Please try again later.'));
		$smarty->assign($_POST);
	}
	else { 
		// __ FORM NOT VALID
		$logger->addMsg(Pommo::_T('Please review and correct errors with your submission.'));
		$smarty->assign($_POST);
	}
}

if (Pommo::$_config['demo_mode'] == 'on')
	$logger->addMsg(sprintf(Pommo::_T('%sDemonstration Mode%s is on -- no Emails will actually be sent. This is good for testing settings.'),'<a href="'.Pommo::$_baseUrl.'setup_configure.php#mailings">','</a>'));

$smarty->assign('fields',Pommo_Fields::get());
$smarty->display('admin/mailings/mailing/ajax.mailingtest.tpl');
Pommo::kill();
?>
