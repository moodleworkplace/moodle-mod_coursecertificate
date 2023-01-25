@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: View the certificates that have been issued
  In order to view the certificates that have been issued
  As a teacher
  I need to view the certificates issues list

  Background:
    Given the following "users" exist:
      | username  | firstname | lastname  | email                 | country |
      | teacher1  | Teacher   | 01        | teacher01@example.com | ES      |
      | teacher2  | Teacher   | 02        | teacher02@example.com | FR      |
      | student1  | Student   | 01        | student01@example.com | ES      |
      | student2  | Student   | 02        | student02@example.com | FR      |
      | student3  | Student   | 03        | student03@example.com | ES      |
      | student4  | Student   | 04        | student04@example.com | PT      |
      | student5  | Student   | 05        | student05@example.com | ES      |
      | student6  | Student   | 06        | student06@example.com | ES      |
      | student7  | Student   | 07        | student07@example.com | ES      |
      | student8  | Student   | 08        | student08@example.com | ES      |
      | student9  | Student   | 09        | student09@example.com | ES      |
      | student10 | Student   | 10        | student10@example.com | ES      |
      | student11 | Student   | 11        | student11@example.com | ES      |
      | manager1  | Manager   | 1         | manager1@example.com  | ES      |
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
      | template    | user      | course | component             | code  | timecreated |
      | Template 01 | student1  | C1     | mod_coursecertificate | code1 | 1009882800  |
      | Template 01 | student2  | C1     | mod_coursecertificate |       | 1009969200  |
      | Template 01 | student3  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student4  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student5  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student6  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student7  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student8  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student9  | C1     | mod_coursecertificate |       |             |
      | Template 01 | student10 | C1     | mod_coursecertificate |       |             |
      | Template 01 | student11 | C1     | mod_coursecertificate |       |             |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template    | groupmode  |
      | coursecertificate | My certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1          |
    And the following config values are set as admin:
      | showuseridentity | email,country |

  Scenario: View the issued certificates list
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    # Ensure consistent sort by email address.
    And I click on "Email address" "link" in the "generaltable" "table"
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         | Country   | Status | Expiry date | Date issued         |
      | Student 01           | student01@example.com | Spain     | Valid  | Never       | 1 January 2002      |
      | Student 02           | student02@example.com | France    | Valid  | Never       | 2 January 2002      |
      | Student 03           | student03@example.com | Spain     | Valid  | Never       | ##today##%d %B %Y## |
      | Student 04           | student04@example.com | Portugal  | Valid  | Never       | ##today##%d %B %Y## |

  Scenario: Filter issued certificates by group
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I set the field "Separate groups" to "Group 1"
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 01           | student01@example.com |
      | Student 02           | student02@example.com |
    And the following should not exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 03           | student03@example.com |
    And I set the field "Separate groups" to "All participants"
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 03           | student03@example.com |

  Scenario: View the issued certificates list as non-editing teacher and separate/visible groups
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I should not see "student01@example.com"
    And the "Separate groups" select box should not contain "Group 1"
    And the "Separate groups" select box should contain "Group 2"
    And I select "Group 3" from the "Separate groups" singleselect
    And the following should not exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 03           | student03@example.com |
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 05           | student05@example.com |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open "My certificate" actions menu
    And I choose "Edit settings" in the open action menu
    And I expand all fieldsets
    And I set the field "Group mode" to "Visible groups"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And the following should not exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 01           | student01@example.com |
    And the "Separate groups" select box should not contain "Group 1"
    And the "Separate groups" select box should contain "Group 2"
    And I select "Group 3" from the "Separate groups" singleselect
    And the following should not exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 03           | student03@example.com |
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 05           | student05@example.com |

  Scenario: View issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And I press "View" action in the "student06@example.com" report row

  Scenario: Remove issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And the following should exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 06           | student06@example.com |
    And I press "Revoke" action in the "student06@example.com" report row
    And I click on "Revoke" "button" in the "Confirm" "dialogue"
    And the following should not exist in the "generaltable" table:
      | First name / Surname | Email address         |
      | Student 06           | student06@example.com |

  Scenario: Verify issued certificates
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I click on "Email address" "link" in the "generaltable" "table"
    And I click on "Verify" "link" in the "student06@example.com" "table_row"

  Scenario: Download issued certificates list
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I press "Download"
    And I log out

  Scenario: View archived certificates
    Given the following certificate issues exist:
      | template    | user     | course | component             | archived | code  | timecreated |
      | Template 01 | student1 | C1     | mod_coursecertificate | 1        | code2 | 946724400   |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I should see "Archived" in the "code2" "table_row"
    And I log out
    When I log in as "student1"
    And I follow "Profile" in the user menu
    And I click on "//a[contains(.,'My certificates') and contains(@href,'tool/certificate')]" "xpath_element"
    And the following should exist in the "generaltable" table:
      | Certificate            | Code  |
      | Template 01 - Course 1 | code1 |
      | Template 01 - Course 1 | code2 |
