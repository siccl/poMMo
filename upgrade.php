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
require ('bootstrap.php');
require_once(Pommo::$_baseDir.'classes/Pommo_Install.php');
Pommo::init(array('authLevel' => 0, 'install' => TRUE));
Pommo::reloadConfig();

$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;
$dbo->dieOnQuery(FALSE);

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();
$smarty->prepareForForm();

// Check to make sure poMMo is installed
if (!Pommo_Install::verify()) {
	$logger->addErr(sprintf(Pommo::_T('poMMo does not appear to be installed! Please %s INSTALL %s before attempting an upgrade.'), '<a href="' . Pommo::$_baseUrl . 'install/install.php">', '</a>'));
	$smarty->display('upgrade.tpl');
	Pommo::kill();
}

// Check to make sure poMMo is PR14 or higher.
if (Pommo::$_config['revision'] < 26) {
	$logger->addErr('Upgrade path unavailable. Cannot upgrade from Aardvark PR13.2 or below!');
	$smarty->display('upgrade.tpl');
	Pommo::kill();
}

// Check to make sure poMMo is not already up to date.
if (Pommo::$_config['revision'] == Pommo::$_revision && !isset ($_REQUEST['forceUpgrade']) && !isset ($_REQUEST['continue'])) {
	$logger->addErr(sprintf(Pommo::_T('poMMo appears to be up to date. If you want to force an upgrade, %s click here %s'), '<a href="' . $_SERVER['PHP_SELF'] . '?forceUpgrade=TRUE">', '</a>'));
	$smarty->display('upgrade.tpl');
	Pommo::kill();
}

// include the upgrade procedure file
require_once(Pommo::$_baseDir . 'install/helper.upgrade.php');

if (isset ($_REQUEST['disableDebug']))
	unset ($_REQUEST['debugInstall']);
elseif (isset ($_REQUEST['debugInstall'])) $smarty->assign('debug', TRUE);

if (empty($_REQUEST['continue'])) {
	$logger->addErr(sprintf(Pommo::_T('To upgrade poMMo, %s click here %s'), '<a href="' . Pommo::$_baseUrl . 'upgrade.php?continue=TRUE">', '</a>'));
} else {
	$smarty->assign('attempt', TRUE);

	if (isset ($_REQUEST['debugInstall']))
		$dbo->debug(TRUE);
		
	if (isset($_REQUEST['forceUpgrade']))
		$GLOBALS['pommoFakeUpgrade'] = true;

	if (PommoUpgrade()) {
		$logger->addErr(Pommo::_T('Upgrade Complete!'));

		// Read in RELEASE Notes -- TODO -> use file_get_contents() one day when everyone has PHP 4.3
		$filename = Pommo::$_baseDir . 'docs/RELEASE';
		$handle = fopen($filename, "r");
		$x = fread($handle, filesize($filename));
		fclose($handle);

		$smarty->assign('notes', $x);
		$smarty->assign('upgraded', TRUE);
	} else {
		$logger->addErr(Pommo::_T('Upgrade Failed!'));
	}
	
	// clear the working directory template files
	$smarty->display('upgrade.tpl');
	
	require_once(Pommo::$_baseDir.'classes/Pommo_Helper_Maintenance.php');
	if(!Pommo_Helper_Maintenance::delDir(Pommo::$_workDir.'/pommo/smarty'))
		$logger->addErr('Unable to Clear Working Directory (non fatal)');
	
	Pommo::kill();	
}

$smarty->display('upgrade.tpl');
Pommo::kill();
?>
