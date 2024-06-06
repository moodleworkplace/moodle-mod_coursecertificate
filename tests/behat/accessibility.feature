@mod @mod_coursecertificate @moodleworkplace @javascript @accessibility
Feature: Test accessibility for the course certificate module
  In order to test accessibility
  As a teacher
  I should be able to pass all accessibility tests

  Background:
    Given the site is running Moodle version 4.1 or higher
    And the following "users" exist:
      | username  | firstname | lastname  | email                 | country |
      | teacher1  | Teacher   | 01        | teacher01@example.com | ES      |
      | student1  | Student   | 01        | student01@example.com | ES      |
      | student2  | Student   | 02        | student02@example.com | FR      |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following certificate templates exist:
      | name        | shared  |
      | Template 01 | 1       |
    And the following certificate issues exist:
      | template    | user      | course | component             | code  | timecreated |
      | Template 01 | student1  | C1     | mod_coursecertificate | code1 | 1009882800  |
      | Template 01 | student2  | C1     | mod_coursecertificate |       |             |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template    |
      | coursecertificate | My certificate | Certificate intro | C1     | coursecertificate1 | Template 01 |
    # This setting is not related to the component, but makes the accessibility test fail. MDL-81241
    And the following config values are set as admin:
      | supportavailability | 0 |

  Scenario: View the issued certificates list
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 01           | student01@example.com |
      | Student 02           | student02@example.com |
    Then the page should meet "wcag131, wcag134, wcag141, wcag143, wcag21aa, wcag21a, wcag412" accessibility standards
