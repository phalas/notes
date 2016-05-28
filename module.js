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
 * Block Notes module javascript.
 *
 * @package    block_notes
 * @copyright  2016 Peter Halás
 * @author     Peter Halas <peter.halas@student.ukf.sk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$.getUrlVar = function(key){
	var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search); 
	return result && unescape(result[1]) || null; 
};

var annotator = $('.box.generalbox').annotator().data('annotator');

annotator.addPlugin('Store', {
	prefix: M.cfg.wwwroot+'/blocks/notes/ajax.php?sesskey='+M.cfg.sesskey,

	//default values need to be removed - prefix url is used
	urls: {
		create: '',
		update: '',
		destroy: '',
		search: ''
	},
	
	//add attributes to store requests made by annotator
	annotationData: {
		'coursemodulesid': $.getUrlVar('id'),
		'chapterid': $.getUrlVar('chapterid')
	},

	//this will perform a "search" action when the plugin loads
	loadFromSearch: {
		'coursemodulesid': $.getUrlVar('id'),
		'chapterid': $.getUrlVar('chapterid')
	}
});

annotator.addPlugin('Unsupported', {
	message: "Modul nie je podporovaný týmto prehliadačom."
});

//solution of annotator bug (https://github.com/openannotation/annotator/issues/495)
$(document).ajaxSuccess(function(event, xhr, settings, data){
	if (settings.url.search(M.cfg.wwwroot+"/blocks/notes/ajax.php") !== -1 && settings.type === 'POST'){
		$('.annotator-hl:not([data-annotation-id])').attr('data-annotation-id', data.id);
	}
});

$("a.print").click(function(){
	$.get(M.cfg.wwwroot+'/blocks/notes/ajax.php?methodname=block_notes_print_notes&coursemodulesid='+$.getUrlVar('id')+'&chapterid='+$.getUrlVar('chapterid')+'&sesskey='+M.cfg.sesskey, function(data){
		var box = $(".annotator-wrapper > div.no-overflow").clone();
		
		//add document header
		box.prepend($("<h1>"+data.name+"</h1>"));
		if (data.title) {
			box.prepend($("<h2>"+data.title+"</h2>"));
		}
		
		//highlight marked text
		$(".annotator-hl", box).attr('style', 'background-color: yellow !important; -webkit-print-color-adjust: exact;');

		//match notes with highlighted parts of text
		$('.annotator-hl[data-annotation-id]', box).each(function(){
			$(this).append($("<sup>"+$(this).data('annotation-id')+"</sup>"));
		});
		
		//append notes
		$.each(data.notes, function(k, v) {
			box.append($("<div><i>" + v.id + " " + (v.text.length ? v.text : 'bez poznámky') + "</i></div>"));
		});
		
		//print selected area
		box.printArea();
	});
});