<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
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
 * Course certificate module mobile functions.
 *
 * @package     mod_coursecertificate
 * @copyright   2021 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_coursecertificate' => [
        'handlers' => [
            'coursecertificate' => [
                'displaydata' => [
                    'title' => 'pluginname',
                    'icon' => $CFG->wwwroot . '/mod/coursecertificate/pix/monologo.svg',
                    'class' => '',
                ],

                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'mobile_certificate_view',
            ],
        ],
        'lang' => [
            ['pluginname', 'coursecertificate'],
            ['nouserscertified', 'coursecertificate'],
            ['certifiedusers', 'coursecertificate'],
            ['code', 'coursecertificate'],
            ['expirydate', 'coursecertificate'],
            ['issueddate', 'coursecertificate'],
            ['open', 'coursecertificate'],
            ['valid', 'tool_certificate'],
            ['expired', 'tool_certificate'],
            ['user', 'core'],
            ['email', 'core'],
            ['status', 'core'],
            ['preview', 'core'],
        ],
    ],
];
