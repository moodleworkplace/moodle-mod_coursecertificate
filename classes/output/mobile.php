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

use cm_info;
use context_course;
use context_module;
use tool_certificate\certificate;
use tool_certificate\permission;
use tool_certificate\template;

/**
 * Mobile output class for coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2021 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Returns the coursecertificate view for the mobile app.
     *
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array       HTML, javascript and otherdata
     */
    public static function mobile_certificate_view(array $args): array {
        global $OUTPUT, $DB, $PAGE, $CFG;
        require_once($CFG->libdir . '/externallib.php');

        $args = (object) $args;
        [$course, $cm] = get_course_and_cm_from_cmid((int) $args->cmid, 'coursecertificate');
        $context = context_module::instance($cm->id);
        $output = $PAGE->get_renderer('coursecertificate');

        // Capabilities check.
        require_course_login($args->courseid , false , $cm, true, true);

        // Get certificate information.
        $viewpage = new view_page($cm->instance, 0, 0, $course, $cm);
        $certificatedata = $viewpage->export_for_template($output);
        $certificate = $DB->get_record('coursecertificate', ['id' => $certificatedata['certificateid']], '*', MUST_EXIST);
        $certificate->name = format_string($certificate->name);
        [$certificate->intro, $certificate->introformat] = external_format_text($certificate->intro,
            $certificate->introformat, $context->id, 'mod_coursecertificate', 'intro');

        // Handle groups.
        $groups = [];
        $groupid = !empty($args->group) ? (int) $args->group : 0;
        if ($groupmode = groups_get_activity_groupmode($cm)) {
            $groups = self::get_groups_options($cm, $groupmode, $groupid);
        }

        // If 'showreport' (user can see report), get issues information.
        $issues = [];
        if ($certificatedata['showreport']) {
            $issuesrecords = certificate::get_issues_for_course($certificate->template, $certificate->course,
                'mod_coursecertificate', $groupmode, $groupid, 0, 0 );
            foreach ($issuesrecords as $issuerecord) {
                $issue = new issue($issuerecord);
                $issues[] = (object) $issue->export_for_template($output);
            }
        }

        // If 'studentview' (user can not manage but can receive issues), get the issue file.
        $fileurl = '';
        if ($certificatedata['studentview']) {
            $issue = template::get_issue_from_code($certificatedata['issuecode']);
            $template = $issue ? template::instance($issue->templateid) : null;
            $context = context_course::instance($issue->courseid, IGNORE_MISSING) ?: null;
            if ($template && permission::can_view_issue($template, $issue, $context)) {
                $fileurl = $template->get_issue_file_url($issue);
            }
        }

        $data = [
            'cmid' => $cm->id,
            'certificate' => $certificate,
            'showreport' => $certificatedata['showreport'],
            'showgroups' => !empty($groups),
            'groups' => $groups,
            'hasissues' => !empty($issues),
            'issues' => array_values($issues),
            'showissue' => $certificatedata['studentview'],
            'fileurl' => $fileurl,
            'currenttimestamp' => time(),
        ];

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_coursecertificate/mobile_view_page', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => [
                'group' => $groupid,
            ],
            'files' => '',
        ];
    }

    /**
     * Returns the certificate issue details for the mobile app.
     *
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array       HTML, javascript and otherdata
     */
    public static function mobile_issue_details(array $args): array {
        global $OUTPUT;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_coursecertificate/mobile_issue_details', (object) $args),
                ],
            ],
            'javascript' => '',
            'otherdata' => '',
            'files' => '',
        ];
    }

    /**
     * Returns allowed groups for the group selector.
     *
     * @param cm_info $cm
     * @param int $groupmode
     * @param int $selectedgroup
     * @return array
     */
    private static function get_groups_options(cm_info $cm, int $groupmode, int $selectedgroup): array {
        global $USER;

        $groups = [];
        $context = context_module::instance($cm->id);
        if ($groupmode == VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $context)) {
            // Add 'All participants' option.
            $allparticipants = (object) [
                'id' => 0,
                'name' => get_string('allparticipants'),
                'selected' => $selectedgroup == 0,
            ];
            $groups[] = $allparticipants;
        }
        foreach (groups_get_activity_allowed_groups($cm, $USER->id) as $groupid => $group) {
            $group->selected = $groupid == $selectedgroup;
            $groups[] = $group;
        }
        return $groups;
    }
}
