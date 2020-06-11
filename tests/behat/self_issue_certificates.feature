@mod @mod_coursecertificate @javascript
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
      | manager1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "roles" exist:
      | shortname            | name                       | archetype |
      | certificateissuer    | Certificate issuer         |           |
    And the following "role assigns" exist:
      | user     | role              | contextlevel | reference |
      | manager1 | certificateissuer | System       |           |
    And the following "permission overrides" exist:
      | capability                     | permission | role                 | contextlevel | reference |
      | tool/certificate:issue         | Allow      | certificateissuer    | System       |           |
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
      | Certificate of completion    | 0       |

  Scenario: Get certificate having the activity requirements
  # TODO.
