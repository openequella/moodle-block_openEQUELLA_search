<?php

// This file is part of the EQUELLA Moodle Integration - http://code.google.com/p/equella-moodle-module/
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

class block_equella_search extends block_list {

	function init() {
		$this->title = get_string('title', 'block_equella_search');
		$this->version = 2011012800;
	}

    function specialization() {
      if (!isset($this->config->collection)) {
        $this->config->collection = array();
      }    
    }
    
	function get_content() {

		global $CFG, $USER, $SITE, $COURSE;

        if( $this->content !== NULL ) {
            return $this->content;
        }
        if( empty($this->instance) ) {
            return null;
        }


		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
        $this->content->footer = '';

		if (empty($this->instance->pageid)) { // sticky
			if (!empty($COURSE)) {
				$this->instance->pageid = $COURSE->id;
			}
		}

		if (!empty($this->instance->pageid)) {
			$context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
			if ($COURSE->id == $this->instance->pageid) {
				$course = $COURSE;
			} else {
				$course = get_record('course', 'id', $this->instance->pageid);
			}
		} else {
			$context = get_context_instance(CONTEXT_SYSTEM);
			$course = $SITE;
		}

		if (!has_capability('moodle/course:view', $context)) {  // Just return
			return $this->content;
		}

		/// Search Equella
		if ($course->id !== SITEID and has_capability('moodle/course:managefiles', $context)) {
			$this->content->items[]= '<a href="'.$CFG->wwwroot.'/blocks/equella_search/equella_search_api.php?courseid='.$this->instance->pageid.'">'.get_string('searchaction', 'block_equella_search').'</a>';
			$this->content->icons[]= '<img src="'.$CFG->wwwroot.'/mod/equella/pix/icon-red.gif" class="icon" alt="" />';
		}

		return $this->content;
	}

	function applicable_formats() {
		return array('course' => true);   // Not needed on site
	}

	function has_config() {
		return false;
	}

	function instance_allow_config() {
		return true;
	}
}

?>
