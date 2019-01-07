# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2019-01-07
### Added
- The bundle is now compatible with Symfony 3.4 ([0ba9cb3](https://github.com/trikoder/oauth2-bundle/commit/0ba9cb306157a9ad89691eb3d20054a6803af472))

### Changed
- Bundle dependency requirements are now more relaxed ([158d221](https://github.com/trikoder/oauth2-bundle/commit/158d2212ff7d8aab802bcd87def6917522d1fbce))
- Permission checks against private/public keys are no longer enforced ([a24415a](https://github.com/trikoder/oauth2-bundle/commit/a24415a560174783a51ecfcd86a644490389cb13))

### Fixed
- Bundle creating a `default` Doctrine connection if it didn't exist ([d4e58a0](https://github.com/trikoder/oauth2-bundle/commit/d4e58a04eff3cc442fa6f9d721984b4c5ceedf67))
- Improper class naming ([b43be3d](https://github.com/trikoder/oauth2-bundle/commit/b43be3d9ac9bc3d5daa43daac61e4939326a13bd))

## [1.0.0] - 2018-11-28
This is the initial release.
