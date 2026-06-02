<?php

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link        https://kalkun.sourceforge.io/
 */

namespace App\Libraries;

trait KalkunPhonenumberTrait {

	/**
	* Convert a phone number as input by the user to E164 format
	* using the region of the user.
	* Done with libphonenumber
	*
	* @param string $phone
	* @return string
	*/
	function phone_format_e164($phone, $input_region = NULL)
	{
		// Default value to '' for the case this is called through Daemon or API
		// This way, we consider number is already in international format.
		$region = '';
		// If user is logged in, get the region from the settings
		if (isset($this->session) && $this->session->get('loggedin') === 'TRUE')
		{
			$this->Kalkun_model = model('KalkunModel');
			$region = $this->Kalkun_model->get_setting()->getRow('country_code');
		}
		// region as function parameter has higher precedence
		$region = ($input_region !== NULL) ? $input_region : $region;

		// reformat phone number to E164
		$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);
		$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);
		return $phone_number;
	}

	/**
	* Convert a phone number as input to human readable format
	* NATIONAL if same region as user, otherwise INTERNATIONAL
	* Done with libphonenumber
	*
	* @param string $phone
	* @return string
	*/
	function phone_format_human($phone, $input_region = NULL)
	{
		$this->Kalkun_model = model('KalkunModel');
		try
		{
			$region = (! empty($input_region)) ? $input_region : $this->Kalkun_model->get_setting()->getRow('country_code');

			$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);

			$phone_region = $phoneNumberUtil->getRegionCodeForNumber($phoneNumberObject);

			if ($region === $phone_region)
			{
				$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::NATIONAL);
			}
			else
			{
				$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
			}
			return $phone_number;
		}
		catch (Exception $e)
		{
			return $phone;
		}
	}

	/**
	* Check phone number validity
	*
	* returns TRUE if valid, otherwise a String containing
	* an error message.
	*
	*/
	function is_phone_number_valid($phone, $input_region = NULL)
	{
		$result = 'false'; // Default to "false"

		$this->Kalkun_model = model('KalkunModel');
		try
		{
			// Check if is possible number
			$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			$region = (! empty($input_region)) ? $input_region : $this->Kalkun_model->get_setting()->getRow('country_code');
			$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);
			$is_possible = $phoneNumberUtil->isPossibleNumber($phoneNumberObject);

			// Check if is mobile number
			$type = $phoneNumberUtil->getNumberType($phoneNumberObject);
			$is_mobile = ($type === \libphonenumber\PhoneNumberType::MOBILE
				|| $type === \libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE);

			// Check if is possible short number
			$shortNumberUtil = \libphonenumber\ShortNumberInfo::getInstance();
			$is_possible_short = $shortNumberUtil->isPossibleShortNumber($phoneNumberObject);

			if ($is_possible && $is_mobile || $is_possible_short)
			{
				$result = TRUE;
			}
			else
			{
				$result = tr_no_op('Please specify a valid mobile phone number');
			}
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
		}
		return $result;
	}
}
