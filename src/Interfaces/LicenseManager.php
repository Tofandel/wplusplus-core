<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 28/07/2018
 * Time: 18:55
 */

namespace Tofandel\Core\Interfaces;

interface LicenseManager extends SubModule {
	/**
	 * Returns the message of the last request
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * Activates the license
	 */
	public function activateLicense();

	/**
	 * Checks the license
	 */
	public function checkLicense();

	/**
	 * Deactivate the license
	 */
	public function deactivateLicense();

}