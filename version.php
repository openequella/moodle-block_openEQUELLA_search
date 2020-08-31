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

/**
 * Version details
 *
 * @package    block
 * @subpackage equella_search
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020083100;             // The current plugin version (Date: YYYYMMDDXX)
$plugin->component = 'block_equella_search'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'mod_equella' => 2015042000
);
$plugin->release = "1.0.0";
