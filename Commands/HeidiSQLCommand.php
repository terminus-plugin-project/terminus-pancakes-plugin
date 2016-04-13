<?php
namespace Terminus\Commands;

use Terminus\Commands\TerminusCommand;
use Terminus\Exceptions\TerminusException;
use Terminus\Models\Collections\Sites;
use Terminus\Utils;

/**
 * Open Site database in HeidiSQL
 *
 * @command site
 */
class HeidiSQLCommand extends TerminusCommand {
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
   * Open Site database in HeidiSQL
   *
   * ## OPTIONS
   *
   * [--site=<site>]
   * : Site to Use
   *
   * [--env=<env>]
   * : Environment
   *
   * ## EXAMPLES
   *  terminus site heidisql --site=my-site --env=dev
   *
   * @subcommand heidisql
   * @alias heidi
   */
  public function heidisql($args, $assoc_args) {
    // Check if OS is Windows
    if (!\Terminus\Utils\isWindows()) {
      $this->failure('Operating system is not supported.');
    }

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

    $this->log()->info('Opening {site} database in HeidiSQL', array('site' => $site->get('name')));

    $possible_heidi_locations = array(
      '\Program Files\HeidiSQL\heidisql.exe',
      '\Program Files (x86)\HeidiSQL\heidisql.exe',
      getenv('TERMINUS_PANCAKES_HEIDISQL_LOC'),
    );

    foreach ($possible_heidi_locations as $phl) {
      if (file_exists($phl)) {
        $phl = escapeshellarg($phl);
        $mysql_host = escapeshellarg($mysql_host);
        $mysql_port = escapeshellarg($mysql_port);
        $mysql_username = escapeshellarg($mysql_username);
        $mysql_password = escapeshellarg($mysql_password);
        $command = sprintf('start /b "" %s -h=%s -P=%s -u=%s -p=%s', $phl, $mysql_host, $mysql_port, $mysql_username, $mysql_password);
        exec($command);
        break;
      }
    }
  }
}
