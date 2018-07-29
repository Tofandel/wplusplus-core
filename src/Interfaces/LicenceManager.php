<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 28/07/2018
 * Time: 18:55
 */

namespace Tofandel\Core\Interfaces;

interface LicenceManager extends SubModule {
	/**
	 * Returns the message of the last request
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * Activates the licence
	 */
	public function activateLicence();

	/**
	 * Checks the licence
	 */
	public function checkLicence();

	/**
	 * Deactivate the licence
	 */
	public function deactivateLicence();

}