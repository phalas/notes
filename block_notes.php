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
 * Block Notes class.
 *
 * @package    block_notes
 * @copyright  2016 Peter Halás
 * @author     Peter Halas <peter.halas@student.ukf.sk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_notes extends block_base {
	public function init() {
		$this->title = get_string('notes', 'block_notes');
	}
	
	function instance_allow_multiple() {
		return true;
	}

	function has_config() {
		return false;
	}

	public function applicable_formats() {
		return array(
			'mod-page' => true, 
			'mod-book' => true
		);
	}

	public function get_content() {
		global $CFG;
		if ($this->content !== null) {
			return $this->content;
		}
		
		$this->page->requires->jquery();
		$this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/notes/annotator-full.min.js'));
		$this->page->requires->css(new moodle_url($CFG->wwwroot . '/blocks/notes/annotator.min.css'));
		$this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/notes/jquery.printarea.js'));
		$this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/notes/module.js'));
		
		$this->content = new stdClass;
		$attrs = array('href' => 'javascript:void(0)', 'class' => 'print');
		$this->content->text = html_writer::tag('a', get_string('print', 'block_notes'), $attrs);
		$this->content->footer = get_string('footer', 'block_notes');

		return $this->content;
	}
}