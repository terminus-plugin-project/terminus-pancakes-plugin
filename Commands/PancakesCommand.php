<?php

namespace Terminus\Commands;

use Terminus\Collections\Sites;
use Terminus\Helpers\InputHelper;
use Terminus\Models\Site;
use Terminus\Models\Environment;
use Terminus\Utils;
use ReflectionMethod;

/**
 * Open Site database in your favorite MySQL Editor
 *
 * Terminus loads files based on \DirectoryIterator, so throw it on the top.
 *
 * @command site
 */
class PancakesCommand extends TerminusCommand {
  /**
   * @var $sites Sites - Sites
   */
  protected $sites;

  /**
   * @var $site Site - The Site being worked on
   */
  protected $site;

  /**
   * @var $last_error_code int - The last error code from an execution
   */
  protected $last_error_code;

  /**
   * @var $environment Environment - The environment
   */
  protected $environment;

  /**
   * @var $connection_info array - The connection info to the site
   */
  protected $connection_info;

  /**
   * @var $app string - The App Label
   */
  protected $app;

  /**
   * @var $aliases string[] - Aliases to match the command on. Doesn't need to be an exact match
   */
  protected $aliases;

  /**
   * @var $command - Command object to run things on
   */
  protected $command;

  /**
   * Object constructor
   *
   * @param array $options
   */
  public function __construct(array $options = []) {
    // Require Login for the Main Command, The subcommands don't need to be rechecked
    $options['require_login'] = isset($options['require_login']) ? $options['require_login'] : FALSE;

    parent::__construct($options);

    $this->sites = isset($options['sites']) ? $options['sites'] : new Sites();
    $this->site = !isset($options['site']) ?: $options['site'];
    $this->environment = !isset($options['environment']) ?: $options['environment'];
    $this->connection_info = !isset($options['connection_info']) ?: $options['connection_info'];
  }

  /**
   * Open Site database in Database Program
   *
   * [--app=<sequelpro|workbench|heidi>]
   * : App to Use
   *
   * [--site=<site>]
   * : Site to Use
   *
   * [--env=<env>]
   * : Environment
   *
   * @subcommand pancakes
   * @alias pc
   */
  public function pancakes($args, $assoc_args) {
    /* @var $input InputHelper */
    $input = $this->input();

    $this->site = $this->sites->get(
      $input->siteName(['args' => $assoc_args])
    );

    $env_id = $input->env([
      'args' => $assoc_args,
      'site' => $this->site
    ]);

    $this->environment = $this->site->environments->get($env_id);
    $this->environment->wake();

    $this->connection_info = $this->environment->connectionInfo();
    $this->connection_info['site_label'] = sprintf('%s [%s]', $this->site->get('name'), $this->environment->get('id'));

    $candidate_instances = $this->getCandinatePlugins();
    /* @var PancakesCommand|null $instance */
    $instance = NULL;

    // Check if any of them match a direct parameter
    if (!empty($assoc_args['app'])) {
      $all_aliases = [];
      foreach ($candidate_instances as $candidate_instance) {
        if (isset($candidate_instance->aliases)) {
          $app_aliases = implode(', ', $candidate_instance->aliases);
          $all_aliases[] = "[{$candidate_instance->app}] $app_aliases";
          foreach ($candidate_instance->aliases as $alias) {
            if (strpos($alias, trim($assoc_args['app'])) !== FALSE) {
              $instance = $candidate_instance;
            }
          }
        }
      }

      if (empty($instance)) {
        $this->failure('{app} was not found. Valid Apps: {aliases}', [
          'app' => $assoc_args['app'],
          'aliases' => implode('; ', $all_aliases),
        ]);
      }
    }


    $indirect = FALSE;
    $candidates = implode(', ', $candidate_instances);

    if (empty($instance) && !empty($candidate_instances)) {
      $this->log()
        ->debug('Valid candidates: {candidates}', [
          'candidates' => $candidates,
        ]);

      if (count($candidate_instances) > 1) {
        $indirect = TRUE;
      }

      $instance = reset($candidate_instances);
    }

    if (empty($instance)) {
      $this->failure('No applications for pancakes found.');
      return;
    }

    if ($indirect) {
      $this->log()
        ->info("Multiple Pancakes Applications were found: $candidates. Add --app to be specific on the app.", [
          'site' => $this->site->get('id'),
        ]);
    }

    $this->log()
      ->info('Opening {site} database in {app}.', [
        'site' => $this->environment->get('id') . '-' . $this->site->get('name') . '.pantheon.io',
        'app' => $instance->app
      ]);

    $instance->pancakes($args, $assoc_args);
  }

  /**
   * Execute Command
   * @param $command
   * @param $arguments
   * @return bool True if command executes without an error
   */
  protected function execCommand($command, $arguments = array()) {
    $arguments = is_array($arguments) ? $arguments : [$arguments];

    if (!empty($arguments)) {
      $command .= ' ' . implode(' ', $arguments);
    }

    $this->log()->debug('Executing: {command}', ['command' => $command]);

    exec($command, $output, $error_code);
    $this->last_error_code = $error_code;
    return !$error_code;
  }

  /**
   * Writes a file to a temporary location
   *
   * @param $data
   * @param $suffix
   * @return mixed
   */
  protected function writeFile($data, $suffix = NULL) {
    $tempfile = tempnam(sys_get_temp_dir(), 'terminus-pancakes');
    $tempfile .= !empty($suffix) ? ('.' . $suffix) : '';

    $handle = fopen($tempfile, "w");
    fwrite($handle, $data);
    fclose($handle);
    return $tempfile;
  }

  /**
   * Runs which
   * @param $command
   * @return bool
   */
  protected function which($command) {
    return $this->execCommand("which $command 2> /dev/null");
  }

  /**
   * Formats a flag for a OS
   *
   * @param $name
   * @return mixed
   */
  protected function flag($name) {
    // I was very tempted to use str_pad....
    // return str_pad($name, strlen($name) + 1 + !Utils\isWindows(), '-', STR_PAD_LEFT);
    return (Utils\isWindows() ? "-" : "--") . $name;
  }

  /**
   * @return string
   * @non-command
   */
  public function __toString() {
    return $this->app;
  }

  /**
   * Platform Independent - Escape Shell Arg. Taken from Drush.
   *
   * @param $arg
   * @param bool $raw
   * @return string
   */
  protected function escapeShellArg($arg, $raw = FALSE) {
    // Short-circuit escaping for simple params (keep stuff readable)
    if (preg_match('|^[a-zA-Z0-9.:/_-]*$|', $arg)) {
      return $arg;
    }
    elseif (Utils\isWindows()) {
      // Double up existing backslashes
      $arg = preg_replace('/\\\/', '\\\\\\\\', $arg);

      // Double up double quotes
      $arg = preg_replace('/"/', '""', $arg);

      // Double up percents.
      $arg = preg_replace('/%/', '%%', $arg);

      // Only wrap with quotes when needed.
      if (!$raw) {
        // Add surrounding quotes.
        $arg = '"' . $arg . '"';
      }

      return $arg;
    }
    else {
      // For single quotes existing in the string, we will "exit"
      // single-quote mode, add a \' and then "re-enter"
      // single-quote mode.  The result of this is that
      // 'quote' becomes '\''quote'\''
      $arg = preg_replace('/\'/', '\'\\\'\'', $arg);

      // Replace "\t", "\n", "\r", "\0", "\x0B" with a whitespace.
      // Note that this replacement makes Drush's escapeshellarg work differently
      // than the built-in escapeshellarg in PHP on Linux, as these characters
      // usually are NOT replaced. However, this was done deliberately to be more
      // conservative when running _drush_escapeshellarg_linux on Windows
      // (this can happen when generating a command to run on a remote Linux server.)
      $arg = str_replace(array("\t", "\n", "\r", "\0", "\x0B"), ' ', $arg);

      // Only wrap with quotes when needed.
      if (!$raw) {
        // Add surrounding quotes.
        $arg = "'" . $arg . "'";
      }

      return $arg;
    }
  }

  /**
   * Gets Candinate Classes that are Loaded
   * @return array
   */
  private function getCandinatePlugins() {
    // Find our Children!
    $classes = get_declared_classes();

    $candidate_instances = [];

    foreach ($classes as $class) {
      $reflection = new \ReflectionClass($class);
      if ($reflection->isSubclassOf(__CLASS__)) {
        $candidate_instance = $reflection->newInstanceArgs([[
          'runner' => $this->runner,
          'logger' => $this->log(),
          'sites'  => $this->sites,
          'site'   => $this->site,
          'environment'  => $this->environment,
          'connection_info'  => $this->connection_info,
          'require_login'  => FALSE,
        ]]);

        if (method_exists($candidate_instance, 'validate')) {
          if (!$candidate_instance->validate()) {
            continue;
          }
        }

        $candidate_instances[] = $candidate_instance;
      }
    }

    return $candidate_instances;
  }
}

// Include Sub-Commands - Terminus uses DirectoryIterator so we need to have better control over the order.
$iterator = new \DirectoryIterator(dirname(__FILE__) . '/../PancakeCommands');
foreach ($iterator as $file) {
  if ($file->isFile()) {
    include_once $file->getPathname();
  }
}
