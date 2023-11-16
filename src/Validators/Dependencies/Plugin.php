<?php
namespace Cvy\WP\Env\Validators\Dependencies;

if (!defined('ABSPATH')) exit;

class Plugin
{
  private $name;

  private $main_file_rel_path;

  public function __construct( string $name, string $main_file_rel_path )
  {
    $this->name = $name;

    $this->main_file_rel_path = $main_file_rel_path;
  }

  public function get_name() : string
  {
    return $this->name;
  }

  public function get_url() : string
  {
    $search_plugins_url =
      $this->is_active() ?
      get_admin_url( null, 'plugins.php?plugin_status=all' ) :
      get_admin_url( null, 'plugin-install.php?tab=search&type=term' );

    return add_query_arg( 's', $this->get_slug(), $search_plugins_url );
  }

  private function get_slug() : string
  {
    $main_file_name = explode( '/', $this->main_file_rel_path )[1];

    return str_replace( '.php', '', $main_file_name );
  }

  public function is_active() : bool
  {
    if ( ! function_exists( 'is_plugin_active' ) )
    {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active( $this->main_file_rel_path );
  }
}