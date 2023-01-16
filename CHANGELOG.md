# Changelog

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
