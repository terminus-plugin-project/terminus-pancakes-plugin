# Pancakes

Terminus Plugin to open Pantheon Databases in [Sequel Pro](http://www.sequelpro.com/) (Mac) or [HeidiSQL](http://www.heidisql.com/) (Windows).

Adds a sub-command to 'site' which is called 'pancakes'. This opens a site in the appropriate database management application.

## Examples
* `terminus site pancakes`
* `terminus site pc --site=companysite-33 --env=dev`

## Installation
For help installing, see [Terminus's Wiki](https://github.com/pantheon-systems/terminus/wiki/Plugins)

## A Note About Windows
The plugin will automatically attempt to find the HeidiSQL executable within your `Program Files` directory.  If your version of HeidiSQL is installed in a non-standard location or you are using the portable version of HeidiSQL, ensure the full path to heidisql.exe (including the executable itself) is set in the `TERMINUS_PANCAKES_HEIDISQL_LOC` environment variable.

## Help
Run `terminus help site pancakes` for help.
