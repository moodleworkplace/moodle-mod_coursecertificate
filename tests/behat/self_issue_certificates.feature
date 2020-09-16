@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: Self issue certificate for coursecertificate template
  In order to get a certificate issue
  As a student
  I need to access the course certificate issued module

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | manager1 | Manager   | 1        | manager1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following certificate templates exist:
      | name                         | shared  |
      | Template 01                  | 1       |
    And the following "activities" exist:
      | activity          | name        | intro             | course | idnumber           | template    | groupmode  |
      | coursecertificate | Certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |

  Scenario: Get certificate having the activity requirements when accessing the activity
    Then I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I press the "back" button in the browser
    And I click on ".popover-region-notifications" "css_element"
    And I should see "Your certificate is available!"

  Scenario: Teacher should not get certificate when accessing the activity
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on ".popover-region-notifications" "css_element"
    And I should not see "Your certificate is available!"
