# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.1.1] - 2020-02-25
### Added
- The bundle is now additionally tested against PHP 7.4 ([2b29be3](https://github.com/trikoder/oauth2-bundle/commit/2b29be3629877a648f4a199b96185b40d625f6aa))

### Fixed
- Authentication provider not being aware of the current firewall context ([d349329](https://github.com/trikoder/oauth2-bundle/commit/d349329056c219969e097ae6bd3eb724968f9812))
- Faulty logic when revoking authorization codes ([24ad882](https://github.com/trikoder/oauth2-bundle/commit/24ad88211cefddf97170f5c1cc8ba1e5cf285e42))

## [2.1.0] - 2019-12-09
### Added
- Ability to change the scope role prefix using the `role_prefix` configuration option ([b2ee617](https://github.com/trikoder/oauth2-bundle/commit/b2ee6179832cc142d95e3b13d9af09d6cb6831d5))
- Interfaces for converter type service classes ([d2caf69](https://github.com/trikoder/oauth2-bundle/commit/d2caf690839523a2c84d967a6f99787898d4c654))
- New testing target in Travis CI for Symfony 4.4 ([8a44fd4](https://github.com/trikoder/oauth2-bundle/commit/8a44fd4d7673467cc4f69988424cdfc677767aab))
- The bundle is now fully compatible with [Symfony Flex](https://github.com/symfony/flex) ([a4ccea1](https://github.com/trikoder/oauth2-bundle/commit/a4ccea1dfaaba6d95daf3e1f1a84952cafb65d01))

### Changed
- [DoctrineBundle](https://github.com/doctrine/DoctrineBundle) version constraint to allow `2.x` derived versions ([885e398](https://github.com/trikoder/oauth2-bundle/commit/885e39811331e89bae99bca71f1a783497d26d12))
- Explicitly list [league/oauth2-server](https://github.com/thephpleague/oauth2-server) version requirements in the documentation ([9dce66a](https://github.com/trikoder/oauth2-bundle/commit/9dce66a089c33c224fe5cb58bdfd6285350a607b))
- Reduce distributed package size by excluding files that are used only for development ([80b9e41](https://github.com/trikoder/oauth2-bundle/commit/80b9e41155e7a94c3b1a4602c8daa25cc6d246b2))
- Simplify `AuthorizationRequestResolveEvent` class creation ([32908c1](https://github.com/trikoder/oauth2-bundle/commit/32908c1a4a89fd89d5835d4de931d237de223b50))

### Fixed
- Not being able to delete clients that have access/refresh tokens assigned to them ([424b770](https://github.com/trikoder/oauth2-bundle/commit/424b770dbd99e4651777a3fa26186a756b4e93c4))

## [2.0.1] - 2019-08-13
### Removed
- PSR-7/17 alias check during the container compile process ([0847ea3](https://github.com/trikoder/oauth2-bundle/commit/0847ea3034cc433c9c8f92ec46fedbdace259e3d))

## [2.0.0] - 2019-08-08
### Added
- Ability to specify a [Defuse](https://github.com/defuse/php-encryption/blob/master/docs/classes/Key.md) key as the encryption key ([d83fefe](https://github.com/trikoder/oauth2-bundle/commit/d83fefe149c1add841d4225ebc2a32aa9333308d))
- Ability to use different PSR-7/17 HTTP transport implementations ([4973e1c](https://github.com/trikoder/oauth2-bundle/commit/4973e1c7ddfc4afcca85989bde1b8d28dcd7fd4a))
- Allow configuration of the private key passphrase ([f16ec67](https://github.com/trikoder/oauth2-bundle/commit/f16ec67f2fa8dbf8fedd78488d625cef2db5b90d))
- Checks if dependent bundles are enabled in the application kernel ([38f6641](https://github.com/trikoder/oauth2-bundle/commit/38f66418b5f28b8666d5bbde1e36a45cfc166afa))
- Console command for clearing expired access and refresh tokens ([de3e338](https://github.com/trikoder/oauth2-bundle/commit/de3e338a24e0b03ab634c4982c46034715635379))
- Console commands for client management ([2425b3d](https://github.com/trikoder/oauth2-bundle/commit/2425b3d149cadb1706eb70b321491bf894114784), [56aafba](https://github.com/trikoder/oauth2-bundle/commit/56aafba995f06e45fd6521735be780c327e67d65))
- Server grant types can now be enabled/disabled through bundle configuration ([baffa92](https://github.com/trikoder/oauth2-bundle/commit/baffa928d9f489bd642fff7ae2bc88ce93badcbf))
- Support for the "authorization_code" server grant type ([a61114a](https://github.com/trikoder/oauth2-bundle/commit/a61114a7f2449bdb28b0779b0a4a7d21b9fff2c2))
- Support for the "implicit" server grant type ([91b3d75](https://github.com/trikoder/oauth2-bundle/commit/91b3d7583e269d5151927f24fbaec9d2fc4cea3d))
- Support for Symfony 4.3 ([e4cf668](https://github.com/trikoder/oauth2-bundle/commit/e4cf6680ddfb7d1327b2c83ed22f46c0db56c67a))
- The bundle is now additionally tested against PHP 7.3 ([9f5937b](https://github.com/trikoder/oauth2-bundle/commit/9f5937bda2a112337a9b375ed3923918bcc06370))

### Changed
- Authentication exceptions are now thrown instead of setting the response object ([8a505f6](https://github.com/trikoder/oauth2-bundle/commit/8a505f61f52d6ce924ab7119a411a17efdf1bbef))
- Modernize bundle service definitions ([fc1f855](https://github.com/trikoder/oauth2-bundle/commit/fc1f8556c180ba961bd6f2c973d36ff7439cbf34), [ef2f557](https://github.com/trikoder/oauth2-bundle/commit/ef2f557f357de8cf39bd87da3499cb38563ad82f))
- Previously [documented](https://github.com/trikoder/oauth2-bundle/blob/v1.1.0/docs/controlling-token-scopes.md) client scope inheriting and restricting is now the new default behavior ([af9bffc](https://github.com/trikoder/oauth2-bundle/commit/af9bffcbcab7b02036c36ba0e1bc7d7b6921280))
- Relaxed the [league/oauth2-server](https://github.com/thephpleague/oauth2-server) package version constraint to allow non-braking changes ([26d9c0b](https://github.com/trikoder/oauth2-bundle/commit/26d9c0b14a4d31e3fd5f620facfa374795f9adeb))
- Use `DateTimeInterface` instead of `DateTime` whenever possible ([4549252](https://github.com/trikoder/oauth2-bundle/commit/454925249bfba1b6fd5c8e07fd64a4e87039759e))

### Fixed
- [DoctrineBundle](https://github.com/doctrine/DoctrineBundle) related deprecation notices ([fbde15b](https://github.com/trikoder/oauth2-bundle/commit/fbde15bfd2295b10563136701f668c839dcc1e5e))
- Not being able to override the "persistence" config tree from other configuration files ([b62b331](https://github.com/trikoder/oauth2-bundle/commit/b62b331834c77609893a1b70633ef7683ada7edc))
- [Symfony](https://github.com/symfony/symfony) related deprecation notices ([601d482](https://github.com/trikoder/oauth2-bundle/commit/601d482351e67d3d22b6ca600e26ed1da7f33866))

### Removed
- Redundant configuration node options ([5fa60ef](https://github.com/trikoder/oauth2-bundle/commit/5fa60efb81fddea79989e502f67bc7aca1bcac16))
- Support for Symfony 4.1 ([4973e1c](https://github.com/trikoder/oauth2-bundle/commit/4973e1c7ddfc4afcca85989bde1b8d28dcd7fd4a))
- Unsupported HTTP verbs on the `/authorize` and `/token` endpoints ([51ef5ae](https://github.com/trikoder/oauth2-bundle/commit/51ef5ae7e659afaf63c024e7da070464d318fd67))

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
