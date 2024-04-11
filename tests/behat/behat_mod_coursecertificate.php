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
 * mod_coursecertificate steps definitions.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Mink\Exception\ExpectationException;
use Moodle\BehatExtension\Exception\SkippedException;

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
     * Check that the manual completion button for the activity is disabled.
     *
     * @Given /^the manual completion button for "(?P<activityname>(?:[^"]|\\")*)" course certificate should be disabled$/
     * @param string $activityname The activity name.
     */
    public function the_manual_completion_button_for_activity_coursecertificate_should_be_disabled(string $activityname): void {
        global $CFG;
        // Execute only on Moodle 3.11 and above. Skip for previous versions.
        if ($CFG->version < 2021050700) {
            throw new SkippedException('Moodle version is too low.');
        }
        $this->execute("behat_completion::the_manual_completion_button_for_activity_should_be_disabled", [$activityname]);
    }

    /**
     * Check that the activity has the given automatic completion condition.
     *
     * phpcs:ignore
     * @Given /^"(?P<activityname>(?:[^"]|\\")*)" course certificate should have the "(?P<conditionname>(?:[^"]|\\")*)" completion condition$/
     * @param string $activityname The activity name.
     * @param string $conditionname The automatic condition name.
     */
    public function activity_coursecertificate_should_have_the_completion_condition(string $activityname,
                                                                                    string $conditionname): void {
        global $CFG;
        // Execute only on Moodle 3.11 and above. Skip for previous versions.
        if ($CFG->version < 2021050700) {
            throw new SkippedException('Moodle version is too low.');
        }
        $this->execute("behat_completion::activity_should_have_the_completion_condition", [$activityname, $conditionname]);
    }

    /**
     * Step to open current course or activity settings page (language string changed between 3.11 and 4.0)
     *
     * @When /^I open course or activity settings page$/
     * @return void
     */
    public function i_open_course_or_activity_settings_page(): void {
        global $CFG;
        if ($CFG->version < 2022012100) {
            $this->execute("behat_navigation::i_navigate_to_in_current_page_administration", ['Edit settings']);
        } else {
            $this->execute("behat_navigation::i_navigate_to_in_current_page_administration", ['Settings']);
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

    /**
     * Opens the activity chooser and opens the activity/resource form page. Sections 0 and 1 are also allowed on frontpage.
     *
     * @Given I add a new instance of coursecertificate module to course :coursefullname section :sectionnum
     * @param string $coursefullname
     * @param int $section
     */
    public function i_add_a_new_instance_of_coursecertificate_module_to_course_section($coursefullname, $section) {
        // Note, this step duplicates `behat_course::i_add_to_course_section` because mod_coursecertificate
        // still supports Moodle 4.0 where it is not available.
        $this->execute('behat_navigation::i_am_on_course_homepage_with_editing_mode_set_to',
            [$coursefullname, 'on']);
        $addurl = new moodle_url('/course/modedit.php', [
            'add' => 'coursecertificate',
            'course' => $this->get_course_id($coursefullname),
            'section' => intval($section),
        ]);
        $this->execute('behat_general::i_visit', [$addurl]);
    }
}
