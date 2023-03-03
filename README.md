# Pancakes

[![Terminus v2.x Compatible](https://img.shields.io/badge/terminus-v2.x-green.svg)](https://github.com/terminus-plugin-project/terminus-pancakes/releases/tag/2.0)
[![Terminus v1.x Compatible](https://img.shields.io/badge/terminus-v1.x-green.svg)](https://github.com/terminus-plugin-project/terminus-pancakes/releases/tag/1.0)
[![Terminus v0.x Compatible](https://img.shields.io/badge/terminus-v0.x-green.svg)](https://github.com/terminus-plugin-project/terminus-pancakes/releases/tag/0.1)

Terminus plugin to open any Pantheon site database using a SQL GUI client.

## Installation

### Composer

For quick install using Composer, install using:

`composer create-project --stability=beta -d ~/.terminus/plugins/ terminus-plugin-project/terminus-pancakes-plugin:~2`

### Manually

Download project and unzip to `~/.terminus/plugins/terminus-pancakes-plugin`

### Using Terminus
`terminus self:plugin:install terminus-plugin-project/terminus-pancakes-plugin`

**_Note:_** For versions before Terminus 1.x, including v0.13, go [here](https://github.com/derimagia/terminus-pancakes/releases/tag/0.1).


## Supported:
[HeidiSQL](http://www.heidisql.com/) (Windows)

[Sequel Pro](http://www.sequelpro.com/) (Mac)

[MySQL Workbench](https://dev.mysql.com/downloads/workbench/) (Mac, Linux and Windows)

[MySQL CLI](https://dev.mysql.com/doc/refman/5.5/en/mysql.html) (Mac and Linux)

**_Note: The latest version of MySQL Workbench for Mac (version 6.3.6) is not compatible with this plugin._**

**_Please download version 6.2.5 instead.  Click on the `Looking for previous GA versions?` link to locate._**

## Examples:

Simply running the new site command "pancakes" or "pc" will auto-detect the application you have installed:

`$ terminus site:pancakes`

`$ terminus site:pc`

`$ terminus pc`

`$ terminus pc site.dev`

You can also be specific with the app you want if you have multiple installed:

`$ terminus site:pc --app=sequel`

`$ terminus site:pc --app=workbench`

`$ terminus site:pc --app=heidi`

## Windows:
The plugin will automatically attempt to find the HeidiSQL executable within your `Program Files` directory.  If your version of HeidiSQL is installed in a non-standard location or you are using the portable version of HeidiSQL, ensure the full path to heidisql.exe (including the executable itself) is set in the `TERMINUS_PANCAKES_HEIDISQL_LOC` environment variable.

Likewise, if your version of MySQL Workbench is installed outside the `Program Files` directory, make sure the `TERMINUS_PANCAKES_MYSQLWORKBENCH_LOC` environment variable is set.

## Help:
Run `terminus help site:pancakes` for help.

Refer to the [Terminus Getting Started](https://pantheon.io/docs/terminus/plugins/).
