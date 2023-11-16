<?php
namespace Cvy\WP\Env;

use \Cvy\WP\Env\Validators\Constants\Validator as ConstantsValidator;
use \Cvy\WP\Env\Validators\Dependencies\Validator as DependenciesValidator;
use Cvy\WP\Env\Validators\Dependencies\Plugin;
use \Cvy\WP\Env\Validators\GridPane\Validator as GridPaneValidator;

if ( ! defined( 'ABSPATH' ) ) exit();

class Env extends \Cvy\DesignPatterns\Singleton
{
  static private $is_grid_pane = false;

  public function __construct()
  {
    ErrorsHandler::get_instance();

    add_action( 'init', fn() => static::validate() );
  }

  static public function set_is_grid_pane( bool $is_grid_pane ) : void
  {
    static::$is_grid_pane = $is_grid_pane;
  }

  static private function validate() : void
  {
    static::validate_debug_constants();

    static::validate_plugin_dependencies();

    static::validate_search_engine_visibility();

    if ( static::$is_grid_pane )
    {
      GridPaneValidator::validate();
    }
  }

  static private function validate_debug_constants() : void
  {
    $debug_log_allowed_values = [];
    $debug_log_allowed_values[] = static::is_loc() ? true : dirname( ABSPATH ) . '/logs/debug.log';

    ConstantsValidator::validate_soft( 'WP_DEBUG', [ true ] );
    ConstantsValidator::validate_soft( 'WP_DEBUG_LOG', $debug_log_allowed_values );
    ConstantsValidator::validate_soft( 'WP_DEBUG_DISPLAY', [ static::is_loc() ] );
  }

  static private function validate_plugin_dependencies() : void
  {
    DependenciesValidator::validate_plugin(
      new Plugin( 'Query Monitor', 'query-monitor/query-monitor.php' ),
      DependenciesValidator::STATE_DISABLED,
      DependenciesValidator::STATE_ENABLED
    );

    DependenciesValidator::validate_plugin(
      new Plugin( 'WP Mail Logging', 'wp-mail-logging/wp-mail-logging.php' ),
      DependenciesValidator::STATE_DISABLED,
      DependenciesValidator::STATE_ENABLED
    );

    DependenciesValidator::validate_plugin(
      new Plugin( 'Disable Emails', 'disable-emails/disable-emails.php' ),
      DependenciesValidator::STATE_DISABLED_CRITICAL,
      DependenciesValidator::STATE_ENABLED_CRITICAL
    );

  }

  static private function validate_search_engine_visibility() : void
  {
    $settings_page_url = get_admin_url( null, 'options-reading.php' );

    $error_msg_template = "Search engine visibility must be <a href='$settings_page_url'>%s</a>!";

    $is_enabled = get_option( 'blog_public' );

    if ( static::is_prod() && ! $is_enabled )
    {
      ErrorsHandler::add_error( sprintf( $error_msg_template, 'enabled' ) );
    }
    else if ( ! static::is_prod() && $is_enabled )
    {
      ErrorsHandler::add_error( sprintf( $error_msg_template, 'disabled' ) );
    }
  }

  static public function get_env() : string
  {
    return CVY_ENV;
  }

  static public function is_prod() : bool
  {
    return static::get_env() === 'prod';
  }

  static public function is_stg() : bool
  {
    return static::get_env() === 'stg';
  }

  static public function is_loc() : bool
  {
    return static::get_env() === 'loc';
  }
}