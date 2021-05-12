@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: Completion in the course certificate activity
  To avoid navigating from the course certificate to the course homepage to see the course certificate activity information
  As a teacher
  I need to be able to see the course certificate activity in the course certificate activity itself

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | teacher1 | Teacher   | 1        | teacher1@example.com  |
      | student1 | Student   | 1        | student1@example.com  |
    And the following "course" exists:
      | fullname          | Course 1  |
      | shortname         | C1        |
      | category          | 0         |
      | enablecompletion  | 1         |
    And the following certificate templates exist:
      | name                         | shared  |
      | Template 01                  | 1       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |

  Scenario: Viewing a course certificate activity with manual completion as a teacher
    And the following "activities" exist:
      | activity          | name          | intro             | course | idnumber           | template    | completion |
      | coursecertificate | Certificate 1 | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I follow "Certificate 1"
    Then the manual completion button for "Certificate 1" course certificate should be disabled

  Scenario: Viewing a course certificate activity with automatic completion as a teacher
    And the following "activities" exist:
      | activity          | name          | intro             | course | idnumber           | template    | completion | completionview |
      | coursecertificate | Certificate 2 | Certificate intro | C1     | coursecertificate2 | Template 01 | 2          | 1              |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I follow "Certificate 2"
    Then "Certificate 2" course certificate should have the "View" completion condition
