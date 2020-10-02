@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: View the certificates that have been issued
  In order to view the certificates that have been issued
  As a teacher
  I need to view the certificates issues list

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 01        | teacher01@example.com |
      | teacher2 | Teacher   | 02        | teacher02@example.com |
      | student1 | Student   | 01        | student01@example.com |
      | student2 | Student   | 02        | student02@example.com |
      | student3 | Student   | 03        | student03@example.com |
      | student4 | Student   | 04        | student04@example.com |
      | student5 | Student   | 05        | student05@example.com |
      | student6 | Student   | 06        | student06@example.com |
      | student7 | Student   | 07        | student07@example.com |
      | student8 | Student   | 08        | student08@example.com |
      | student9 | Student   | 09        | student09@example.com |
      | student10 | Student   | 10        | student10@example.com |
      | student11 | Student   | 11        | student11@example.com |
      | manager1 | Manager   | 1        | manager1@example.com |
    And the following "courses" exist:
      | fullname | shortname | groupmode  |
      | Course 1 | C1        | 1          |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | teacher         |
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
      | Group 3 | C1     | G3       |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | teacher2 | G2 |
      | student3 | G2 |
      | student4 | G2 |
      | teacher2 | G3 |
      | student5 | G3 |
    And the following "roles" exist:
      | shortname              | name                       | archetype |
      | certificateissuer      | Certificate issuer         |           |
    And the following "role assigns" exist:
      | user     | role                | contextlevel | reference |
      | manager1 | certificateissuer   | System       |           |
    And the following "permission overrides" exist:
      | capability                     | permission | role                 | contextlevel | reference |
      | tool/certificate:issue         | Allow      | certificateissuer    | System       |           |
    And the following certificate templates exist:
      | name        | shared  |
      | Template 01 | 1       |
    And the following certificate issues exist:
      | template                      | user      | course | component             |
      | Template 01                   | student1  | C1     | mod_coursecertificate |
      | Template 01                   | student2  | C1     | mod_coursecertificate |
      | Template 01                   | student3  | C1     | mod_coursecertificate |
      | Template 01                   | student4  | C1     | mod_coursecertificate |
      | Template 01                   | student5  | C1     | mod_coursecertificate |
      | Template 01                   | student6  | C1     | mod_coursecertificate |
      | Template 01                   | student7  | C1     | mod_coursecertificate |
      | Template 01                   | student8  | C1     | mod_coursecertificate |
      | Template 01                   | student9  | C1     | mod_coursecertificate |
      | Template 01                   | student10  | C1     | mod_coursecertificate |
      | Template 01                   | student11  | C1     | mod_coursecertificate |
    And the following "activities" exist:
      | activity          | name        | intro             | course | idnumber           | template    | groupmode  |
      | coursecertificate | Certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |

  Scenario: View the issued certificates list
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    # Test group filtering.
    And I set the field "Separate groups" to "Group 1"
    And I should see "student01@example.com"
    And I should see "student02@example.com"
    And I should not see "student03@example.com"
    And I set the field "Separate groups" to "All participants"
    And I should see "student03@example.com"
    # Test sorting.
    And I click on "Email address" "link" in the "generaltable" "table"
    And I should not see "student01@example.com"
    # Test pagination.
    And I click on "2" "link" in the ".pagination" "css_element"
    And I should see "student01@example.com"

  Scenario: View the issued certificates list as non-editing teacher and separate/visible groups
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I should not see "student01@example.com"
#    And I click on "Separate groups" "field"
    And "Group 1" "option" should not exist in the "Separate groups" "select"
    And "Group 2" "option" should exist in the "Separate groups" "select"
    And I select "Group 3" from the "Separate groups" singleselect
    And I should not see "student03@example.com"
    And I should see "student05@example.com"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "Certificate" actions menu
    And I choose "Edit settings" in the open action menu
    And I expand all fieldsets
    And I set the field "Group mode" to "Visible groups"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I should not see "student01@example.com"
    And "Group 1" "option" should not exist in the "Separate groups" "select"
    And "Group 2" "option" should exist in the "Separate groups" "select"
    And I select "Group 3" from the "Separate groups" singleselect
    And I should not see "student03@example.com"
    And I should see "student05@example.com"

  Scenario: View issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And I click on "View" "link" in the "student06@example.com" "table_row"

  Scenario: Remove issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And I should see "student06@example.com"
    And I click on "Revoke" "link" in the "student06@example.com" "table_row"
    And I press "Confirm"
    And I should not see "student06@example.com"

  Scenario: Verify issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And I click on "Verify" "link" in the "student06@example.com" "table_row"

  Scenario: Download issued certificates list
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Certificate"
    And I press "Download"
    And I log out
