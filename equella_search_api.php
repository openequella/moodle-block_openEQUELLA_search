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
require_once($CFG->dirroot.'/mod/equella/common/lib.php');
require_once($CFG->dirroot.'/mod/equella/common/soap.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/equella_search/block_equella_search.php');

global $DB, $CFG,$USER, $OUTPUT;

$courseid       = required_param('courseid', PARAM_INT); 
$page			= optional_param('page', 0, PARAM_INT);
$perpage		= optional_param('perpage', 10, PARAM_INT);
$sorttype		= optional_param('sorttype', 3, PARAM_INT);
$searchstring	= optional_param('searchstring', '', PARAM_TEXT);

set_time_limit(100);

$course = $DB->get_record('course', array('id' => $courseid));
require_login($course);

$PAGE->set_url('/blocks/equella_search/equella_search_api.php', array('courseid' => $courseid));
$PAGE->set_title($course->shortname.': '.get_string('pagetitle', 'block_equella_search'));
$PAGE->set_heading($course->fullname.': '.get_string('pagetitle', 'block_equella_search'));
$PAGE->navbar->add(get_string('searchaction', 'block_equella_search'), $CFG->wwwroot.'/blocks/equella_search/equella_search_api.php?courseid='.$courseid); 

echo $OUTPUT->header();
echo $OUTPUT->container_start();

$configdata = get_block_configdata('equella_search');

?>
<form action="equella_search_api.php" method="get">
	<input type="hidden" name="courseid" value="<?php echo $courseid ?>" />
	<input type="hidden" name="form_submitted" value="1" />

	<div align="center">
		<label for="searchstring"><?php echo get_string('search.label', 'block_equella_search') ?></a>
		<input type="text" id="searchstring" name="searchstring" size="40" value="<?php echo $searchstring ?>" />
		<input type="submit" value="<?php echo get_string('search.button', 'block_equella_search') ?>" />
	</div>

	<br>
	<hr>
	<br>
<?php

if(isset($_REQUEST['form_submitted'])){

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
			if ($itemFile == '')
			{
				$itemFullUrl = $itemUrl . 'viewdefault.jsp';
			}
			else
			{
				$itemFullUrl = $itemUrl.'?attachment.uuid='.$attUuid;
			}
			
			$table->data[] = array (
				htmlentities($searchResultsXml->nodeValue('xml/item/name', $result), ENT_COMPAT, 'UTF-8'), 
				htmlentities($searchResultsXml->nodeValue('xml/item/description', $result), ENT_COMPAT, 'UTF-8'),
				htmlentities($itemFile, ENT_COMPAT, 'UTF-8'),
				'<a href="'.equella_appendtoken($itemFullUrl).'" target="_blank">'.get_string('view', 'block_equella_search').'</a>'
			);

			echo '<input type="hidden" name="url_'.$itemUuid.'"  value="'.$itemUrl.'" />';
			echo '<input type="hidden" name="file_'.$itemUuid.'" value="'.$itemFile.'" />';
		}
	
		?>
			<div style="float: right">
				<label for="sorttype"><?php echo get_string('order', 'block_equella_search') ?></label>
				<select id="sorttype" name="sorttype" onChange="document.location = '<?php echo reloadQuery() ?>sorttype=' + this.options[this.selectedIndex].value">
					<?php
						sortOption(0);
						sortOption(1);
						sortOption(2);
						sortOption(3);
					?>
				</select>
			</div>
		<?php

		echo '<h3>'.get_string('resultcount', 'block_equella_search', array(
			'from' => ($page * $perpage) + 1,
			'to' => min(($page * $perpage) + $perpage, $resultsavailable),
			'total' => $resultsavailable			
		)).'</h3>';

		echo html_writer::table($table);
		echo '<p>&nbsp;</p>';
		
		$pagingbar = new paging_bar($resultsavailable, $page, $perpage, reloadQuery()."sorttype=$sorttype&amp;");
		$pagingbar->pagevar = 'page';
		echo $OUTPUT->render($pagingbar);

	} else {

		echo '<h3>'.get_string('noresults', 'block_equella_search').'</h3>';

	}

	echo get_String('tryequella', 'block_equella_search', equella_appendtoken(equella_full_url('access/search.do')));
}

echo '</form><br>';
echo $OUTPUT->container_end();
echo $OUTPUT->footer($course);

///////////////// Functions /////////////////

/**
 * Does not include "sorttype".  Do it yourself!
 */ 
function reloadQuery($page = null) {
	global $searchstring, $perpage, $courseid;
	return "?searchstring=$searchstring&amp;perpage=$perpage&amp;courseid=$courseid&amp;form_submitted=1&amp;";
	if( $page ) {
		$q .= "page=$page&amp;";
	}
	return $q;
}

function suppress_node_not_found($string){
	if ($string == '!! node not found !!')
	{
	 return '';
	}
	return $string;	
}

function sortOption($num) {
	global $sorttype;
	echo '<option value="'.$num.'"';
	if( $sorttype == $num ) {
		echo ' selected="selected"';
	}
	echo '>'.get_string('sort.'.$num, 'block_equella_search').'</option>';
}
?>
