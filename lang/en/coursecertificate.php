<?php
// This file is part of Moodle - http://moodle.org/
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

$string['automaticsenddisabled'] = 'The automatic sending of this certificate is disabled.';
$string['automaticsendenabled'] = 'The automatic sending of this certificate is enabled.';
$string['certificateissues'] = 'Certificate issues';
$string['certifiedusers'] = 'Certified users';
$string['chooseatemplate'] = 'Choose a template...';
$string['code'] = 'Code';
$string['coursecertificate:addinstance'] = 'Add a new Course certificate activity';
$string['coursecertificate:manage'] = 'Manage a Course certificate activity';
$string['coursecertificate:view'] = 'View Course certificate';
$string['coursecertificate:viewreport'] = 'View Course certificate issues report';
$string['coursecertificatefieldset'] = 'Course certificate Settings';
$string['coursecertificatename'] = 'Certificate';
$string['coursecertificatesettings'] = 'Settings';
$string['disableautomaticsend'] = 'Users will no longer receive a PDF copy of the certificate in their inboxes as soon as they have
 access to this activity, although they still will be able to download it manually.';
$string['emailteachers'] = 'Send notification and PDF to teachers by email';
$string['enableautomaticsend'] = 'All users will receive a PDF copy of the certificate in their inboxes as soon as they have access 
to this activity. This also includes users that already have access to this activity but didn\'t do so yet. <br>Users who accessed 
this activity in the past will be ignored.';
$string['expirydate'] = 'Expiry date';
$string['includepdf'] = 'Include PDF on the email notification sent to users';
$string['issueddate'] = 'Date issued';
$string['modulename'] = 'Course certificate';
$string['modulename_help'] = 'The course certificate module provides an opportunity for learners to celebrate achievements by 
obtaining certificates.<br><br> It allows you to choose from different certificate templates which will automatically display user data 
such as full name, course, etc. <br><br> Users will be able to download a PDF copy of the certificate themselves by accessing this 
activity, and there are options to send a PDF copy to them by email automatically.<br><br>If the template used on this activity contains
 a QR code, users will be able to scan it to validate their certificates.';
$string['modulename_link'] = 'mod/certificate/view';
$string['modulenameplural'] = 'Course certificate activities';
$string['notemplateswarning'] = 'There are no available templates. Please contact the site administrator.';
$string['notemplateswarningwithlink'] = 'There are no available templates. Please go to <a href="{$a}">Certificate</a> and create a new one.';
$string['nouserscertified'] = 'No users are certified.';
$string['page-mod-certificate-x'] = 'Any course certificate module page';
$string['pluginadministration'] = 'Course certificate administration';
$string['pluginname'] = 'Course certificate';
$string['privacy:metadata'] = 'The course certificate activity does not store personal data.';
$string['receivecertificate'] = 'Receive certificate';
$string['receivecertificatenotification'] = 'Receive certificate?';
$string['revoke'] = 'Revoke';
$string['revokeissue'] = 'Are you sure you want to revoke this certificate issue from this user?';
$string['selectdate'] = 'Select date';
$string['selecttemplatewarning'] = 'Once this activity issues at least one certificate, this field will be locked and will no longer be editable.';
$string['status'] = 'Status';
$string['taskissuecertificates'] = 'Issue course certificates';
$string['template'] = 'Template';
$string['userscanpreview'] = 'Users can preview the certificate';
$string['whenavailable'] = 'When available';
