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
 * Table that displays the certificates issued in a course.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate\output;

use cm_info;
use context_course;
use context_module;
use context_system;
use mod_coursecertificate\permission;
use tool_certificate\template;

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
     * @var int
     */
    protected $groupid;

    /**
     * @var string
     */
    protected $downloadparamname = 'download';

    /**
     * @var bool
     */
    protected $canrevoke;

    /**
     * @var bool
     */
    protected $canviewall;

    /**
     * @var bool
     */
    protected $canverify;

    /**
     * Sets up the table.
     *
     * @param \stdClass $certificate
     * @param cm_info $cm the course module
     * @param int|null $groupid
     */
    public function __construct(\stdClass $certificate, cm_info $cm, int $groupid = null) {
        global $CFG;
        parent::__construct('mod-coursecertificate-issues-' . $cm->instance);

        $context = \context_module::instance($cm->id);

        $this->certificate = $certificate;
        $this->cm = $cm;
        $this->groupid = $groupid;

        $this->canverify = permission::can_verify_issues();
        $this->canrevoke = permission::can_revoke_issues($this->certificate->course);
        $this->canviewall = permission::can_view_all_issues($this->certificate->course);

        $columnsheaders = ['fullname' => get_string('fullname')];
        if ($CFG->version < 2021050700) {
            // Moodle 3.9-3.10.
            $extrafields = get_extra_user_fields($context);
            foreach ($extrafields as $extrafield) {
                $columnsheaders += [$extrafield => get_user_field_name($extrafield)];
            }
        } else {
            // Moodle 3.11 and above.
            $extrafields = \core_user\fields::for_identity($context, false)->get_required_fields();
            foreach ($extrafields as $extrafield) {
                $columnsheaders += [$extrafield => \core_user\fields::get_display_name($extrafield)];
            }
        }

        $columnsheaders += [
            'status' => get_string('status', 'coursecertificate'),
            'expires' => get_string('expirydate', 'coursecertificate'),
            'timecreated' => get_string('issueddate', 'coursecertificate'),
            'code' => get_string('code', 'coursecertificate')
        ];

        $filename = format_string('course-certificate-issues');
        $this->is_downloading(optional_param($this->downloadparamname, 0, PARAM_ALPHA),
            $filename, get_string('certificateissues', 'coursecertificate'));

        if (!$this->is_downloading() && ($this->canrevoke || $this->canviewall)) {
            $columnsheaders += ['actions' => get_string('actions')];
        }

        $this->define_columns(array_keys($columnsheaders));
        $this->define_headers(array_values($columnsheaders));
        $this->collapsible(false);
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('code');
        $this->no_sorting('actions');
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
        $this->useridfield = 'userid';
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
        if (!$this->is_downloading() && $this->canverify) {
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
        $expired = $certificateissue->status == 0;
        $expiredstr = get_string('expired', 'tool_certificate');
        $validstr = get_string('valid', 'tool_certificate');

        return $expired ? $expiredstr : $validstr;
    }

    /**
     * Generate the expires column.
     *
     * @param \stdClass $certificateissue
     * @return string
     */
    public function col_expires($certificateissue) {
        if ($certificateissue->expires > 0) {
            return userdate($certificateissue->expires, get_string('strftimedatetime', 'langconfig'));
        } else {
            return get_string('never');
        }
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
        if ($this->canviewall) {
            $previewicon = new \pix_icon('i/search', get_string('view'));
            $previewlink = template::view_url($certificateissue->code);
            $previewattributes = [
                'target' => '_blank',
                'class' => 'action-icon delete-icon',
                'data-action' => 'preview-issue',
                'data-issueid' => $certificateissue->issueid
            ];
            $actions .= $OUTPUT->action_icon($previewlink, $previewicon, null, $previewattributes);
        }
        if ($this->canrevoke) {
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
     * @uses \tool_certificate\certificate
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        $total = \tool_certificate\certificate::count_issues_for_course(
            $this->certificate->template,
            $this->certificate->course,
            'mod_coursecertificate',
            $this->cm->effectivegroupmode,
            $this->groupid
        );
        $this->pagesize($pagesize, $total);

        $this->rawdata = \tool_certificate\certificate::get_issues_for_course(
            $this->certificate->template,
            $this->certificate->course,
            'mod_coursecertificate',
            $this->cm->effectivegroupmode,
            $this->groupid,
            $this->get_page_start(),
            $this->get_page_size(),
            $this->get_sql_sort()
        );

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Download the data.
     *
     * @uses \tool_certificate\certificate
     */
    public function download() {
        \core\session\manager::write_close();
        $total = \tool_certificate\certificate::count_issues_for_course(
            $this->certificate->template,
            $this->certificate->course,
            'mod_coursecertificate',
            $this->cm->effectivegroupmode,
            $this->groupid
        );
        $this->out($total, false);
        exit;
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();
        echo \html_writer::div(get_string('nouserscertified', 'coursecertificate'), 'alert alert-info mt-3');
    }
}

