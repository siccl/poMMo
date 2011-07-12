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
 
class Pommo_Validate
{
 	// validates supplied subscriber data against fields
	// accepts a subscriber's data (array)
	// accepts a parameter array
	//   prune: (bool) true => if true, prune the data array (passed by reference)
	//     to only recognized/checked fields
	//   ignore: (bool) false => if true, invalid fields will be pruned from $in array -- no error thrown
	//	 ignoreInactive: (bool) true => if true, invalid inactive fields will be pruned from $in array -- no error thrown
	//   active: (bool) true => if true, only check data against active fields. Typically true 
	//     if subscribing via form, false if admin importing. 
	//	 skipReq: (bool) false => if true, skip the required check AND empty fields.
	//   log: (bool) true => if true, log invalid fields as error. Typicall true
	//     if subscribing via form, false if admin importing.
	// returns (bool) validation status
	//   NOTE: has the MAGIC FUNCTIONALITY of converting date field input 
	//     to a UNIX TIMESTAMP. This is necessary for quick SQL comparisson of dates, etc.
	//   NOTE: has the MAGIC FUNCTINALITY of changing "true"/"false" to checkbox "on"/off equivelent
	//   NOTE: has the MAGIC FUNCTIONALITY of trimming leading and trailing whitepace
	//   NOTE: has the MAGIC FUNCTIONALITY of shortening value to 60 characters (or 255 if a comment type)
	
	// TODO -> should fields be passed by reference? e.g. are they usually already available when subscriberData() is called?
	function subscriberData(&$in, $p = array())
	{
		$defaults = array(
			'prune' => true,
			'active' => true,
			'log' => true,
			'ignore' => false,
			'ignoreInactive' => true,
			'skipReq' => false);
		$p = Pommo_Api::getParams($defaults, $p);
		
		require_once(Pommo::$_baseDir.'classes/Pommo_Fields.php');
		$logger = Pommo::$_logger;
		
		$fields = Pommo_Fields::get(array('active' => $p['active']));
		
		$valid = true;
		foreach ($fields as $id => $field) {
			
			$inactive = ($field['active'] == 'on') ? false : true;
			
			if (!isset($in[$id]) && $p['skipReq'])
				continue;
			$in[$id] = @trim($in[$id]);
			
			if (empty($in[$id])) {
				unset($in[$id]); // don't include blank values
				if ($field['required'] == 'on') {
					if ($p['log'])
						$logger->addErr(sprintf(Pommo::_T('%s is a required field.'),$field['prompt']));
					$valid = false;
				}
				continue;
			}
			
			// shorten
			$in[$id] = substr($in[$id],0,255);
			
			switch ($field['type']) {
				case "checkbox":
					if (strtolower($in[$id]) == 'true')
						$in[$id] = 'on';
					if (strtolower($in[$id]) == 'false')
						$in[$id] = '';
					if ($in[$id] != 'on' && $in[$id] != '') {
						if ($p['ignore'] || ($inactive && $p['ignoreInactive'])) {
							unset($in[$id]);
							break;
						}
						if ($p['log'])
							$logger->addErr(sprintf(Pommo::_T('Illegal input for field %s.'),$field['prompt']));
						$valid = false;
					}
					break;
				case "multiple":
					if (is_array($in[$id])) {
						foreach ($in[$id] as $key => $val)
							if (!in_array($val, $field['array'])) {
								if ($p['ignore'] || ($inactive && $p['ignoreInactive'])) {
									unset($in[$id]);
									break;
								}
								if ($p['log'])
									$logger->addErr(sprintf(Pommo::_T('Illegal input for field %s.'),$field['prompt']));
								$valid = false;
							}
					}
					elseif (!in_array($in[$id], $field['array'])) {
						if ($p['ignore'] || ($inactive && $p['ignoreInactive'])) {
							unset($in[$id]);
							break;
						}
						if ($p['log'])
							$logger->addErr(sprintf(Pommo::_T('Illegal input for field %s.'),$field['prompt']));
						$valid = false;
					}
					break;
				case "date": // convert date to timestamp [float; using adodb time library]
					
					if(is_numeric($in[$id]))
						$in[$id] = Pommo_Helper::timeToStr($in[$id]);
						
					$in[$id] = Pommo_Helper::timeFromStr($in[$id]);
					
					if(!$in[$id]) {
						if ($p['ignore'] || ($inactive && $p['ignoreInactive'])) {
							unset($in[$id]);
							break;
						}
						if ($p['log'])
							$logger->addErr(sprintf(Pommo::_T('Field (%s) must be a date ('.Pommo_Helper::timeGetFormat().').'),$field['prompt']));
						$valid = false;
					}
					break;
				case "number":
					if (!is_numeric($in[$id])) {
						if ($p['ignore'] || ($inactive && $p['ignoreInactive'])) {
							unset($in[$id]);
							break;
						}
						if ($p['log'])
							$logger->addErr(sprintf(Pommo::_T('Field (%s) must be a number.'),$field['prompt']));
						$valid = false;
					}
				break;
			}
		}
		// prune
		if($p['prune'])
			$in = Pommo_Helper::arrayIntersect($in,$fields);
			
		return $valid;
	}

	/*	validateEmail
	 *	Validates an E-mail address
	 *
	 *	@param	string	$email.- E-mail to validate
	 *
     * 	@return	boolean	True if valid, false otherwise
     */
	public static function validateEmail($email)
	{
		$regex = '/^[_A-z0-9-]+((\.|\+)[_A-z0-9-]+)*@[A-z0-9-]+(\.[A-z0-9-]+)*'.
				'(\.[A-z]{2,4})$/';
		
		if (!preg_match($regex, $email))
		{
        	return false;
    	}
    	else
    	{
        	return true;
    	}
	}
	
	/*	validateDateTime
	 *	Validates a datetime value
	 *
	 *	@param	string	$date.- Date to validate expected in yyyy-mm-dd hh:mm:ss
	 *
     * 	@return	boolean	True if valid, false otherwise
     */
	public static function validateDateTime($date)
	{
		list($date, $time) = explode(' ', $date);
		
		if (!self::validateDate($date) || !self::validateTime($time))
		{
			return false;
		}
		
		return true;
	}
	
	/*	validateDate
	 *	Validates a date value
	 *
	 *	@param	string	$date.- Date to validate expected in yyyy-mm-dd
	 *
     * 	@return	boolean	True if valid, false otherwise
     */
	public static function validateDate($date)
	{
		list($year, $month, $day) = explode('-', $date);
		
		return @checkdate($month, $day, $year);
	}
	
	/*	validateTime
	 *	Validates a time value for mysql
	 *
	 *	@param	string	$time.- Time to validate expected in hh:mm:ss
	 *
     * 	@return	boolean	True if valid, false otherwise
     */
	public static function validateTime($time)
	{
		list($hour, $minute, $second) = explode(':', $time);
		
		$hour = (int)$hour;
		if (0 > $hour || 24 < $hour)
		{
			return false;
		}
		
		$minute = (int)$minute;
		if (0 > $minute || 59 < $minute)
		{
			return false;
		}
		
		$second = (int)$second;
		if (0 > $second || 59 < $second)
		{
			return false;
		}
		
		return true;
	}

	/*	validateUrl
	 *	Validates an URL. This validation is part of smarty_validate plugin.
	 *
	 *	@param	string	$url.- URL to validate
	 *
     * 	@return	boolean	True if valid, false otherwise
     */
	public static function validateUrl($url)
	{
		return preg_match('!^http(s)?://[\w-]+\.[\w-]+(\S+)?$!i', $url);
	}
 }

