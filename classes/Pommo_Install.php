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

class Pommo_Install
{
	public static $errors;

	/**
	 *	validateInstallationData
	 *	Validates data necessary for installation
	 *
	 *	@param	array	$data.- Data to be validated
	 *
	 *	@return	boolean	True on success, false otherwise
	 */
	public static function validateInstallationData($data)
	{
		require_once(Pommo::$_baseDir.'classes/Pommo_Validate.php');

		self::$errors = array();

		$emptyMessage = _('Cannot be empty.');

		if (empty($data['list_name']))
		{
			self::$errors['list_name'] = $emptyMessage;
		}

		if (empty($data['site_name']))
		{
			self::$errors['site_name'] = $emptyMessage;
		}

		if (!Pommo_Validate::validateUrl($data['site_url']))
		{
			self::$errors['site_url'] = _('Must be a valid URL');
		}

		if (empty($data['admin_password']))
		{
			self::$errors['admin_password'] = $emptyMessage;
		}

		if ($data['admin_password'] != $data['admin_password2'])
		{
			self::$errors['admin_password2'] = _('Passwords must match.');
		}

		if (!Pommo_Validate::validateEmail($data['admin_email']))
		{
			self::$errors['admin_email'] = _('Must be a valid email');
		}

		if (empty(self::$errors))
		{
			return true;
		}
		return false;
	}

 	/*	parseSQL
 	 *	parses a SQL file (usually generated via mysqldump)
 	 *	text like ':::table:::' will be replaced with $dbo->table['table'];
 	 *	(to add prefix)
 	 *
 	 *	@param	boolean	$ignoreerrors
 	 *	@param	boolean	$file
 	 *
 	 *	@return	boolean	True on success, false otherwise
 	 */
 	function parseSQL($ignoreerrors = false, $file = false)
 	{
		$dbo 	= Pommo::$_dbo;
		$logger = Pommo::$_logger;
	
		if (!$file)
		{
			$file = Pommo::$_baseDir.'sql/sql.schema.php';
		}
			
		$file_content = @file($file);
		if (empty ($file_content))
		{
			Pommo::kill('Error installing. Could not read '.$file);
		}
		$query = '';
		foreach ($file_content as $sql_line)
		{
			$tsl = trim($sql_line);
			if (($sql_line != "")
					&& (substr($tsl, 0, 2) != "--")
					&& (substr($tsl, 0, 1) != "#"))
			{
				$query .= $sql_line;
				if (preg_match("/;\s*$/", $sql_line))
				{
					$matches = array();
					preg_match('/:::(.+):::/', $query, $matches);
					if ($matches[1])
					{
						$query = preg_replace('/:::(.+):::/',
								$dbo->table[$matches[1]], $query);
					}
					$query = trim($query);
					if (!$dbo->query($query) && !$ignoreerrors)
					{
						$logger->addErr(Pommo::_T('Database Error: ').
								$dbo->getError());
						return false;
					}
					$query = '';
				}
			}
		}
		return true;
 	}
 	
 	// verifies if poMMo has been installed.
 	// returns bool (true if installed)
 	function verify() {
 		global $pommo;
		$dbo =& Pommo::$_dbo;
 		if (is_object($dbo)) {
			$query = "SHOW TABLES LIKE '%s'";
			$query = $dbo->prepare($query,array($dbo->_prefix.'%'));
			if ($dbo->records($query) > 10)
				return true;
		}
		return false;
 	}
 	
 	// performs an update increment
 	// checks if the update has already been performed
 	// returns update status
 	function incUpdate($serial, $sql, $msg = "Performing Update", $eval = false) {
 		global $pommo;
 		$dbo =& Pommo::$_dbo;
 		$logger =& Pommo::$_logger;
 			
 		if (!is_numeric($serial))
 			Pommo::kill('Invalid serial passed; '.$serial);
 			
 		$msg = $serial . ". $msg ...";
 			
		$query = "
			SELECT serial FROM ".$dbo->table['updates']." 
			WHERE serial=%i";
			
		$query = $dbo->prepare($query,array($serial));
		if ($dbo->records($query)) {
			$msg .= "skipped.";
			$logger->addMsg($msg);
			return true;
		}
		
		if(!isset($GLOBALS['pommoFakeUpgrade'])) {
			
			// run the update
			
			if($eval) {
				eval($sql);
			}
			else {
				$query = $dbo->prepare($sql);
				if (!$dbo->query($query)) {
					// query failed...
					$msg .= ($GLOBALS['pommoLooseUpgrade']) ?
						'IGNORED.' : 'FAILED';
					$logger->addErr($msg);
				
					return $GLOBALS['pommoLooseUpgrade'];
				}
			}
			
			$msg .= "done.";
			$logger->addMsg($msg);
		}
		else {
			$msg .= "skipped.";
			$logger->addMsg($msg,2);
		}

		$query = "
			INSERT INTO ".$dbo->table['updates']." 
			(serial) VALUES(%i)";
		$query = $dbo->prepare($query,array($serial));
		if (!$dbo->query($query))
			Pommo::kill('Unable to serialize');
		
		
		
		return true;
 	}
}
?>
