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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Mink\Exception\ExpectationException;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions for mod_coursecertificate.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_coursecertificate extends behat_base {

    /**
     * Convert page names to URLs for steps like 'When I am on the "[identifier]" "[page type]" page'.
     *
     * @param string $type
     * @param string $identifier
     * @return moodle_url
     * @throws Exception
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        switch ($type) {
            case 'Index':
                return new moodle_url('/mod/coursecertificate/index.php', ['id' => $this->get_course_id($identifier)]);
            default:
                throw new Exception("Unrecognised page type '{$type}'");
        }
    }

    /**
     * Check that the manual completion button for the activity exists a number of times.
     *
     * @Given the manual completion button for :activityname course certificate should be displayed :times times
     *
     * @param string $activityname The activity name.
     * @param int $times The number of appearances.
     */
    public function the_manual_completion_button_for_activity_coursecertificate_should_be_displayed_times(string $activityname,
                                                                                                          int $times): void {
        $selector = "div[data-activityname='$activityname'] button";
        $count = count($this->find_all('css',  $selector));
        if ($count != $times) {
            // The button appears a different number of times.
            throw new ExpectationException(
                "The manual completion button for '{$activityname}' exists '{$count}' times",
                $this->getSession()
            );
        }
    }
}
