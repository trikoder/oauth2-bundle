# Contributing

All contributions are **welcome** and **very much appreciated**.

We accept contributions via Pull Requests on [Github](https://github.com/trikoder/oauth2-bundle).

## Pull Request guidelines

- **Add tests!** - We strongly encourage adding tests as well since the PR might not be accepted without them.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Development

[Docker](https://www.docker.com/) 18.03+ and [Docker Compose](https://github.com/docker/compose) 1.13+ are required for the development environment.

### Building the environment

Make sure your Docker images are all built and up-to-date using the following command:

```sh
dev/bin/docker-compose build
```

> **NOTE:** You can target a different version of PHP during development by appending the `--build-arg PHP_VERSION=<version>` argument.

After that, install all the needed packages required to develop the project:

```sh
dev/bin/php composer install
```

### Debugging

You can run the debugger using the following command:

```sh
dev/bin/php-debug vendor/bin/phpunit
```

Make sure your IDE is setup properly, for more information check out the [dedicated documentation](docs/debugging.md).

### Code linting

This bundle enforces the PSR-2 and Symfony code standards during development by using the [PHP CS Fixer](https://cs.sensiolabs.org/) utility. Before committing any code, you can run the utility to fix any potential rule violations:

```sh
dev/bin/php composer lint
```

### Testing

You can run the whole test suite using the following command:

```sh
dev/bin/php-test composer test
```

**Happy coding**!
