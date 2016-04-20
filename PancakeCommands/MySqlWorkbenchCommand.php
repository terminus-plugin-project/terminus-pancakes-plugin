<?php

namespace Terminus\Commands;

/**
 * Open Site database in MySQL Workbench
 */
class MySqlWorkbenchCommand extends PancakesCommand {

  /**
   * {@inheritdoc}
   */
  public $aliases = ['mysql-workbench', 'mysqlworkbench', 'workbench'];

  /**
   * {@inheritdoc}
   */
  public $app = 'MySQL Workbench';

  /**
   * - App Location
   */
  public $app_location;

  /**
   * - App Home Location
   */
  public $app_home_location;

  /**
   * Open Site database in MySQL Workbench
   */
  public function pancakes($args, $assoc_args) {
    $domain = $this->environment->get('id') . '-' . $this->site->get('name') . '.pantheon.io';
    $this->connection_info['domain'] = $domain;
    $this->connection_info['connection_id'] = md5($domain . '.connection');
    $this->connection_info['server_instance_id'] = md5($domain . '.server');

    $connections_xml = $this->getConnectionXml($this->connection_info);
    $connections_file = "{$this->app_home_location}connections.xml";
    $this->writeXML($connections_file, $connections_xml, $domain);

    $server_instances_xml = $this->getServerInstanceXml($this->connection_info);
    $server_instances_file = "{$this->app_home_location}server_instances.xml";
    $this->writeXML($server_instances_file, $server_instances_xml, $domain);

    $this->execCommand($this->app_location, [$this->flag('admin'), $domain]);
  }

  /**
   * Validate MySQLWorkbench can run
   *
   * @return bool
   */
  protected function validate($args, $assoc_args) {
     /* @TODO: Terminus now has Utils for this, wait until most people are using it and switch it */
    $os = strtoupper(substr(PHP_OS, 0, 3));
    switch ($os) {
      case 'DAR':
        $candidates = array('/Applications/MySQLWorkbench.app/Contents/MacOS/MySQLWorkbench');
        $workbench_home = getenv('HOME') . '/Library/Application Support/MySQL/Workbench/';
        break;
      case 'LIN';
        $candidates = array('/usr/bin/mysql-workbench');
        $workbench_home = getenv('HOME') . '/.mysql/workbench/';
        break;
      case 'WIN':
        $candidates = array(
          '\Program Files\MySQL\MySQL Workbench 6.3 CE\MySQLWorkbench.exe',
          '\Program Files (x86)\MySQL\MySQL Workbench 6.3 CE\MySQLWorkbench.exe',
          "'" . getenv('TERMINUS_PANCAKES_MYSQLWORKBENCH_LOC') . "'",
        );
        $workbench_home = getenv('HOMEPATH') . '\\AppData\\Roaming\\MySQL\\Workbench\\';
        break;
      default:
        return FALSE;
    }

    // Try to find the app in the path
    foreach ($assoc_args as $key => $arg) {
      if ($key == 'app') {
        if ($this->which($arg)) {
          $this->app_home_location = $workbench_home;
          $this->app_location = '"' . $arg . '"';
          return TRUE;
        }
      }
    }

    // Try to find the app in typical locations
    foreach ($candidates as $candidate) {
      if (file_exists($candidate)) {
        $this->app_home_location = $workbench_home;
        $this->app_location = '"' . $candidate . '"';
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Generate the XML for opening a connection in MySQL Workbench
   */
  protected function getConnectionXml($ci) {
    return <<<XML
    <value type="object" struct-name="db.mgmt.Connection" id="{$ci['connection_id']}" struct-checksum="0x96ba47d8">
      <link type="object" struct-name="db.mgmt.Driver" key="driver">com.mysql.rdbms.mysql.driver.native_sshtun</link>
      <value type="string" key="hostIdentifier">Mysql@{$ci['mysql_host']}:{$ci['mysql_port']}@{$ci['sftp_host']}:{$ci['git_port']}</value>
      <value type="int" key="isDefault">1</value>
      <value _ptr_="0x321bf00" type="dict" key="modules"/>
      <value _ptr_="0x321bf70" type="dict" key="parameterValues">
        <value type="string" key="DbSqlEditor:LastDefaultSchema">{$ci['mysql_database']}</value>
        <value type="string" key="SQL_MODE"></value>
        <value type="string" key="hostName">{$ci['mysql_host']}</value>
        <value type="int" key="lastConnected"></value>
        <value type="string" key="password">{$ci['mysql_password']}</value>
        <value type="int" key="port">{$ci['mysql_port']}</value>
        <value type="string" key="schema">{$ci['mysql_database']}</value>
        <value type="string" key="serverVersion">10.0.21-MariaDB-log</value>
        <value type="string" key="sshHost">{$ci['sftp_host']}:{$ci['git_port']}</value>
        <value type="string" key="sshKeyFile"></value>
        <value type="string" key="sshPassword"></value>
        <value type="string" key="sshUserName">{$ci['sftp_username']}</value>
        <value type="string" key="sslCA"></value>
        <value type="string" key="sslCert"></value>
        <value type="string" key="sslCipher"></value>
        <value type="string" key="sslKey"></value>
        <value type="int" key="useSSL">2</value>
        <value type="string" key="userName">{$ci['mysql_username']}</value>
      </value>
      <value type="string" key="name">{$ci['domain']}</value>
      <link type="object" struct-name="GrtObject" key="owner">d460176e-fabd-11e5-874c-f0761c1cdeaf</link>
    </value>
XML;
  }

  /**
   * Generate the XML for opening a server instance in MySQL Workbench
   */
  protected function getServerInstanceXml($ci) {
    return <<<XML
    <value type="object" struct-name="db.mgmt.ServerInstance" id="{$ci['server_instance_id']}" struct-checksum="0x367436e2">
      <link type="object" struct-name="db.mgmt.Connection" key="connection">{$ci['connection_id']}</link>
      <value _ptr_="0x3218b80" type="dict" key="loginInfo"/>
      <value _ptr_="0x32067c0" type="dict" key="serverInfo">
        <value type="int" key="setupPending">1</value>
      </value>
      <value type="string" key="name">{$ci['domain']}</value>
    </value>
XML;
  }

  /**
   * Write the XML to the configuration file
   */
  protected function writeXML($file, $xml, $domain) {
    $data = file_get_contents($file);
    if (!strpos($data, $domain)) {
      $lines = file($file);
      $last = sizeof($lines) - 1;
      unset($lines[$last]);
      if (sizeof($lines) == 3) {
        $lines[2] = str_replace('/>', '>', $lines[2]);
      }
      else {
        $last = sizeof($lines) - 1;
        unset($lines[$last]);
      }
      $end = "\n  </value>\n</data>";
      $data = implode('', $lines) . $xml . $end;
      $handle = fopen($file, "w");
      fwrite($handle, $data);
      fclose($handle);
    }
  }

}
