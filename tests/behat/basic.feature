@mod @mod_coursecertificate @moodleworkplace @javascript
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

  @_switch_window
  Scenario: Teacher can create an instance of course certificate module
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
      | Certificate of completion    | 0       |
    When I log in as "teacher1"
    Then I add a new instance of coursecertificate module to course "Course 1" section "1"
    And "Manage certificate templates" "link" should not exist
    And I click on "Template" "select"
    And I should not see "Certificate of completion"
    And I click on "Expiry date type" "select"
    And I should see "Select date"
    And I should see "After"
    And I set the following fields to these values:
      | Name              | Your awesome certificate      |
      | Template          | Certificate of participation  |
      | Expiry date type  | Never                         |
    And I press "Save and display"
    And I should see "Your awesome certificate"
    And I should see "The automatic sending of this certificate is disabled"
    And I should see "Nothing to display"
    And I press "Enable"
    And I press "Confirm"
    And I should see "The automatic sending of this certificate is enabled"
    And I open course or activity settings page
    And I set the following fields to these values:
      | Name      | Your super awesome certificate     |
    And I press "Save and display"
    And I should see "Your super awesome certificate"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Your super awesome certificate" "link" in the "region-main" "region"
    And I switch to the main window
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Your super awesome certificate" "link" in the "region-main" "region"
    Then the following should exist in the "generaltable" table:
      | First name | Status | Expiry date |
      | Student 1  | Valid  | Never       |

  @_switch_window
  Scenario: Teacher can create an instance of course certificate module with expiry date absolute
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    When I log in as "teacher1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name              | Your awesome certificate     |
      | Template          | Certificate of participation |
      | Expiry date type  | Select date                  |
      | Day               | ##tomorrow##%d##             |
      | Month             | ##tomorrow##%B##             |
      | Year              | ##tomorrow##%Y##             |
    And I press "Save and display"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Your awesome certificate" "link" in the "region-main" "region"
    And I switch to the main window
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Your awesome certificate" "link" in the "region-main" "region"
    Then the following should exist in the "generaltable" table:
      | First name | Status | Expiry date            |
      | Student 1  | Valid  | ##tomorrow##%d %B %Y## |

  @_switch_window
  Scenario: Teacher can create an instance of course certificate module with expiry date relative
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    When I log in as "teacher1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name              | Your awesome certificate     |
      | Template          | Certificate of participation |
      | Expiry date type  | After                        |
      | Time              | 1                            |
      | Time unit         | weeks                        |
    And I press "Save and display"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Your awesome certificate" "link" in the "region-main" "region"
    And I switch to the main window
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Your awesome certificate" "link" in the "region-main" "region"
    Then the following should exist in the "generaltable" table:
      | First name | Status | Expiry date           |
      | Student 1  | Valid  | ##+1 week##%d %B %Y## |

  Scenario: Teacher can duplicate and delete an instance of course certificate module
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    And the following "activities" exist:
      | activity          | name        | intro             | course | idnumber           | template                     |
      | coursecertificate | Certificate | Certificate intro | C1     | coursecertificate1 | Certificate of participation |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I duplicate "Certificate" activity
    And I wait until "Certificate (copy)" "link" exists
    And I delete "Certificate (copy)" activity
    Then I should not see "Certificate (copy)" in the "region-main" "region"

  Scenario: Manager can create an instance of course certificate module with non shared templates
    And the following "permission overrides" exist:
      | capability                      | permission | role                 | contextlevel | reference |
      | tool/certificate:manage         | Allow      | certificateissuer    | System       |           |
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
      | Certificate of completion    | 0       |
    When I log in as "manager1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    Then "Manage certificate templates" "link" should exist
    And I set the following fields to these values:
      | Name     | Your awesome certificate  |
      | Template | Certificate of completion |
    And I press "Save and display"
    And I should see "Your awesome certificate"
    And I should see "The automatic sending of this certificate is disabled"
    And I should see "Nothing to display"

  Scenario: Teacher can not create course certificate if there are not available templates
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of completion    | 0       |
    When I log in as "teacher1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    Then I should see "There are no available templates. Please contact the site administrator."
    And I press "Save and display"
    And I should see "You must supply a value here."

  Scenario: Manager can not create course certificate if there are not available templates
    And the following "permission overrides" exist:
      | capability                      | permission | role                 | contextlevel | reference |
      | tool/certificate:manage         | Allow      | certificateissuer    | System       |           |
    When I log in as "manager1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    Then I should see "There are no available templates. Please go to certificate template management page and create a new one."
    And I press "Save and display"
    And I should see "You must supply a value here."
    And "certificate template management page" "link" should exist in the ".alert-warning" "css_element"

  Scenario: Teacher can not change course certificate template if it has been issued
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    And the following certificate issues exist:
      | template                      | user      | course | component             |
      | Certificate of participation  | student1  | C1     | mod_coursecertificate |
    When I log in as "teacher1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name     | Your awesome certificate     |
      | Template | Certificate of participation |
    And I press "Save and return to course"
    And I click on "Your awesome certificate" "link" in the "region-main" "region"
    Then I should see "Student 1"
    And I open course or activity settings page
    And the "Template" "select" should be disabled

  Scenario: Teacher can revoke a certificate
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    And the following certificate issues exist:
      | template                      | user      | course | component             |
      | Certificate of participation  | student1  | C1     | mod_coursecertificate |
    When I log in as "teacher1"
    And I add a new instance of coursecertificate module to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name     | Your awesome certificate     |
      | Template | Certificate of participation |
    And I press "Save and return to course"
    Then I click on "Your awesome certificate" "link" in the "region-main" "region"
    And I press "Revoke" action in the "Student 1" report row
    And I click on "Revoke" "button" in the "Confirm" "dialogue"
    And I should see "Nothing to display"

  Scenario: Teacher can manage blocks in the module page
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template                     |
      | coursecertificate | Certificate 01 | Certificate intro | C1     | coursecertificate1 | Certificate of participation |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Certificate 01" "link" in the "region-main" "region"
    And I add the "Tags" block
    And I configure the "Tags" block
    And I set the following fields to these values:
      | Tags block title  | This is my block  |
    And I press "Save changes"
    Then I should see "This is my block"

  Scenario: Display information about all coursecertificate activities
    And the following certificate templates exist:
      | name                         | shared  |
      | Certificate of participation | 1       |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template                     |
      | coursecertificate | Certificate 01 | Certificate intro | C1     | coursecertificate1 | Certificate of participation |
      | coursecertificate | Certificate 02 | Certificate intro | C1     | coursecertificate1 | Certificate of participation |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Activities" block
    And I click on "Course certificates" "link" in the "Activities" "block"
    And I should see "Certificate 01"
    And I should see "Certificate 02"
    And I click on "Certificate 01" "link" in the "region-main" "region"
    And I should see "Nothing to display"

  @_switch_window
  Scenario: Display course certificate after removing current selected template.
    And the following certificate templates exist:
      | name                           | shared  |
      | Certificate of participation A | 1       |
      | Certificate of participation B | 1       |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template                       |
      | coursecertificate | Certificate 01 | Certificate intro | C1     | coursecertificate1 | Certificate of participation A |
    When I log in as "admin"
    And I navigate to "Certificates > Manage certificate templates" in site administration
    And I click on "Actions" "icon" in the "Certificate of participation A" "table_row"
    And I choose "Delete" in the open action menu
    And I click on "Delete" "button" in the "Confirm" "dialogue"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Certificate 01" "link" in the "region-main" "region"
    And I should see "The selected template can’t be found. Please go to the activity settings and select a new one."
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Certificate 01" "link" in the "region-main" "region"
    And I switch to a second window
    Then I should see "The certificate is not available. Please contact the course administrator."
    And I switch to the main window
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Certificate 01" "link" in the "region-main" "region"
    And I open course or activity settings page
    And I set the following fields to these values:
      | Template  | Certificate of participation B |
    And I press "Save and display"
    And I should not see "There is no selected template."

  Scenario: Display activity hidden warning
    And the following certificate templates exist:
      | name                           | shared  |
      | Certificate of participation A | 1       |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template                       | visible | automaticsend |
      | coursecertificate | Certificate 01 | Certificate intro | C1     | coursecertificate1 | Certificate of participation A | 0       | 1             |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Certificate 01" "link" in the "region-main" "region"
    Then I should see "This activity is currently hidden. By making it visible, students who meet the activity access restrictions will automatically receive a PDF copy of the certificate."
    And I press "Disable"
    And I press "Confirm"
    And I should not see "This activity is currently hidden. By making it visible, students who meet the activity access restrictions will automatically receive a PDF copy of the certificate."
    And I press "Enable"
    And I press "Confirm"
    And I should see "This activity is currently hidden. By making it visible, students who meet the activity access restrictions will automatically receive a PDF copy of the certificate."

  Scenario: Display automatic sending disabled info
    And the following certificate templates exist:
      | name                           | shared  |
      | Certificate of participation A | 1       |
    And the following "activities" exist:
      | activity          | name           | intro             | course | idnumber           | template                       | visible | automaticsend |
      | coursecertificate | Certificate 01 | Certificate intro | C1     | coursecertificate1 | Certificate of participation A | 1       | 0             |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Certificate 01" "link" in the "region-main" "region"
    Then I should see "Students who meet this activity's access restrictions will be issued with their certificate once they access it."
    And I press "Enable"
    And I press "Confirm"
    And I should not see "Students who meet this activity's access restrictions will be issued with their certificate once they access it."
    And I press "Disable"
    And I press "Confirm"
    And I should see "Students who meet this activity's access restrictions will be issued with their certificate once they access it."
