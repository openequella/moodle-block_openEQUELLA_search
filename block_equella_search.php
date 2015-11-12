<?php

// This file is part of the EQUELLA Moodle Integration - https://github.com/equella/moodle-block-search
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
    }

    function get_content() {
        global $CFG, $USER, $SITE, $COURSE, $DB;

        if( $this->content !== NULL ) {
            return $this->content;
        }
        if( empty($this->instance) ) {
            return null;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        if(isset($this->config->search_type1)) {
            $this->content->search_type1 = $this->config->search_type1;
        }
        if(isset($this->config->search_type2)) {
            $this->content->search_type2 = $this->config->search_type2;
        }
        if(isset($this->config->collection1)) {
            $this->content->collection1 = $this->config->collection1;
        }
        if(isset($this->config->collection2)) {
            $this->content->collection2 = $this->config->collection2;
        }
        if(isset($this->config->collection3)) {
            $this->content->collection3 = $this->config->collection3;
        }
        $this->content->footer = '';

        if (empty($this->instance->pageid)) { // sticky
            if (!empty($COURSE)) {
                $this->instance->pageid = $COURSE->id;
            }
        }

        if (!empty($this->instance->pageid)) {
            $context = context_course::instance($this->instance->pageid);
            if ($COURSE->id == $this->instance->pageid) {
                $course = $COURSE;
            } else {
                $course = $DB->get_record('course', array('id'=>$this->instance->pageid));
            }
        } else {
            $context = context_system::instance();
            $course = $SITE;
        }

        if (!has_capability('block/equella_search:view', $context)) {  // Just return
            return $this->content;
        }

        /// Search Equella
        if ($course->id !== SITEID and has_capability('block/equella_search:search', $context)) {
            $equellasearchurl = new moodle_url('/blocks/equella_search/equella_search_api.php', array('courseid'=>$this->instance->pageid));
            $this->content->items[] = html_writer::link($equellasearchurl, get_string('searchaction', 'block_equella_search'));
            $iconurl = new moodle_url('/mod/equella/pix/icon-red.gif');
            $icon = html_writer::empty_tag('img', array('src' => $iconurl, 'class' => 'icon', 'alt' => 'equella-icon'));
            $this->content->icons[]= $icon;
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
