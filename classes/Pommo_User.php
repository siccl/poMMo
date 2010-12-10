<?php
/**
 * Copyright (C) 2010  Adrian Ancona Novelo <soonick5@yahoo.com.mx>
 * 
 * This file is part of poMMo (https://github.com/soonick/pommo)
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

/*	Pommo_User
 *	Class in charge of opperations about users (adding, deleting, etc...)
 */
class Pommo_User
{
	/*	__construct
	 *
	 *	@return	void
	 */
	function __construct()
	{
	}

	/*	save
	 *	Saves a new user in the database
	 *
	 *	@param	string	$username
	 *	@param	string	$password
	 *
	 *	@return	boolean	True if the user was saved, false otherwise
	 */
	function save($username, $password)
	{
		try
		{
			$dbo = Pommo::$_dbo;
			$dbo->_dieOnQuery = false;

			$query = 'INSERT INTO '.$dbo->table['users'].'
					SET username = "%s", password = SHA1("%s")';
			if (!$dbo->query($dbo->prepare($query, array($username, $password))))
			{
				return false;
			}
			return true;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
}

