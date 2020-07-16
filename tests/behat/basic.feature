@mod @mod_coursecertificate @javascript
Feature: Basic functionality of course certificate module
  In order to issue certificates in a course
  As a teacher
  I need to be able to create instances of course certificate module

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

  Scenario: Teacher can create an instance of course certificate module
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Course certificate" to section "1"
    And I click on "Template" "select"
    And I should not see "Certificate of completion"
    And I set the following fields to these values:
      | Name      | Your awesome certificate     |
      | Template  | Certificate of participation |
    And I press "Save and display"
    And I should see "Your awesome certificate"
    And I should see "The automatic sending of this certificate is disabled"
    And I should see "No users are certified."
    And I press "Enable"
    And I press "Confirm"
    And I should see "The automatic sending of this certificate is enabled"
    And I log out

  Scenario: Manager can create an instance of course certificate module with non shared templates
    And I log in as "manager1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Course certificate" to section "1" and I fill the form with:
      | Name     | Your awesome certificate  |
      | Template | Certificate of completion |
    And I follow "Your awesome certificate"
    And I should see "Your awesome certificate"
    And I should see "The automatic sending of this certificate is disabled"
    And I should see "No users are certified."
    And I log out

  Scenario: Teacher can not change course certificate template if it has been issued
    And the following certificate issues exist:
      | template                      | user      | course |
      | Certificate of participation  | student1  | C1     |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Course certificate" to section "1" and I fill the form with:
      | Name     | Your awesome certificate     |
      | Template | Certificate of participation |
    And I follow "Your awesome certificate"
    And I should see "Student 1"
    And I click on "Actions menu" "link"
    And I click on "Edit settings" "link"
    And the "Template" "select" should be disabled

  Scenario: Teacher can revoke a certificate
    And the following certificate issues exist:
      | template                      | user      | course |
      | Certificate of participation  | student1  | C1     |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Course certificate" to section "1" and I fill the form with:
      | Name     | Your awesome certificate     |
      | Template | Certificate of participation |
    And I follow "Your awesome certificate"
    And I should see "Student 1"
    And I click on "Revoke" "link"
    And I press "Confirm"
    And I should see "No users are certified."
