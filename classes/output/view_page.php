<?php
// This file is part of Moodle - http://moodle.org/
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

use completion_info;
use context_module;
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

    /**
     * Constructor.
     * @param $id
     * @param $page
     * @param $perpage
     */
    public function __construct() {
        global $DB, $PAGE;

        $id = required_param('id', PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);

        $pageurl = $url = new moodle_url('/mod/coursecertificate/view.php', array('id' => $id,
            'page' => $page, 'perpage' => $perpage));

        list ($course, $cm) = get_course_and_cm_from_cmid($id, 'coursecertificate');
        $this->certificate = $DB->get_record('coursecertificate', ['id' => $cm->instance], '*', MUST_EXIST);
        $this->perpage = $perpage;
        require_login($course, true, $cm);

        $context = context_module::instance($cm->id);
        $this->canviewreport = permission::can_view_report($context);
        $this->canmanage = permission::can_manage($context);

        $event = \mod_coursecertificate\event\course_module_viewed::create([
            'objectid' => $this->certificate->id,
            'context' => $context
        ]);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('coursecertificate', $this->certificate);
        $event->trigger();

        // TODO: Set completion in the right cases.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        if ($this->canviewreport) {
            $this->table = new certificate_issues_table($this->certificate, $cm);
            $this->table->define_baseurl($pageurl);

            if ($this->table->is_downloading()) {
                $this->table->download();
                exit();
            }
        }

        $PAGE->set_url('/mod/certificate/view.php', ['id' => $cm->id]);
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
        $data['canmanage'] = $this->canmanage;
        $data['canviewreport'] = $this->canviewreport;
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
        $table->out($this->perpage, false);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}