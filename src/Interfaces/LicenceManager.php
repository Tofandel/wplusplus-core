<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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