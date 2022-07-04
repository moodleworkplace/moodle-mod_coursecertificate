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
      | activity          | name           | intro             | course | idnumber           | template    | groupmode  |
      | coursecertificate | My certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |

  Scenario: Get certificate having the activity requirements when accessing the activity
    Then I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I press the "back" button in the browser
    And I click on ".popover-region-notifications" "css_element"
    And I should see "Your certificate is available!"

  Scenario: Teacher should not get certificate when accessing the activity
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I click on ".popover-region-notifications" "css_element"
    And I should not see "Your certificate is available!"

  Scenario: User can receive new course certificate when they have archived ones
    Given the following certificate issues exist:
      | template    | user      | course | component             | code  | timecreated | archived |
      | Template 01 | student1  | C1     | mod_coursecertificate | code1 | 1009882800  | 1        |
      | Template 01 | student1  | C1     | mod_coursecertificate | code2 | 1041415200  | 1        |
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I press the "back" button in the browser
    And I follow "Profile" in the user menu
    And I click on "//a[contains(.,'My certificates') and contains(@href,'tool/certificate')]" "xpath_element"
    And the following should exist in the "generaltable" table:
      | Certificate | Date issued         |
      | Template 01 | ##today##%d %B %Y## |
      | Template 01 | 1 January 2003      |
      | Template 01 | 1 January 2002      |
