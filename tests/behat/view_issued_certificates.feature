@mod @mod_coursecertificate @javascript
Feature: View the certificates that have been issued
  In order to view the certificates that have been issued
  As a teacher
  I need to view the certificates issues list

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
      | student4 | Student   | 4        | student4@example.com |
      | student5 | Student   | 5        | student5@example.com |
      | student6 | Student   | 6        | student6@example.com |
      | student7 | Student   | 7        | student7@example.com |
      | student8 | Student   | 8        | student8@example.com |
      | student9 | Student   | 9        | student9@example.com |
      | student10 | Student   | 10        | student10@example.com |
      | student11 | Student   | 11        | student11@example.com |
      | manager1 | Manager   | 1        | manager1@example.com |
    And the following "courses" exist:
      | fullname | shortname | groupmode  |
      | Course 1 | C1        | 1          |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | manager1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
      | student6 | C1     | student        |
      | student7 | C1     | student        |
      | student8 | C1     | student        |
      | student9 | C1     | student        |
      | student10 | C1     | student        |
      | student11 | C1     | student        |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | G1       |
      | Group 2 | C1     | G2       |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |
      | student4 | G2 |
    And the following "roles" exist:
      | shortname              | name                       | archetype |
      | certificateissuer      | Certificate issuer         |           |
      | certificateverifier    | Certificate verifier       |           |
    And the following "role assigns" exist:
      | user     | role                | contextlevel | reference |
      | manager1 | certificateissuer   | System       |           |
      | teacher1 | certificateverifier | System       |           |
    And the following "permission overrides" exist:
      | capability                     | permission | role                 | contextlevel | reference |
      | tool/certificate:issue         | Allow      | certificateissuer    | System       |           |
      | tool/certificate:verify        | Allow      | certificateverifier  | System       |           |
    And the following certificate templates exist:
      | name        | shared  |
      | Template 01 | 1       |
    And the following certificate issues exist:
      | template                      | user      | course |
      | Template 01  | student1  | C1     |
      | Template 01  | student2  | C1     |
      | Template 01  | student3  | C1     |
      | Template 01  | student4  | C1     |
      | Template 01  | student5  | C1     |
      | Template 01  | student6  | C1     |
      | Template 01  | student7  | C1     |
      | Template 01  | student8  | C1     |
      | Template 01  | student9  | C1     |
      | Template 01  | student10  | C1     |
      | Template 01  | student11  | C1     |
    And the following "activities" exist:
      | activity          | name        | intro             | course | idnumber           | template    | groupmode  |
      | coursecertificate | Certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |

  Scenario: View the issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I should see "student1@example.com"
    And I set the field "Separate groups" to "Group 1"
    And I should not see "student3@example.com"
    And I set the field "Separate groups" to "All participants"
    And I click on "Surname" "link" in the "generaltable" "table"
    And I should not see "student9@example.com"
    And I should see "student11@example.com"
    And I click on "2" "link" in the ".pagination" "css_element"
    And I should see "student9@example.com"

  Scenario: Preview issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on "View" "link" in the "student1@example.com" "table_row"

  Scenario: Remove issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I should see "student1@example.com"
    And I click on "Revoke" "link" in the "student1@example.com" "table_row"
    And I press "Confirm"
    And I should not see "student1@example.com"

  Scenario: Verify issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on "Verify" "link" in the "student1@example.com" "table_row"