# Example symfony OIDC

This repository provides a Symfony framework example for ZITADEL using OpenID connect (OIDC) authentication.
This example is provided as companion to our [guide](https://zitadel.com/docs/examples/login/symfony),
which should produce this application when followed.

## Features

 - OIDC Code flow with User Info call after authentication.
 - Fully integrated with Symfony security and firewall.
 - User Role mapping
 - Persistent user data using local sqlite file. See `DATABASE_URL` in [.env](.env).
 - Public page at `/`
 - Authenticated `/profile` page for all users.
 - Authenticated `/admin` page for admin role users.

## Package structure

The package structure follows a [Symfony boilerplate app](https://symfony.com/doc/current/setup.html#creating-symfony-applications) generated with:

```bash
symfony new my_project_directory --version="7.0.*" --webapp
```

Code implementations live under [`src/`](/src/) with accompanying [templates](/templates/).

## Getting started

If you want to run this example directly you can fork and clone it to your system.
Be sure to [configure ZITADEL](https://docs-git-docs-example-symfony-zitadel.vercel.app/docs/examples/login/symfony#zitadel-setup) to accept requests from this app.

### Prerequisites

You need a working PHP 8.2 or higher environment set up for use with Symfony. See more details in the [Symfony installation documentation](https://symfony.com/doc/current/setup.html#technical-requirements).

Alternatively if you have a system with Docker and an IDE capable of running [Development Container](https://containers.dev/),
definitions are provided with a complete PHP-8.2 environment, configuration and tools required for Symfony development.
Use your IDE to build and launch the development environment or use GitHub code spaces from your browser.

### Symfony

After setting up your system and repository, install the project dependencies locally.

```bash
composer install
```

At this point you might want to start a [`xdebug` client](https://xdebug.org/docs/step_debug#clients).
This is not required, but php might complain in the following steps that it can't connect to a `xdebug` client.
An example [`launch.json``](.vscode/launch.json) file has been provided for VSCode. Use the "Listen for Xdebug" option.

You can check the application environment with:

```bash
bin/console about
```

Create a local sqlite database (stored in `./var`).:

```bash
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
```

And run the development server:

```bash
symfony server:start --no-tls
```

Visit [http://localhost:8000] and click around. When you go to profile you will be redirected to login your user on ZITADEL. After login you should see some profile data of the current user. Upon clicking logout you are redirected to the homepage. Now you can click "users" and login with an account that has the admin role.
