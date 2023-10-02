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
use core_reportbuilder\system_report;
use mod_coursecertificate\helper;
use mod_coursecertificate\permission;
use templatable;
use renderable;
use core_reportbuilder\system_report_factory;
use tool_certificate\reportbuilder\local\systemreports\issues;

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

    /** @var system_report $report */
    protected $report;

    /** @var bool $canmanage */
    protected $canmanage;

    /** @var bool $canviewreport */
    protected $canviewreport;

    /** @var bool $canreceiveissues */
    protected $canreceiveissues;

    /** @var bool */
    private $canviewall;

    /** @var cm_info $cm */
    protected $cm;

    /** @var string $issuecode */
    protected $issuecode;

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

        $context = context_module::instance($this->cm->id);
        $this->certificate = $DB->get_record('coursecertificate', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $this->canviewreport = permission::can_view_report($context);
        $this->canmanage = permission::can_manage($context);
        $this->canviewall = permission::can_view_all_issues($course->id);
        $this->canreceiveissues = permission::can_receive_issues($context);

        // Trigger the event.
        $event = \mod_coursecertificate\event\course_module_viewed::create([
            'objectid' => $this->certificate->id,
            'context' => $context,
        ]);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('coursecertificate', $this->certificate);
        $event->trigger();

        // Update the completion.
        $completion = new completion_info($course);
        $completion->set_module_viewed($this->cm);

        // Get the current group.
        $groupid = (int) groups_get_activity_group($this->cm, true);

        // View certificate issue PDF if user can not manage, can receive issues and activity template is correct.
        // TODO WP-3032 bug - if user can view all and receive at the same time they never receive certificate.
        if (!$this->canviewall && $this->canreceiveissues && $this->certificate->template != 0) {
            // View certificate PDF only if activity has a template.
            // Issue certificate to the user if they don't have one or retrieve the issued certificate.
            helper::issue_certificate($USER, $this->certificate, $course);
            if ($existingcertificate = helper::get_user_certificate($USER->id, $course->id, $this->certificate->template)) {
                $this->issuecode = $existingcertificate->code;
            }
        }

        // Show issues table.
        if ($this->canviewreport) {
            $this->report = system_report_factory::create(issues::class, $context, '', '', 0, [
                'templateid' => $this->certificate->template,
                'groupid' => $groupid,
            ]);
        }
    }

    /**
     * Function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        $data = [];
        $data['certificateid'] = $this->certificate->id;
        $data['automaticsend'] = $this->certificate->automaticsend;
        $data['groupselector'] = groups_print_activity_menu($this->cm, $PAGE->url, true);
        if (isset($this->report)) {
            $data['report'] = $this->report->output();
        }
        $data['showautomaticsend'] = $this->canmanage;
        $data['showreport'] = $this->canviewreport;
        $data['notemplateselected'] = $this->certificate->template == 0;
        $data['studentview'] = !$this->canviewall && $this->canreceiveissues;
        $data['showhiddenwarning'] = $this->certificate->automaticsend && !$this->cm->visible;
        $data['shownoautosendinfo'] = !$this->certificate->automaticsend && $this->cm->visible;
        $data['issuecode'] = $this->issuecode;
        if ($this->issuecode) {
            $data['viewurl'] = \tool_certificate\template::view_url($data['issuecode'])->out(false);
        }

        return $data;
    }
}
