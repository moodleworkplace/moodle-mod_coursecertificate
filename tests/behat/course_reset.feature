@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: Course reset with coursecertificate module
  In order to re-issue certificates
  As a teacher
  I need to be able to archive existing ones.

  Background:
    Given the following certificate templates exist:
      | name        | shared  |
      | Template 01 | 1       |
    And the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | First    | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  Scenario: Course reset defaults for coursecertificate module
    Given the following "activities" exist:
      | activity          | name             | course | idnumber | template    |
      | coursecertificate | Test certificate | C1     | ccert    | Template 01 |
    When I log in as "teacher1"
    And I am on the "Course 1" "reset" page
    And I press "Select default"
    And I expand all fieldsets
    And the field "Archive issued certificates" matches value "1"

  Scenario: Course reset of coursecertificate module in Moodle 4.4 or lower
    Given the site is running Moodle version 4.4 or lower
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Second   | student2@example.com |
      | student3 | Student   | Third    | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "activities" exist:
      | activity          | name             | course | idnumber | template    | completion |
      | coursecertificate | Test certificate | C1     | ccert    | Template 01 | 1          |
    And the following certificate issues exist:
      | template    | user      | course | component             | code  | timecreated |
      | Template 01 | student1  | C1     | mod_coursecertificate | code1 | 1009882800  |
      | Template 01 | student2  | C1     | mod_coursecertificate |       | 1009969200  |
    When I log in as "student1"
    And I am on "Course 1" course homepage
    When I toggle the manual completion state of "Test certificate"
    Then the manual completion button of "Test certificate" is displayed as "Done"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test certificate"
    And the following should exist in the "generaltable" table:
      | First name     | Email address        | Expiry date | Date issued    |
      | Student Second | student2@example.com | Never       | 2 January 2002 |
      | Student First  | student1@example.com | Never       | 1 January 2002 |
    And I should not see "Archived" in the ".generaltable" "css_element"
    When I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Activity completion" "link"
    And "Completed" "icon" should exist in the "Student First" "table_row"
    And "Completed" "icon" should not exist in the "Student Second" "table_row"
    And "Completed" "icon" should not exist in the "Student Third" "table_row"
    And I am on the "Course 1" "reset" page
    And I set the following fields to these values:
      | Archive issued certificates | 1 |
      | Delete completion data      | 1 |
    And I press "Reset course"
    And I press "Continue"
    When I am on "Course 1" course homepage
    And I follow "Test certificate"
    And I should see "Archived" in the "student1@example.com" "table_row"
    And I should see "Archived" in the "student2@example.com" "table_row"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Activity completion" "link"
    And "Completed" "icon" should not exist in the "Student First" "table_row"

  Scenario: Course reset of coursecertificate module
    Given the site is running Moodle version 4.5 or higher
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | student2 | Student   | Second   | student2@example.com |
      | student3 | Student   | Third    | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "activities" exist:
      | activity          | name             | course | idnumber | template    | completion |
      | coursecertificate | Test certificate | C1     | ccert    | Template 01 | 1          |
    And the following certificate issues exist:
      | template    | user      | course | component             | code  | timecreated |
      | Template 01 | student1  | C1     | mod_coursecertificate | code1 | 1009882800  |
      | Template 01 | student2  | C1     | mod_coursecertificate |       | 1009969200  |
    When I log in as "student1"
    And I am on "Course 1" course homepage
    When I toggle the manual completion state of "Test certificate"
    Then the manual completion button of "Test certificate" is displayed as "Done"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test certificate"
    And the following should exist in the "generaltable" table:
      | First name     | Email address        | Expiry date | Date issued    |
      | Student Second | student2@example.com | Never       | 2 January 2002 |
      | Student First  | student1@example.com | Never       | 1 January 2002 |
    And I should not see "Archived" in the ".generaltable" "css_element"
    When I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Activity completion" "link"
    And "Completed" "icon" should exist in the "Student First" "table_row"
    And "Completed" "icon" should not exist in the "Student Second" "table_row"
    And "Completed" "icon" should not exist in the "Student Third" "table_row"
    And I am on the "Course 1" "reset" page
    And I set the following fields to these values:
      | Archive issued certificates | 1        |
      | Completion data             | 1        |
      | All grades                  | 0        |
      | All local role assignments  | 0        |
      | Unenrol users               | No roles |
    And I press "Reset course"
    And I click on "Reset course" "button" in the "Reset course?" "dialogue"
    When I am on "Course 1" course homepage
    And I follow "Test certificate"
    And I should see "Archived" in the "student1@example.com" "table_row"
    And I should see "Archived" in the "student2@example.com" "table_row"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Activity completion" "link"
    And "Completed" "icon" should not exist in the "Student First" "table_row"
