# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.7.0-beta] - 2024-03-29

### Added
- The ability to import configuration Novaxis files, enhancing project setup and customization flexibility.

### Changed
- The keyword for representing the current class has been changed from `.self` to `self`, aligning with common programming conventions.
- Updated naming rules to enforce clarity and prevent conflicts. Specifically, `self` has been reserved and cannot be used as a name for classes, variables, or aliases.

### Fixed
- Improvements in `bin/novaxis` for better reliability and performance.
- Several syntax improvements, refining the language for ease of use and understanding.

### Note
- This is a beta release and might contain unresolved issues. Users are encouraged to provide feedback and report any encountered bugs.

## [1.6.4] - 2024-03-28

### Added
- [tests](tests/) folder containing Novaxis codes for ensuring the project's functionality.
- New examples in the [examples](examples/) folder to demonstrate project usage.
- `reinstall` target in the Makefile for easily refreshing the installation.

### Fixed
- Various files for enhanced stability and performance.
- Makefile enhancements for improved build and installation processes.

## [1.6.3] - 2024-03-25

### Added
- New example file for Novaxis language, located in the [examples directory](examples/).

### Fixed
- Corrected the Makefile issue where it wasn't creating a symlink for bin/novaxis.

## [Earlier Versions]

The project has a rich history of development with several versions released before 1.6.3. Detailed change logs for these versions are not included here. For any specific inquiries regarding the project's evolution, please feel free to reach out to the project maintainers.