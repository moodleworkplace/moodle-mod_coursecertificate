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
 * Certificate issues report renderable.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate\output;

use cm_info;
use completion_info;
use context_module;
use mod_coursecertificate\helper;
use mod_coursecertificate\permission;
use moodle_url;
use templatable;
use renderable;

defined('MOODLE_INTERNAL') || die();

/**
 * Certificate issues report renderable class.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements templatable, renderable {

    /** @var \stdClass $certificate */
    protected $certificate;

    /** @var int $perpage */
    protected $perpage;

    /** @var certificate_issues_table $table */
    protected $table;

    /** @var bool $canmanage */
    protected $canmanage;

    /** @var bool $canviewreport */
    protected $canviewreport;

    /** @var bool $canreceiveissues */
    protected $canreceiveissues;

    /** @var bool */
    private $canviewall;

    /** @var moodle_url $pageurl */
    protected $pageurl;

    /** @var cm_info $cm */
    protected $cm;

    /**
     * Constructor.
     *
     * @param int $id
     * @param int $page
     * @param int $perpage
     * @param \stdClass $course
     * @param cm_info $cm
     */
    public function __construct(int $id, int $page, int $perpage, \stdClass $course, cm_info $cm) {
        global $DB, $PAGE, $USER;

        $this->perpage = $perpage;
        $this->cm = $cm;
        $this->pageurl = new moodle_url('/mod/coursecertificate/view.php', ['id' => $id,
            'page' => $page, 'perpage' => $perpage]);

        $context = context_module::instance($this->cm->id);
        $this->certificate = $DB->get_record('coursecertificate', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $this->canviewreport = permission::can_view_report($context);
        $this->canmanage = permission::can_manage($context);
        $this->canviewall = permission::can_view_all_issues($course->id);
        $this->canreceiveissues = permission::can_receive_issues($context);

        // Trigger the event.
        $event = \mod_coursecertificate\event\course_module_viewed::create([
            'objectid' => $this->certificate->id,
            'context' => $context
        ]);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('coursecertificate', $this->certificate);
        $event->trigger();

        // Update the completion.
        $completion = new completion_info($course);
        $completion->set_module_viewed($this->cm);

        // Get the current group.
        if (groups_get_activity_groupmode($this->cm)) {
            $groupid = groups_get_activity_group($this->cm, true);
        }

        // View certificate issue PDF if user can not manage, can receive issues and activity template is correct.
        if (!$this->canviewall && $this->canreceiveissues && $this->certificate->template != 0) {
            // View certificate PDF only if activity has a template.
            $params = ['id' => $this->certificate->template];
            $templaterecord = $DB->get_record('tool_certificate_templates', $params, '*', MUST_EXIST);
            if ($templaterecord) {
                $issuesqlconditions = [
                    'userid' => $USER->id,
                    'templateid' => $templaterecord->id,
                    'courseid' => $course->id,
                    'component' => 'mod_coursecertificate'
                ];
                // If user does not have an issue yet, create it first.
                $issuedata = helper::get_issue_data($course, $USER);
                if (!$DB->record_exists('tool_certificate_issues', $issuesqlconditions)) {
                    \tool_certificate\template::instance($templaterecord->id)->issue_certificate(
                        $USER->id,
                        $this->certificate->expires,
                        $issuedata,
                        'mod_coursecertificate',
                        $course->id
                    );
                }
                // Redirect to view issue page.
                if ($issue = $DB->get_record('tool_certificate_issues', $issuesqlconditions, '*', MUST_EXIST)) {
                    $showissueurl = new \moodle_url('/admin/tool/certificate/view.php',
                        ['code' => $issue->code]);
                    redirect($showissueurl);
                }
            }
        }

        // Show issues table.
        if ($this->canviewreport) {
            $this->table = new certificate_issues_table($this->certificate, $this->cm, $groupid ?? null);
            $this->table->define_baseurl($this->pageurl);

            if ($this->table->is_downloading()) {
                $this->table->download();
                exit();
            }
        }

        $PAGE->set_url('/mod/coursecertificate/view.php', ['id' => $this->cm->id]);
        $PAGE->set_title(format_string($this->certificate->name));
        $PAGE->set_heading(format_string($course->fullname));
        $PAGE->set_context($context);
    }

    /**
     * Function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        $data = [];
        $data['certificateid'] = $this->certificate->id;
        $data['certificatename'] = $this->certificate->name;
        $data['automaticsend'] = $this->certificate->automaticsend;
        if (isset($this->table)) {
            $data['table'] = $this->render_table($this->table);
        }
        $data['showautomaticsend'] = $this->canmanage;
        $data['showreport'] = $this->canviewreport;
        $data['notemplateselected'] = $this->certificate->template == 0;
        $data['studentview'] = !$this->canviewall && $this->canreceiveissues;
        $data['showhiddenwarning'] = $this->certificate->automaticsend && !$this->cm->visible;
        $data['shownoautosendinfo'] = !$this->certificate->automaticsend && $this->cm->visible;

        return $data;
    }

    /**
     * Renders a table.
     *
     * @param \table_sql $table
     * @return string HTML
     */
    private function render_table(\table_sql $table) {
        ob_start();
        groups_print_activity_menu($this->cm, $this->pageurl);
        $table->out($this->perpage, false);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
