<?php
namespace Terminus\Commands;

use Terminus\Commands\TerminusCommand;
use Terminus\Models\Collections\Sites;

/**
 * Say hello to the user
 *
 * @command site
 */
class PancakesCommand extends TerminusCommand {
  /**
   * Object constructor
   *
   * @param array $options
   * @return PantheonAliases
   */
  public function __construct(array $options = []) {
    $options['require_login'] = true;
     parent::__construct($options);
     $this->sites = new Sites();
  }

   /**
   * Connects SequelPro to the Site
   *
   * ## OPTIONS
   *
   * [--site=<site>]
   * : Site to Use
   *
   * [--env=<env>]
   * : Environment to clear
   *
   * ## EXAMPLES
   *  terminus site pancakes --site=test
   *
   * @subcommand pancakes
   * @alias pc
   */
  public function pancakes($args, $assoc_args) {
    $site = $this->sites->get(
      $this->input()->siteName(array('args' => $assoc_args))
    );

    $env_id   = $this->input()->env(array('args' => $assoc_args, 'site' => $site));
    $environment = $site->environments->get($env_id);
    $connection_info = $environment->connectionInfo();

    $mysql_host = $connection_info['mysql_host'];
    $mysql_username = $connection_info['mysql_username'];
    $mysql_password = $connection_info['mysql_password'];
    $mysql_port = $connection_info['mysql_port'];
    $mysql_database = $connection_info['mysql_database'];

    // Wake the Site
    $environment->wake();

    if (\Terminus\Utils\isWindows()) {
      $this->log()->info('Opening {site} in HeidiSQL', array('site' => $site->get('name')));

      $possible_heidi_locations = array(
        'C:\Program Files\HeidiSQL\heidisql.exe',
        'C:\Program Files (x86)\HeidiSQL\heidisql.exe',
      );

      foreach ($possible_heidi_locations as $phl) {
        if (file_exists($phl)) {

          $command = sprintf('start /b "" "%s" -h="%s" -P=%s -u="%s" -p="%s"', $phl, $mysql_host, $mysql_port, $mysql_username, $mysql_password);
          $this->log()->info($command);
          exec($command);
          break;
        } else {
          continue;
        }
      }
    } else {
      $this->log()->info('Opening {site} in SequelPro', array('site' => $site->get('name')));

      $label = sprintf('%s [%s]', $site->get('name'), $env_id);
      $openxml = $this->getOpenFile($label, $mysql_host, $mysql_port, $mysql_username, $mysql_password, $mysql_database);

      $tempfile = tempnam('/tmp', 'terminus-sequelpro') . '.spf';

      $handle = fopen($tempfile, "w");
      fwrite($handle, $openxml);
      fclose($handle);

      // Open in SequelPro
      $command = sprintf('%s %s', 'open', $tempfile);
      exec($command);
    }
  }

  /**
  * Gets the XML for opening a connection in Sequel Pro
  */
  public function getOpenFile($label, $mysql_host, $mysql_port, $mysql_username, $mysql_password, $mysql_database) {
    $mysql_host = htmlspecialchars($mysql_host);
    $mysql_port = htmlspecialchars($mysql_port);
    $mysql_username = htmlspecialchars($mysql_username);
    $mysql_password = htmlspecialchars($mysql_password);
    $mysql_database = htmlspecialchars($mysql_database);

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
  <key>ContentFilters</key>
  <dict/>
  <key>auto_connect</key>
  <true/>
  <key>data</key>
  <dict>
    <key>connection</key>
    <dict>
      <key>database</key>
      <string>{$mysql_database}</string>
      <key>host</key>
      <string>${mysql_host}</string>
      <key>name</key>
      <string>${label}</string>
      <key>user</key>
      <string>${mysql_username}</string>
      <key>password</key>
      <string>${mysql_password}</string>
      <key>port</key>
      <integer>${mysql_port}</integer>
      <key>rdbms_type</key>
      <string>mysql</string>
    </dict>
    <key>session</key>
    <dict/>
  </dict>
  <key>encrypted</key>
  <false/>
  <key>format</key>
  <string>connection</string>
  <key>queryFavorites</key>
  <array/>
  <key>queryHistory</key>
  <array/>
  <key>rdbms_type</key>
  <string>mysql</string>
  <key>version</key>
  <integer>1</integer>
</dict>
</plist>
XML;
  }
}
