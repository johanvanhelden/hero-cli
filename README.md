# Hero CLI

## Version 0.0.2

### What is Hero CLI?

A companion application to [Dockerhero](https://github.com/johanvanhelden/dockerhero) for easy and quick local development.

What can it do? It is made to make multi-step workflows executable in just 1 single command.
So, for example, 1 local command can take care of your composer installation and migrations within Dockerhero and asset building on your local machine.

## Installation
- Clone this project to your project directory, the directory that also includes Dockerhero. For example: `/home/john/projects`
```bash
composer install
cp .env.example .env
cp recipes.yml.example recipes.yml
```

From here you have 2 choices, you can either add the path to Hero CLI to your `$PATH` or [build yourself a binary](https://laravel-zero.com/docs/build-a-standalone-application/) and place it in, for example, `/usr/local/bin`

### Adding the path to Hero CLI to the $PATH
Put the following line in your `~/.bash_aliases` or `~/.bashrc` file:

```bash
export PATH="$PATH:$HOME/projects/hero-cli"
```

_Please make sure that the path is actually correct._

## Configuration
- Change the `.env` file to reflect your local setup.
- Change the `recipes.yml` file to reflect your project workflow.

## Recipes
The power of Hero CLI lies in the recipes. It allows you to define your workflow for you projects.
For an example of a recipe file, you can take a look at the `recipes.yml.example` file in the root of the project.

_Please note, docker commands should always be relative. Hero CLI takes care of building the path for you._

### Variables
The following variables can be used:
- `{localProjectPath}` This will be replaced with the path to your project

### Running recipes
To execute a recipe, you run:
``` bash
hero recipe {recipeName} {projectName}
```

### Allow exceptions in commands
By default an exception will be thrown if an error occurs during a command's execution. To allow failures to happen, add
`allow_failure: true` to the recipe's command.

### Show the command's output
By default the output of the commands will be hidden. To view the output, add
`show_output: true` to the recipe's command.

### Customize the path for a local command
By default the local command will be executed in the project directory. To customize the path, add
`path: "/home/username/my-custom/path/"` to the recipe's command.

## Project setup
A command is included to automatically setup a project for the first time. Simply run: 

``` bash
hero setup:project {projectName}
```

The .env.example will be copied, and the database and the database user will automatically be setup.

## Security

If you discover any security-related issues, please email [johan@johanvanhelden.com](mailto:johan@johanvanhelden.com) instead of using the issue tracker.

## License

GNU General Public License v3.0 (gpl-3.0). Please see the [License File](LICENSE.md) for more information.
