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
require_once(Pommo::$_baseDir.'classes/Pommo_Mailing.php');

Pommo::init();
$logger	= Pommo::$_logger;
$dbo 	= Pommo::$_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();

if (Pommo_Mailing::isCurrent())
{
	Pommo::kill(sprintf(Pommo::_T('A Mailing is currently processing. Visit the
			%sStatus%s page to check its progress.'),
			'<a href="mailing_status.php">', '</a>'));
}

if (Pommo::$_config['demo_mode'] == 'on')
{
	$logger->addMsg(sprintf(Pommo::_T('%sDemonstration Mode%s is on -- no Emails
			will actually be sent. This is good for testing settings.'),
			'<a href="'.Pommo::$_baseUrl.
			'setup_configure.php#mailings">', '</a>'));
}


require_once(Pommo::$_baseDir.'themes/wysiwyg/editors.php');
$editors = new PommoWYSIWYG();
$editor = $editors->loadEditor();
if (!$editor)
{
	die('Could not find requested WYSIWYG editor ('.$editor.') in editors.php');
}
$smarty->assign('wysiwygJS', $editor);

// translation assignments for dialg titles...
$smarty->assign('t_personalization', Pommo::_T('Personalization'));
$smarty->assign('t_testMailing', Pommo::_T('Test Mailing'));
$smarty->assign('t_saveTemplate', Pommo::_T('Save Template'));


$smarty->display('admin/mailings/mailings_start.tpl');

