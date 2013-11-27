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

require_once($CFG->dirroot.'/mod/equella/common/lib.php');
require_once($CFG->dirroot.'/mod/equella/common/soap.php');

class block_equella_search_edit_form extends block_edit_form {
    protected function specific_definition($mform) {

        $equella = new EQUELLA(equella_soap_endpoint());
        $equella->loginWithToken(equella_getssotoken());

        $mform->addElement('header', 'configheader', get_string('config.collections.title', 'block_equella_search'));

        // Bit of a hack here since we want to give users is a list of searchable collections,
        // but unfortunatley the current 4.1 version, QA3, does not have such a method.  The
        // addition of a new method has been proposed, but we still need to check if the method
        // doesn't exist and fallback to getting a list of contributable collections.
        if( $equella->hasMethod('getSearchableCollections') ) {
            $collectionsXml = $equella->searchableCollections();
        } else {
            $collectionsXml = $equella->contributableCollections();
        }

        foreach( $collectionsXml->nodeList('/xml/itemdef') as $collectionNode) {
            $value = 'config_collection['. $collectionsXml->nodeValue('uuid', $collectionNode) . ']';
            $mform->addElement('advcheckbox', $value, $collectionsXml->nodeValue('name', $collectionNode), null, array('group' => 1));
        }

        $this->add_checkbox_controller(1, '', null);
    }
}
