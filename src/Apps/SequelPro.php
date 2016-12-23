<?php

namespace Pantheon\TerminusPancakes\Apps;

/**
 * Open Site database in SequelPro
 */
class SequelProCommand extends PancakesApp{
  /**
   * {@inheritdoc}
   */
  public $aliases = ['sequelpro', 'sequel'];

  /**
   * {@inheritdoc}
   */
  public $app = 'Sequel Pro';

  /**
   * Open Site database in SequelPro
   */
  public function open() {
    $xmldata = $this->getSequelProOpenFile();
    $tempfile = $this->writeFile($xmldata, 'spf');
    $this->execCommand('open', $tempfile);
  }

  /**
   * Validates the app can be used
   */
  public function validate() {
    // @TODO: Validate SequelPro better
    // @TODO: New Terminus has better way to check OS
    return strtoupper(substr(PHP_OS, 0, 3)) === 'DAR';
  }

  /**
   * Gets the XML for opening a connection in Sequel Pro
   */
  private function getSequelProOpenFile() {
    $label = htmlspecialchars($this->connection_info['site_label']);
    $mysql_host = htmlspecialchars($this->connection_info['mysql_host']);
    $mysql_port = htmlspecialchars($this->connection_info['mysql_port']);
    $mysql_username = htmlspecialchars($this->connection_info['mysql_username']);
    $mysql_password = htmlspecialchars($this->connection_info['mysql_password']);
    $mysql_database = htmlspecialchars($this->connection_info['mysql_database']);

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
