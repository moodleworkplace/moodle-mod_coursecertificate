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

namespace mod_coursecertificate\output;

use renderable;
use renderer_base;
use stdClass;
use templatable;
use tool_certificate\template;

/**
 * Issue renderable class.
 *
 * @package     mod_coursecertificate
 * @copyright   2021 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class issue implements renderable, templatable {

    /**
     * @var stdClass $issue
     */
    private $issue;

    /**
     * Constructor
     *
     * @param stdClass $issue
     */
    public function __construct(stdClass $issue) {
        $this->issue = $issue;
    }

    /**
     * Exports for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'fullname' => fullname($this->issue),
            'email' => $this->issue->email,
            'expires' => $this->issue->expires > 0 ? userdate($this->issue->expires, get_string('strftimedatetime', 'langconfig'))
                : get_string('never'),
            'timecreated' => userdate($this->issue->timecreated, get_string('strftimedatetime', 'langconfig')),
            'status' => $this->issue->status,
            'statusstring' => $this->issue->status == 0 ? get_string('expired', 'tool_certificate')
                : get_string('valid', 'tool_certificate'),
            'code' => $this->issue->code,
            'previewurl' => template::view_url($this->issue->code),
            'verifyurl' => template::verification_url($this->issue->code),
        ];
    }
}
