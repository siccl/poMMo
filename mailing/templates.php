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
require_once(Pommo::$_baseDir.'inc/helpers/templates.php');

Pommo::init();
$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();
$smarty->prepareForForm();

/**********************************
	JSON OUTPUT INITIALIZATION
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Json.php');
$json = new Pommo_Json();

$success = false;

if(isset($_POST['skip']) || (isset($_POST['template']) && !is_numeric($_POST['template'])))
	$success = true;
elseif(isset($_POST['load'])) {
	$template = current(Pommo_MailingTemplate::get(array('id' => $_POST['template'])));
	Pommo::$_session['state']['mailing']['body'] = $template['body'];
	Pommo::$_session['state']['mailing']['altbody'] = $template['altbody'];
	
	$success = true;
}
elseif(isset($_POST['delete'])) {
	$msg = (Pommo_MailingTemplate::delete($_POST['template'])) ?
		Pommo::_T('Template Deleted') :
		Pommo::_T('Error with deletion.');
	
	$json->add('callbackFunction','deleteTemplate');
	$json->add('callbackParams', array(
		'id' => $_POST['template'],
		'msg' => $msg)
	);
}
else {
	$smarty->assign('templates',Pommo_MailingTemplate::getNames());
	$smarty->display('admin/mailings/mailing/templates.tpl');
	Pommo::kill();
}

$json->serve($success);
?>
