# Pancakes

Terminus plugin to open any Pantheon site database using a SQL GUI client.

## Supported:
[HeidiSQL](http://www.heidisql.com/) (Windows)

[Sequel Pro](http://www.sequelpro.com/) (Mac)

[MySQL Workbench](https://dev.mysql.com/downloads/workbench/) (Mac, Linux and Windows)

**_Note: The latest version of MySQL Workbench for Mac (version 6.3.6) is not compatible with this plugin._**

**_Please download version 6.2.5 instead.  Click on the `Looking for previous GA versions?` link to locate._**

## Examples:
`$ terminus site heidisql`

`$ terminus site heidi --site=my-company --env=dev`

`$ terminus site sequelpro`

`$ terminus site sequel --site=my-company --env=dev`

`$ terminus site mysql-workbench`

`$ terminus site workbench --site=my-company --env=dev`

## Installation:
Refer to the [Terminus Wiki](https://github.com/pantheon-systems/terminus/wiki/Plugins).

## Windows:
The plugin will automatically attempt to find the HeidiSQL executable within your `Program Files` directory.  If your version of HeidiSQL is installed in a non-standard location or you are using the portable version of HeidiSQL, ensure the full path to heidisql.exe (including the executable itself) is set in the `TERMINUS_PANCAKES_HEIDISQL_LOC` environment variable.

MySQL Workbench requires the executable to be in the command prompt path.  The directory to add will be similar to `C:\Program Files\MySQL\MySQL Workbench 6.3 CE\`. See http://www.computerhope.com/issues/ch000549.htm.

## Help:
Run `terminus help site heidi|sequel|workbench` for help.
