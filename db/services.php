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

defined('MOODLE_INTERNAL') || die();

/**
 * Block Notes services.
 *
 * @package    block_notes
 * @copyright  2016 Peter Halás
 * @author     Peter Halas <peter.halas@student.ukf.sk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$services = array(
	//the name of the web service
	'block_notes_service' => array(
		//web service functions of this service
		'functions' => array(
			'block_notes_store_notes', 'block_notes_get_notes', 'block_notes_print_notes'
		),
		//if set, the web service user need this capability to access
		//any function of this service. For example: 'some/capability:specified'
		'requiredcapability' => '',  
		//if enabled, the Moodle administrator must link some user to this service
		'restrictedusers' => 0,
		//if enabled, the service can be reachable on a default installation
		'enabled' => 1
	)
);

$functions = array(
    'block_notes_store_notes' => array( //web service function name
        'classname'   => 'block_notes_external', //class containing the external function
        'methodname'  => 'store_notes', //external function name
        'classpath'   => 'blocks/notes/externallib.php', //file containing the class/external function
        'description' => 'Stores new notes.', //human readable description of the web service function
        'type'        => 'write' //database rights of the web service function (read, write)
    ),
	'block_notes_get_notes' => array( //web service function name
        'classname'   => 'block_notes_external', //class containing the external function
        'methodname'  => 'get_notes', //external function name
        'classpath'   => 'blocks/notes/externallib.php', //file containing the class/external function
        'description' => 'Get notes.', //human readable description of the web service function
        'type'        => 'read' //database rights of the web service function (read, write)
    ),
	'block_notes_print_notes' => array( //web service function name
        'classname'   => 'block_notes_external', //class containing the external function
        'methodname'  => 'print_notes', //external function name
        'classpath'   => 'blocks/notes/externallib.php', //file containing the class/external function
        'description' => 'Print notes.', //human readable description of the web service function
        'type'        => 'read' //database rights of the web service function (read, write)
    ),
	'block_notes_delete_notes' => array( //web service function name
        'classname'   => 'block_notes_external', //class containing the external function
        'methodname'  => 'delete_notes', //external function name
        'classpath'   => 'blocks/notes/externallib.php', //file containing the class/external function
        'description' => 'Delete notes.', //human readable description of the web service function
        'type'        => 'write' //database rights of the web service function (read, write)
    )
);