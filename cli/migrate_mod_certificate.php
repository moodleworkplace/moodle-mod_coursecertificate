<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Legacy mod_certificate migration to mod_coursecertificate.
 *
 * @package    local_olms_work
 * @copyright  2023 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

/** @var moodle_database $DB */
/** @var stdClass $CFG */

require_once(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Show extra debug info.
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
error_reporting($CFG->debug);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// Now get cli options.
list($options, $unrecognized) = cli_get_params([
    'templateid' => false,
    'cmid' => false,
    'uninstall' => false,
    'help' => false,
], ['h' => 'help']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error('Unknown options: ' . $unrecognized);
}

if ($options['help'] || (!$options['templateid'] && !$options['uninstall'])
) {
    echo <<<EOL
Legacy mod_certificate migration to mod_coursecertificate.

Options:
--templateid=XX        Target certificate template id.
--cmid=XX              Optional source activity id, all if not specified.
--uninstall            Uninstall mod_certificate plugin.
-h, --help             Print out this help.

EOL;
    die;
}

raise_memory_limit(MEMORY_HUGE);
$CFG->noemailever = true;

if (!$DB->get_manager()->table_exists('certificate')
    || !$DB->get_manager()->table_exists('certificate_issues')
    || !file_exists("$CFG->dirroot/mod/certificate/version.php")
) {
    cli_error('Plugin mod_certificate must be installed and present');
}

if (!get_config('mod_coursecertificate', 'version')
    || !file_exists("$CFG->dirroot/mod/coursecertificate/version.php")
) {
    cli_error('Plugin mod_coursecertificate must be installed and present');
}

$sql = "SELECT c.*, cm.id AS cmid
          FROM {certificate} c
          JOIN {modules} m ON m.name = 'certificate'
          JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = c.id
      ORDER BY c.id ASC";
$records = $DB->get_records_sql($sql, []);

if ($options['cmid']) {
    $certificates = [];
    foreach ($records as $record) {
        if ($record->cmid == $options['cmid']) {
            $certificates[$record->id] = $record;
            break;
        }
    }
    if (!$certificates) {
        cli_error('Invalid cmid');
    }
} else {
    $certificates = $records;
}
unset($records);

if ($certificates) {
    if (!$options['templateid']) {
        cli_error('Template id is required');
    }
    $template = $DB->get_record('tool_certificate_templates', ['id' => $options['templateid']], '*', MUST_EXIST);
    $newmodule = $DB->get_record('modules', ['name' => 'coursecertificate'], '*', MUST_EXIST);
    $fs = get_file_storage();
    $syscontext = context_system::instance();

    // First make sure pdf files are present.
    cli_writeln('Generating missing PDF certificate files:');
    require_once("$CFG->dirroot/mod/certificate/locallib.php");
    require_once($CFG->dirroot.'/mod/certificate/locallib.php');
    require_once("$CFG->libdir/pdflib.php");
    make_cache_directory('tcpdf');

    foreach ($certificates as $certificate) {
        $cm = $DB->get_record('course_modules', ['id' => $certificate->cmid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $certificate->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        $sql = "SELECT ci.*
                  FROM {certificate_issues} ci
                  JOIN {certificate} c ON c.id = ci.certificateid
                  JOIN {modules} m ON m.name = 'certificate'
                  JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = c.id
                  JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :modulelevel
                  JOIN {user} u ON u.id = ci.userid AND u.deleted = 0
             LEFT JOIN {files} f ON f.contextid = ctx.id AND f.component = 'mod_certificate' AND f.filearea = 'issue'
                                    AND f.itemid = ci.id AND f.filepath = '/' AND f.filename <> '.'
                 WHERE f.id IS NULL
              ORDER BY ci.id ASC";
        $params = ['id' => $certificate->id, 'modulelevel' => CONTEXT_MODULE];
        $issues = $DB->get_records_sql($sql, $params);
        foreach ($issues as $certrecord) {
            $user = $DB->get_record('user', ['id' => $certrecord->userid], '*', MUST_EXIST);
            cron_setup_user($user);
            $filename = certificate_get_certificate_filename($certificate, $cm, $course) . '.pdf';
            $pdf = null;
            require("$CFG->dirroot/mod/certificate/type/$certificate->certificatetype/certificate.php");
            $filecontents = $pdf->Output('', 'S');
            certificate_save_pdf($filecontents, $certrecord->id, $filename, $context->id);
        }
    }
    cron_setup_user('reset');
    $CFG->debug = (E_ALL | E_STRICT);
    $CFG->debugdisplay = 1;
    error_reporting($CFG->debug);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    cli_writeln('...finished.');

    // Migrate activity records and files.
    foreach ($certificates as $certificate) {
        $trans = $DB->start_delegated_transaction();
        $cm = $DB->get_record('course_modules', ['id' => $certificate->cmid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $certificate->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        $cc = new stdClass();
        $cc->course = $certificate->course;
        $cc->name = $certificate->name;
        $cc->template = $template->id;
        $cc->automaticsend = 0;
        $cc->expirydatetype = 0;
        $cc->expirydateoffset = 0;
        $cc->timecreated = $certificate->timecreated;
        $cc->timemodified = $certificate->timemodified;
        $cc->intro = $certificate->intro;
        $cc->introformat = $certificate->introformat;
        $cc->id = $DB->insert_record('coursecertificate', $cc);

        $cm->module = $newmodule->id;
        $cm->instance = $cc->id;
        $DB->update_record('course_modules', $cm);
        cache_helper::purge_all();

        $files = $fs->get_area_files($context->id, 'mod_certificate', 'intro');
        foreach ($files as $file) {
            if ($file->get_filename() === '.') {
                continue;
            }
            $newfile = [
                'contextid' => $context->id,
                'component' => 'mod_coursecertificate',
                'filearea'  => 'intro',
            ];
            $fs->create_file_from_storedfile($newfile, $file);
        }
        $fs->delete_area_files($context->id, 'mod_certificate', 'intro');

        $issues = $DB->get_records('certificate_issues', ['certificateid' => $certificate->id]);
        foreach ($issues as $issue) {
            $user = $DB->get_record('user', ['id' => $issue->userid, 'deleted' => 0]);
            if ($user) {
                $ci = new stdClass();
                $ci->userid = $user->id;
                $ci->templateid = $template->id;
                $ci->code = $issue->code;
                $ci->emailed = ($certificate->delivery == 2) ? 1 : 0;
                $ci->timecreated = $issue->timecreated;
                $ci->expires = null;
                $ci->data = null;
                $ci->component = 'mod_coursecertificate';
                $ci->courseid = $course->id;
                $ci->archived = 0;
                $ci->id = $DB->insert_record('tool_certificate_issues', $ci);

                $files = $fs->get_area_files($context->id, 'mod_certificate', 'issue', $issue->id, 'timecreated', false);
                if ($files) {
                    $file = reset($files);
                    $newfile = [
                        'contextid' => $syscontext->id,
                        'component' => 'tool_certificate',
                        'filearea'  => 'issues',
                        'itemid'    => $ci->id,
                        'filepath'  => '/',
                        'filename'  => $ci->code . '.pdf'
                    ];
                    $fs->create_file_from_storedfile($newfile, $file);
                } else {
                    debugging("Missing pdf in certificate $certificate->id for user $user->id", DEBUG_DEVELOPER);
                }
                $fs->delete_area_files($context->id, 'mod_certificate', 'issue', $issue->id);
            }
            $DB->delete_records('certificate_issues', ['id' => $issue->id]);
        }
        $DB->delete_records('certificate', ['id' => $certificate->id]);

        $trans->allow_commit();
    }
}
purge_all_caches();

// Optionally uninstall.
if ($options['uninstall']) {
    if ($DB->record_exists('certificate', [])) {
        cli_error('Plugin mod_certificate cannot be uninstalled because there are still some activities.');
    }
    cli_writeln('Uninstalling mod_certificate:');
    uninstall_plugin('mod', 'certificate');
    cli_writeln('...finished, do not forget to delete mod/certificate/* directory.');
}

cli_separator();
cli_writeln('mod_certificate migration was completed.');

exit(0);