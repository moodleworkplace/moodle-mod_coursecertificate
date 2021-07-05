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
 * The external API for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * The external class for the Coursecertificate module.
 *
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends \external_api {
    /**
     * Returns the structure of parameters for update_automaticsend function.
     * @return \external_function_parameters
     */
    protected static function update_automaticsend_parameters() {
        $params = [
            'id' => new \external_value(PARAM_INT, 'The ID of the certificate', VALUE_REQUIRED),
            'automaticsend' => new \external_value(PARAM_BOOL, 'The value of automaticsend setting')
        ];
        return new \external_function_parameters($params);
    }

    /**
     * Update automaticsend setting value.
     *
     * @param int $id
     * @param bool $automaticsend
     * @return array
     */
    public static function update_automaticsend(int $id, bool $automaticsend) {
        global $DB;

        $params = self::validate_parameters(self::update_automaticsend_parameters(),
            ['id' => $id, 'automaticsend' => $automaticsend]);

        $certificate = $DB->get_record('coursecertificate', ['id' => $params['id']], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('coursecertificate', $certificate->id);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        if (permission::can_manage($context)) {
            $certificate->automaticsend = $params['automaticsend'];
            if ($DB->update_record('coursecertificate', $certificate)) {
                \core\event\course_module_updated::create_from_cm($cm, $context)->trigger();
                return [
                    'showhiddenwarning' => $certificate->automaticsend && !$cm->visible,
                    'shownoautosendinfo' => !$certificate->automaticsend && $cm->visible,
                ];
            }
        }
        return [
            'showhiddenwarning' => false,
            'shownoautosendinfo' => false,
        ];
    }

    /**
     * Describes the return function of update_certificate_automaticsend
     *
     * @return \external_single_structure
     */
    public static function update_automaticsend_returns() {
        return new \external_single_structure([
            'showhiddenwarning' => new \external_value(PARAM_BOOL, 'Desc'),
            'shownoautosendinfo' => new \external_value(PARAM_BOOL, 'Desc'),
        ]);
    }
}
