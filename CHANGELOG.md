# Changelog

## 5.0.1 - 2025-06-10
### Added
- Improved CI automation

## 5.0 - 2025-04-14
### Added
- Added support for Moodle 5.0

## 4.5.3 - 2025-03-18
### Fixed
- behat tests checking that certificate opens in a new window failing with selenium/standalone-chrome:4

## 4.4.4 - 2024-10-08
### Added
- Compatibility with Moodle 4.5; Updates to version testing matrices

## 4.4.3 - 2024-09-03
### Changed
- Only changes to automatic testing scripts

## 4.4.2 - 2024-08-13
### Fixed
- Failing behat tests because of incorrect table headers

## 4.4.1 - 2024-06-11
### Fixed
- fixed implicit nullable parameter declaration deprecated in PHP 8.4
  (new coding style check)

## 4.4 - 2024-05-21
### Added
- Changed the icon to be more consistent with activity icons in 4.4

## 4.3.4 - 2024-04-23
### Added
- Compatibility with Moodle 4.4, added to the testing matrix
### Fixed
- Coding style fixes to comply with moodle-plugin-ci 4.4.0

## 4.3.3 - 2024-02-13
### Fixed
- Race condition if the template was deleted in the middle of the process of issuing certificates
- Link to the module documentation from the 'Add activity' menu

## 4.3.2 - 2023-12-28
### Fixed
- Users who have both student and teacher roles will no longer receive certificates
  without meeting availability restrictions conditions.

## 4.3 - 2023-11-09
### Added
- Testing on Workplace 4.3

## 4.2.3 - 2023-10-10
### Changed
- Coding style fixes
- Included LMS 4.3 and PHP 8.2 in the GHA testing matrix

## 4.2 - 2023-05-30
### Changed
- Removed strings: automaticsenddisabledinfo, enableautomaticsend, selecttemplatewarning,
  taskissuecertificates, template
- Deprecated strings: certificateissues, revoke, revokeissue, selectdate, status

## 4.1.3 - 2023-04-25
### Added
- Compatibility with Moodle LMS 4.2
- Compatibility with PHP 8.1 for Moodle LMS 4.1 and 4.2
### Fixed
- Prevent debugging messages about missing leftmargin and rightmargin field types

## 4.1.2 - 2023-03-14
### Added
- Setting to skip some text filters when generating PDFs

## 4.1.1 - 2023-01-17
### Changed
- Automated tests fixes

## 4.0.5+ - 2023-01-11
### Changed
- Certificates PDFs now always open in a new tab

## 4.0.5 - 2022-11-15
### Changed
- Added testing on LMS 4.1 (no functional changes)

## 4.0.4+ (2022101400)
- No changes, released to match the version of tool_certificate

## 4.0.3 (2022082400)
### Changed
- Use system report for certificate issues.
- Add lock when generating certificate

## 4.0.2 (2022071200)
### Added
- Course certificates may be archived when a course is reset allowing to receive more than one
  certificate per user in the same course

## 4.0.1 (2022051000)
### Changed
- Prevent race condition resulting in issuing course certificate twice

## 4.0.0 (2022042000)
### Changed
- This version of the plugin is only for Moodle LMS 4.0 and above
- New icon for Moodle LMS 4.0
- Use new API to display activity header

## 3.11.6 (2022031500)
### Added
- Allow relative dates for expiry dates (i.e. 1 year after issue)

## 3.11.5 (2022011800)
### Added
- Added support for the mobile app

### Changed
- Show country name instead of the two-letter code in the generated certificates
- Compliance with codechecker v3.0.5

## 3.11.1 (2021072000)
### Changed
- Coding style fixes

## 3.11 (2021060800)
### Changed
- Compatibility with Moodle 3.9 - 3.11

## 3.10.4 (2021051100)
### Changed
- Fixes to coding style to make new version of codechecker happy

## 3.10.1+ (2021020800)
### Changed
- Viewing and previewing certificates now open a new browser tab

## 3.10+ (2020121700)
### Changed
- Fixed a bug in how a 'Text area' course custom field is handled in the certificate templates
- For performance reasons the exact number of users who will receive certificate is no longer displayed.
  [CONTRIB-8325](https://tracker.moodle.org/browse/CONTRIB-8325)

## Previous versions
Changelog was not maintained before version 3.10
