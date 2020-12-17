<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_coursecertificate
 * @category    string
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityhiddenwarning'] = 'This activity is currently hidden. By making it visible, students who meet the activity access restrictions will automatically receive a PDF copy of the certificate.';
$string['automaticsend_helptitle'] = "Help with automatic sending";
$string['automaticsenddisabled'] = 'The automatic sending of this certificate is disabled.';
$string['automaticsenddisabled_help'] = 'By leaving this disabled, students must click on the activity link displayed on the course page to receive the certificate, once they meet this activity\'s access restrictions.<br/><br/>
By enabling it, students will automatically receive a PDF copy of the certificate once they meet this activity\'s access restrictions. Note that all students that already meet this activity\'s access restrictions will receive the certificate when enabling this.';
$string['automaticsenddisabledalert'] = 'Students who meet this activity\'s access restrictions will be issued with their certificate once they access it.';
$string['automaticsenddisabledinfo'] = 'Currently, {$a} students meet this activity\'s access restrictions and will be issued with their certificate once they access it.';
$string['automaticsendenabled'] = 'The automatic sending of this certificate is enabled.';
$string['automaticsendenabled_help'] = 'By leaving this enabled, students will automatically receive a PDF copy of the certificate once they meet this activity\'s access restrictions.<br/><br/>
By disabling it, students will need to click on the activity link displayed on the course page to receive the certificate, once they meet this activity\'s access restrictions.';
$string['certificateissues'] = 'Certificate issues';
$string['certifiedusers'] = 'Certified users';
$string['chooseatemplate'] = 'Choose a template...';
$string['code'] = 'Code';
$string['coursecertificate:addinstance'] = 'Add a new Course certificate activity';
$string['coursecertificate:receive'] = 'Receive issued certificates';
$string['coursecertificate:view'] = 'View Course certificate';
$string['coursecertificate:viewreport'] = 'View Course certificate issues report';
$string['coursecompletiondate'] = 'Completion date';
$string['courseinternalid'] = 'Internal course ID used in URLs';
$string['courseurl'] = 'Course URL';
$string['disableautomaticsend'] = 'Students will no longer automatically receive a PDF copy of the certificate as soon as they meet
 this activity\'s access restrictions. Instead, they will need to click on the activity link displayed on the course page to receive
 the certificate, once they meet this activity\'s access restrictions.';
$string['enableautomaticsend'] = 'All students will automatically receive a PDF copy of the certificate as soon as they meet this activity\'s access restrictions.<br/><br/>
Currently, {$a} students already meet these access restrictions but haven\'t accessed this activity yet. They will immediately receive their copy as well.<br/><br/>
Students who have already accessed this activity will not receive the certificate again.';
$string['enableautomaticsendpopup'] = 'All students will automatically receive a PDF copy of the certificate as soon as they meet this activity\'s access restrictions.<br/><br/>
Students who already meet these access restrictions but haven\'t accessed this activity yet will immediately receive their copy as well.<br/><br/>
Students who have already accessed this activity will not receive the certificate again.';
$string['expirydate'] = 'Expiry date';
$string['issueddate'] = 'Date issued';
$string['managetemplates'] = 'Manage certificate templates';
$string['modulename'] = 'Course certificate';
$string['modulename_help'] = 'The course certificate module provides an opportunity for learners to celebrate achievements by
 obtaining certificates.<br/><br/> It allows you to choose from different certificate templates which will automatically display user data
 such as full name, course, etc. <br/><br/> Users will be able to download a PDF copy of the certificate themselves by accessing this
 activity, and there are options to send a PDF copy to them by email automatically.<br/><br/>If the template used on this activity contains
 a QR code, users will be able to scan it to validate their certificates.';
$string['modulename_link'] = 'mod/certificate/view';
$string['modulenameplural'] = 'Course certificates';
$string['notemplateselected'] = 'The selected template can’t be found. Please go to the activity settings and select a new one.';
$string['notemplateselecteduser'] = 'The certificate is not available. Please contact the course administrator.';
$string['notemplateswarning'] = 'There are no available templates. Please contact the site administrator.';
$string['notemplateswarningwithlink'] = 'There are no available templates. Please go to <a href="{$a}">certificate template management page</a> and create a new one.';
$string['nouserscertified'] = 'No users are certified.';
$string['page-mod-coursecertificate-x'] = 'Any course certificate module page';
$string['pluginadministration'] = 'Course certificate administration';
$string['pluginname'] = 'Course certificate';
$string['previewcoursefullname'] = 'Course full name';
$string['previewcourseshortname'] = 'Course short name';
$string['privacy:metadata'] = 'The course certificate activity does not store personal data.';
$string['revoke'] = 'Revoke';
$string['revokeissue'] = 'Are you sure you want to revoke this certificate issue from this user?';
$string['selectdate'] = 'Select date';
$string['selecttemplatewarning'] = 'Once this activity issues at least one certificate, this field will be locked and will no longer be editable.';
$string['status'] = 'Status';
$string['taskissuecertificates'] = 'Issue course certificates';
$string['template'] = 'Template';
