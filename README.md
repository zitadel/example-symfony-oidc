# Example symfony

This repository provides a Symfomy framework example for ZITADEL.

## Development

### Devcontainer

This project contains [Development Container](https://containers.dev/) definitions with a complete PHP-8.2 environment, configuration and tools required for Symfony development.
Use you favorite IDE to build and launch the development environment.

### Symfony

After cloning the repository, install the project dependencies.

```bash
composer install
```

At this point you might want to start a [`xdebug` client](https://xdebug.org/docs/step_debug#clients).
This is not required, but Sympony might complain in the following steps that it can't connect to a `xdebug` client.
An example [`launch.json``](.vscode/launch.json) file has been provided for VSCode. Use the "Listen for Xdebug" option.

You can check the application environment with:

```bash
php bin/console about
```

And run the development server:

```bash
symfony server:start --no-tls
```
