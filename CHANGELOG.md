# Changelog

## 3.11.17 - 2023-10-10
### Fixed
- Lock the moodle-plugin-ci, this version is supported for security fixes only and we will
  not keep it up-to-date with new coding style requirements

## 3.11.14 - 2023-04-25
### Fixed
- Prevent debugging messages about missing leftmargin and rightmargin field types

## 3.11.11 (2022031650)
### Changed
- Only coding style

## 3.11.9 (2022031630)
### Changed
- Add lock when generating certificate

## 3.11.8 (2022031620)
### Added
- Course certificates may be archived when a course is reset allowing to receive more than one
  certificate per user in the same course

## 3.11.7 (2022031610)
### Changed
- Prevent race condition resulting in issuing course certificate twice

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
