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
 * Table that displays the certificates issued in a course.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate\output;

use context_course;
use context_module;
use context_system;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class certificate_issues_table.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate_issues_table extends \table_sql {
    /**
     * @var \stdClass $certificate Course certificate
     */
    protected $certificate;

    /**
     * @var \stdClass $cm The course module.
     */
    protected $cm;

    /**
     * @var string
     */
    protected $downloadparamname = 'download';

    /**
     * Sets up the table.
     *
     * @param \stdClass $certificate
     * @param \stdClass $cm the course module
     */
    public function __construct($certificate, $cm)
    {
        parent::__construct('mod-coursecertificate-issues-' . $cm->instance);

        $context = \context_module::instance($cm->id);
        $extrafields = get_extra_user_fields($context);

        $columns = [];
        $columns[] = 'fullname';
        foreach ($extrafields as $extrafield) {
            $columns[] = $extrafield;
        }
        $columns[] = 'status';
        $columns[] = 'expires';
        $columns[] = 'timecreated';
        $columns[] = 'code';

        $headers = [];
        $headers[] = get_string('fullname');
        foreach ($extrafields as $extrafield) {
            $headers[] = get_user_field_name($extrafield);
        }
        $headers[] = get_string('status', 'coursecertificate');
        $headers[] = get_string('expirydate', 'coursecertificate');
        $headers[] = get_string('issueddate', 'coursecertificate');
        $headers[] = get_string('code', 'coursecertificate');

        if (!$this->is_downloading()) {
            $columns[] = 'actions';
            $headers[] = get_string('actions');
        }

        $filename = format_string('course-certificate-issues');
        $this->is_downloading(optional_param($this->downloadparamname, 0, PARAM_ALPHA),
            $filename, get_string('certificateissues', 'coursecertificate'));

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true, 'firstname');
        $this->no_sorting('code');
        $this->no_sorting('status');
        $this->no_sorting('actions');
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
        $this->useridfield = 'userid';

        $this->certificate = $certificate;
        $this->cm = $cm;
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_fullname($certificateissue) {
        global $OUTPUT;

        if (!$this->is_downloading()) {
            return $OUTPUT->user_picture($certificateissue) . ' ' . fullname($certificateissue);
        } else {
            return fullname($certificateissue);
        }
    }

    /**
     * Generate the certificate time created column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_timecreated($certificateissue) {
        return userdate($certificateissue->timecreated, get_string("strftimedatetime", "langconfig"));
    }

    /**
     * Generate the code column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_code($certificateissue) {
        // TODO: check tool certificate permission class
        if (!$this->is_downloading() && \tool_certificate\permission::can_verify()) {
            return \html_writer::link(new \moodle_url('/admin/tool/certificate/index.php', ['code' => $certificateissue->code]),
                    $certificateissue->code, ['title' => get_string('verify', 'tool_certificate')]);
        }
        return $certificateissue->code;
    }

    /**
     * Generate the status column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
     public function col_status($certificateissue) {
         $expired = ($certificateissue->expires > 0) && ($certificateissue->expires <= time());

         return $expired ? get_string('expired', 'tool_certificate') :
              get_string('valid', 'tool_certificate');
     }

    /**
     * Generate the status column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_expires($certificateissue) {
        return $certificateissue->expires > 0 ?
            userdate($certificateissue->expires, get_string('strftimedatetime', 'langconfig'))
            : get_string('never');
    }

    /**
     * Generate the actions column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_actions($certificateissue) {
        global $OUTPUT;

        $actions = '';
        // TODO: Use another capability?
        if (\tool_certificate\permission::can_verify()) {
            $previewicon = new \pix_icon('i/search', get_string('view'));
            $previewlink = new \moodle_url('/admin/tool/certificate/view.php',
                ['code' => $certificateissue->code]);
            $previewattributes = [
                'class' => 'action-icon delete-icon',
                'data-action' => 'preview-issue',
                'data-issueid' => $certificateissue->issueid
            ];
            $actions .= $OUTPUT->action_icon($previewlink, $previewicon, null, $previewattributes);
        }

        $context = context_course::instance($certificateissue->courseid);
        if (\tool_certificate\permission::can_manage($context)) {
            $rekoveicon = new \pix_icon('i/delete', get_string('revoke', 'coursecertificate'));
            $revokeattributes = [
                'class' => 'action-icon revoke-icon',
                'data-action' => 'revoke-issue',
                'data-issueid' => $certificateissue->issueid
            ];
            $actions .= $OUTPUT->action_icon('#', $rekoveicon, null, $revokeattributes);
        }

        return $actions;
    }

    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        $total = \tool_certificate\certificate::count_issues_for_course($this->certificate->template, $this->certificate->course);
        $this->pagesize($pagesize, $total);

        $this->rawdata = \tool_certificate\certificate::get_issues_for_course($this->certificate->template, $this->certificate->course, $this->cm,
            $this->get_page_start(), $this->get_page_size(), $this->get_sql_sort());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Download the data.
     */
    public function download() {
        \core\session\manager::write_close();
        $total = \tool_certificate\certificate::count_issues_for_course($this->certificate->template, $this->certificate->course);
        $this->out($total, false);
        exit;
    }

    /**
     * This function is not part of the public api.
     */
    function print_nothing_to_display() {
        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();
        echo \html_writer::div(get_string('nouserscertified', 'coursecertificate'), 'alert alert-info');
    }
}

