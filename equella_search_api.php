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

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/adminlib.php');

require_once($CFG->dirroot . '/mod/equella/common/lib.php');
require_once($CFG->dirroot . '/mod/equella/common/soap.php');
require_once($CFG->dirroot . '/blocks/moodleblock.class.php');

require_once(dirname(__FILE__) . '/block_equella_search.php');
require_once(dirname(__FILE__) . '/search_form.php');

$courseid     = required_param('courseid', PARAM_INT);
$page	      = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 10, PARAM_INT);
$sorttype     = optional_param('sorttype', 3, PARAM_INT);
$searchstring = optional_param('searchstring', '', PARAM_TEXT);

// Unlimited execue time
@set_time_limit(0);

$course = $DB->get_record('course', array('id' => $courseid));
require_login($course);

$PAGE->set_url('/blocks/equella_search/equella_search_api.php', array('courseid' => $courseid));
$PAGE->set_title($course->shortname.': '.get_string('pagetitle', 'block_equella_search'));
$PAGE->set_heading($course->fullname.': '.get_string('pagetitle', 'block_equella_search'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('searchaction', 'block_equella_search'), $CFG->wwwroot.'/blocks/equella_search/equella_search_api.php?courseid='.$courseid);

echo $OUTPUT->header();
echo $OUTPUT->container_start();

$configdata = get_block_configdata('equella_search');

$searchform = new block_equella_search_form('equella_search_api.php', array('courseid'=>$courseid), 'post');

if (!empty($searchstring)) {
    $data = array(
        'searchstring'=>$searchstring,
    );
    $searchform->set_data($data);
}

echo $OUTPUT->box_start();
$searchform->display();
echo $OUTPUT->box_end();

if (!empty($searchstring)) {

    $equella = new EQUELLA(equella_soap_endpoint());
    $equella->loginWithToken(equella_getssotoken());

    $offset = $page*$perpage;

    // array_filter with no second parameter removes all entries with null/0/false values

    $filter_collections = null;
    if (isset($configdata->collection))
    {
        $filter_collections = array_keys(array_filter($configdata->collection));
    }

    $searchResultsXml = $equella->searchItems($searchstring, $filter_collections, null, 1, $sorttype, 0, $offset, $perpage);
    $resultsavailable = $searchResultsXml->nodeValue('/results/available');

    if( $resultsavailable ) {

        $table = new html_table();
        $table->width = '100%';
        $table->align = array('left', 'left', 'left', 'center');

        foreach (array('title', "description", "filename", "action") as $column) {
            $table->head[$column] = get_string('header.'.$column, 'block_equella_search');
        }

        foreach( $searchResultsXml->nodeList('/results/result') as $result ) {
            $itemUuid = $searchResultsXml->nodeValue('xml/item/@id', $result);
            $itemUrl = $searchResultsXml->nodeValue('xml/item/url', $result);
            $itemFile = $searchResultsXml->nodeValue('xml/item/attachments/attachment/file', $result);
            $attUuid = $searchResultsXml->nodeValue('xml/item/attachments/attachment/uuid', $result);
            if ($itemFile == '') {
                $itemFullUrl = $itemUrl . 'viewdefault.jsp';
            } else {
                $itemFullUrl = $itemUrl.'?attachment.uuid='.$attUuid;
            }

            $table->data[] = array (
                htmlentities($searchResultsXml->nodeValue('xml/item/name', $result), ENT_COMPAT, 'UTF-8'),
                htmlentities($searchResultsXml->nodeValue('xml/item/description', $result), ENT_COMPAT, 'UTF-8'),
                htmlentities($itemFile, ENT_COMPAT, 'UTF-8'),
                html_writer::link(equella_appendtoken($itemFullUrl), get_string('view', 'block_equella_search'), array('target'=>'_blank')),
            );

            echo '<input type="hidden" name="url_'.$itemUuid.'"  value="'.$itemUrl.'" />';
            echo '<input type="hidden" name="file_'.$itemUuid.'" value="'.$itemFile.'" />';
        }

        $menu = array();
        for ($i = 0; $i < 4; $i++) {
            $menu[$i] = get_string('sort.'.$i, 'block_equella_search');
        }

        $params = array('courseid'=>$courseid, 'searchstring'=>$searchstring, 'page'=>$page, 'perpage'=>$perpage);
        $singleselect = new single_select(new moodle_url("/blocks/equella_search/equella_search_api.php", $params), 'sorttype', $menu, $sorttype, null, "catmenu");
        $singleselect->set_label(get_string('order', 'block_equella_search'));

        echo $OUTPUT->box_start('mdl-right');
        echo $OUTPUT->render($singleselect);
        echo $OUTPUT->box_end();

        $heading = get_string('resultcount', 'block_equella_search', array(
                'from' => ($page * $perpage) + 1,
                'to' => min(($page * $perpage) + $perpage, $resultsavailable),
                'total' => $resultsavailable
        ));
        echo $OUTPUT->heading($heading, 3);

        echo html_writer::table($table);

        // Build page bar
        $params['sorttype'] = $sorttype;
        unset($params['page']);
        unset($params['perpage']);
        $baseurl = new moodle_url('/blocks/equella_search/equella_search_api.php', $params);
        $pagingbar = new paging_bar($resultsavailable, $page, $perpage, $baseurl);
        $pagingbar->pagevar = 'page';
        echo $OUTPUT->render($pagingbar);

    } else {
        $heading = get_string('noresults', 'block_equella_search');
        echo $OUTPUT->heading($heading, 3);
    }

    echo get_string('tryequella', 'block_equella_search', equella_appendtoken(equella_full_url('access/search.do')));
}

echo $OUTPUT->container_end();
echo $OUTPUT->footer($course);
