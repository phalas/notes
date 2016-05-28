<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block Notes ajax class.
 *
 * @package    block_notes
 * @copyright  2016 Peter Halás
 * @author     Peter Halas <peter.halas@student.ukf.sk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/externallib.php');

try {
	//set webservice parameters
	if (!($methodname = optional_param('methodname', NULL, PARAM_TEXT))) {
		switch (filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING)) {
			case 'POST':
			case 'PUT':
				$methodname = 'block_notes_store_notes';
				$params = array(
					'note' => json_decode(file_get_contents('php://input'), TRUE)
				);
				break;

			case 'DELETE':
				$methodname = 'block_notes_delete_notes';
				$params = array(
					'note' => json_decode(file_get_contents('php://input'), TRUE)
				);
				break;

			case 'GET':
				$methodname = 'block_notes_get_notes';
				$params = array(
					'coursemodulesid' => required_param('coursemodulesid', PARAM_INT),
					'chapterid' => optional_param('chapterid', NULL, PARAM_INT)
				);
				break;

			default:
				throw new moodle_exception('servicenotavailable', 'webservice');
		}
	} else if ($methodname == 'block_notes_print_notes') {
		$params = array(
			'coursemodulesid' => required_param('coursemodulesid', PARAM_INT),
			'chapterid' => optional_param('chapterid', NULL, PARAM_INT)
		);
	} else {
		throw new moodle_exception('servicenotavailable', 'webservice');
	}

	$externalfunctioninfo = external_function_info($methodname);
	if (!$externalfunctioninfo->allowed_from_ajax) {
		throw new moodle_exception('servicenotavailable', 'webservice');
	}

	// Do not allow access to write or delete webservices as a public user.
	if ($externalfunctioninfo->loginrequired) {
		if (defined('NO_MOODLE_COOKIES') && NO_MOODLE_COOKIES) {
			throw new moodle_exception('servicenotavailable', 'webservice');
		}
		if (!isloggedin()) {
			throw new moodle_exception('servicenotavailable', 'webservice');
		} else {
			require_sesskey();
		}
	}

	// Validate params, this also sorts the params properly, we need the correct order in the next part.
	$callable = array($externalfunctioninfo->classname, 'validate_parameters');
	$validparams = call_user_func($callable, $externalfunctioninfo->parameters_desc, $params);

	// Execute - gulp!
	$callable = array($externalfunctioninfo->classname, $externalfunctioninfo->methodname);
	$response = call_user_func($callable, $validparams);
} catch (Exception $e) {
	$jsonexception = get_exception_info($e);
	unset($jsonexception->a);
	if (!debugging('', DEBUG_DEVELOPER)) {
		unset($jsonexception->debuginfo);
		unset($jsonexception->backtrace);
	}
	$response['error'] = true;
	$response['exception'] = $jsonexception;
}

echo json_encode($response);
