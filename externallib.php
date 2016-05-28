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

require_once("$CFG->libdir/externallib.php");

/**
 * Block Notes external class.
 *
 * @package    block_notes
 * @copyright  2016 Peter Halás
 * @author     Peter Halas <peter.halas@student.ukf.sk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_notes_external extends external_api {

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function store_notes_parameters() {
		return new external_function_parameters(array(
			'note' => new external_single_structure(array(
				'ranges' => new external_multiple_structure(
					new external_single_structure(array(
						'start' => new external_value(PARAM_TEXT, 'start position of annotation in text'),
						'startOffset' => new external_value(PARAM_INT, 'start offset of annotation in text'),
						'end' => new external_value(PARAM_TEXT, 'end position of annotation in text'),
						'endOffset' => new external_value(PARAM_INT, 'end offset of annotation in text')
					))
				),
				'quote' => new external_value(PARAM_TEXT, 'quoted text'),
				'text' => new external_value(PARAM_TEXT, 'annotation text'),
				'coursemodulesid' => new external_value(PARAM_INT, 'id of course module'),
				'chapterid' => new external_value(PARAM_INT, 'id of chapter', VALUE_DEFAULT, NULL),
				'id' => new external_value(PARAM_INT, 'id of note', VALUE_DEFAULT, NULL)
			))
		));
	}

	/**
	 * Returns description of method return values
	 * @return external_single_structure
	 */
	public static function store_notes_returns() {
		return new external_single_structure(array(
			'id' => new external_value(PARAM_INT, 'note id')
		));
	}

	/**
	 * Returns whether service is callable from ajax
	 * @return bool
	 */
	function store_notes_is_allowed_from_ajax() {
		return true;
	}

	/**
	 * Store notes
	 * @param array $params
	 * @return array
	 */
	public static function store_notes($params) {
		global $DB, $USER;
		
		$validparams = self::validate_parameters(self::store_notes_parameters(), $params);
		$note = (object) $validparams['note'];
		$note->ranges = json_encode($note->ranges);
		$note->userid = $USER->id;
		
		$transaction = $DB->start_delegated_transaction();
		if ($note->id) {
			$DB->update_record('block_notes', $note);
		} else {
			$note->id = $DB->insert_record('block_notes', $note);
		}
		$transaction->allow_commit();

		return array('id' => $note->id);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_notes_parameters() {
		return new external_function_parameters(array(
			'coursemodulesid' => new external_value(PARAM_INT, 'id of course module'),
			'chapterid' => new external_value(PARAM_INT, 'id of chapter')
		));
	}

	/**
	 * Returns description of method return values
	 * @return external_single_structure
	 */
	public static function get_notes_returns() {
		return new external_single_structure(array(
			'total' => new external_value(PARAM_INT, 'total number of notes'),
			'rows' => new external_multiple_structure(
				new external_single_structure(array(
					'id' => new external_value(PARAM_INT, 'note id'),
					'coursemodulesid' => new external_value(PARAM_INT, 'course modules id'),
					'chapterid' => new external_value(PARAM_INT, 'chapter id', VALUE_DEFAULT, NULL),
					'quote' => new external_value(PARAM_TEXT, 'quoted text'),
					'text' => new external_value(PARAM_TEXT, 'annotation text'),
					'ranges' => new external_multiple_structure(
						new external_single_structure(array(
							'start' => new external_value(PARAM_TEXT, 'start position of annotation in text'),
							'startOffset' => new external_value(PARAM_INT, 'start offset of annotation in text'),
							'end' => new external_value(PARAM_TEXT, 'end position of annotation in text'),
							'endOffset' => new external_value(PARAM_INT, 'end offset of annotation in text')
						))
					)
				))
			)
		));
	}

	/**
	 * Returns whether service is callable from ajax
	 * @return bool
	 */
	function get_notes_is_allowed_from_ajax() {
		return true;
	}
	
	/**
	 * Get notes
	 * @param array $params
	 * @return array
	 */
	public static function get_notes($params) {
		global $DB, $USER;

		$validparams = self::validate_parameters(self::get_notes_parameters(), $params);
		$note = (object) $validparams;
		$note->userid = $USER->id;

		$fields = 'id,coursemodulesid,chapterid,quote,text,ranges';
		$sort = 'id';
		$conditions = array(
			'coursemodulesid' => $note->coursemodulesid, 'userid' => $note->userid
		);
		if ($note->chapterid) {
			$conditions['chapterid'] = $note->chapterid;
		}
		
		$noterecords = $DB->get_records('block_notes', $conditions, $sort, $fields);
		foreach ($noterecords as $noterecord) {
			$noterecord->ranges = json_decode($noterecord->ranges);
		}
		
		return array(
			'total' => count($noterecords), 
			'rows' => array_values($noterecords)
		);
	}
	
	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function print_notes_parameters() {
		return new external_function_parameters(array(
			'coursemodulesid' => new external_value(PARAM_INT, 'id of course module'),
			'chapterid' => new external_value(PARAM_INT, 'id of chapter')
		));
	}

	/**
	 * Returns description of method return values
	 * @return external_single_structure
	 */
	public static function print_notes_returns() {
		return new external_single_structure(array(
			'name' => new external_value(PARAM_TEXT, 'page or book name to print'),
			'title' => new external_value(PARAM_TEXT, 'chapter title to print'),
			'notes' => new external_value(PARAM_RAW, 'notes to print')
		));
	}

	/**
	 * Returns whether service is callable from ajax
	 * @return bool
	 */
	function print_notes_is_allowed_from_ajax() {
		return true;
	}
	
	/**
	 * Print notes
	 * @param array $params
	 * @return array
	 */
	public static function print_notes($params) {
		global $DB, $USER;

		$validparams = self::validate_parameters(self::print_notes_parameters(), $params);
		$note = (object) $validparams;
		$note->userid = $USER->id;

		$modulename = $DB->get_field_sql("SELECT md.name FROM {modules} md "
				. "JOIN {course_modules} cm ON cm.module = md.id "
				. "WHERE cm.id = :cmid", array('cmid' => $note->coursemodulesid));
				
		$sql = '';
		$attrs = array();
		if ($modulename == 'page'){
			$attrs['cmid'] = $note->coursemodulesid;
			$sql = "SELECT p.name FROM {course_modules} cm "
					. "JOIN {modules} md ON md.id = cm.module "
					. "JOIN {page} p ON p.id = cm.instance "
					. "WHERE cm.id = :cmid";
		} else if ($modulename == 'book') {
			$attrs['cmid'] = $note->coursemodulesid;
			$attrs['bcid'] = $note->chapterid;
			$sql = "SELECT b.name, bc.title FROM {course_modules} cm "
					. "JOIN {modules} md ON md.id = cm.module "
					. "JOIN {book} b ON b.id = cm.instance "
					. "JOIN {book_chapters} bc ON bc.bookid = b.id "
					. "WHERE cm.id = :cmid AND bc.id = :bcid";
		} else {
			throw new invalid_parameter_exception('Unknown module name.');
		}
		$modulerecord = $DB->get_record_sql($sql, $attrs);

		$conditions = array('coursemodulesid' => $note->coursemodulesid, 'userid' => $note->userid);
		if ($note->chapterid) {
			$conditions['chapterid'] = $note->chapterid;
		}
		$noterecords = $DB->get_records('block_notes', $conditions);
		
		return array(
			'name' => $modulerecord->name, 
			'title' => (isset($modulerecord->title) ? $modulerecord->title : NULL), 
			'notes' => $noterecords
		);
	}
	
	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function delete_notes_parameters() {
		return new external_function_parameters(array(
			'note' => new external_single_structure(array(
				'ranges' => new external_multiple_structure(
					new external_single_structure(array(
						'start' => new external_value(PARAM_TEXT, 'start position of annotation in text'),
						'startOffset' => new external_value(PARAM_INT, 'start offset of annotation in text'),
						'end' => new external_value(PARAM_TEXT, 'end position of annotation in text'),
						'endOffset' => new external_value(PARAM_INT, 'end offset of annotation in text')
					))
				),
				'quote' => new external_value(PARAM_TEXT, 'quoted text'),
				'text' => new external_value(PARAM_TEXT, 'annotation text'),
				'coursemodulesid' => new external_value(PARAM_INT, 'id of course module'),
				'chapterid' => new external_value(PARAM_INT, 'id of chapter', VALUE_DEFAULT, NULL),
				'userid' => new external_value(PARAM_INT, 'id of user', VALUE_DEFAULT, NULL),
				'id' => new external_value(PARAM_INT, 'id of note', VALUE_DEFAULT, NULL)
			))
		));
	}

	/**
	 * Returns description of method return values
	 * @return external_single_structure
	 */
	public static function delete_notes_returns() {
		return new external_single_structure(array(
			'id' => new external_value(PARAM_INT, 'note id')
		));
	}

	/**
	 * Returns whether service is callable from ajax
	 * @return bool
	 */
	function delete_notes_is_allowed_from_ajax() {
		return true;
	}

	/**
	 * Delete notes
	 * @param array $params
	 * @return array
	 */
	public static function delete_notes($params) {
		global $DB, $USER;
		
		$validparams = self::validate_parameters(self::delete_notes_parameters(), $params);
		$note = (object) $validparams['note'];
		$note->userid = $USER->id;
		
		$transaction = $DB->start_delegated_transaction();
		$DB->delete_records('block_notes', array('id' => $note->id, 'userid' => $note->userid));
		$transaction->allow_commit();

		return array('id' => NULL);
	}
}
