# Pancakes

Terminus plugin to open any Pantheon site database using a SQL GUI client.

## Installation
**_Note:_** This plugin will only work with Terminux 1.x

Refer to the [Terminus Wiki](https://github.com/pantheon-systems/terminus/wiki/Plugins).

## Supported:
[HeidiSQL](http://www.heidisql.com/) (Windows)

[Sequel Pro](http://www.sequelpro.com/) (Mac)

[MySQL Workbench](https://dev.mysql.com/downloads/workbench/) (Mac, Linux and Windows)

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
