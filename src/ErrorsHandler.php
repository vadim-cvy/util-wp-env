<?php
namespace Cvy\WP\Env;
use Cvy\WP\Assets\JS;

if (!defined('ABSPATH')) exit;

class ErrorsHandler extends \Cvy\DesignPatterns\Singleton
{
  static protected $critical_errors = [];

  static protected $general_errors = [];

  protected function __construct()
  {
    add_action( 'admin_init', fn() => $this->show_errors() );
    add_action( 'wp', fn() => $this->show_errors() );
  }

  static public function add_error( string $msg, bool $is_critical = false ) : void
  {
    if ( $is_critical )
    {
      static::$critical_errors[] = $msg;
    }
    else
    {
      static::$general_errors[] = $msg;
    }
  }

  private function show_errors() : void
  {
    if ( empty( static::$critical_errors ) && empty( static::$general_errors ) )
    {
      return;
    }

    if ( ! static::is_user_authorized_see_errors() )
    {
      if ( ! empty( static::$critical_errors ) )
      {
        $this->die('');
      }
      else
      {
        return;
      }
    }

    $swal2_handle = 'cvy_swal2';

    wp_enqueue_script( $swal2_handle,
      'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.18/sweetalert2.all.min.js',
      [], null, true
    );

    $main_js_handle = 'cvy_env_validator';

    $main_js_path = realpath( __DIR__ . '/../assets' ) . '/index.js';

    $main_js_rel_path = str_replace( realpath( ABSPATH ) . DIRECTORY_SEPARATOR, '', $main_js_path );

    $main_js_url = home_url( '/' . $main_js_rel_path );
    $main_js_url = str_replace( DIRECTORY_SEPARATOR, '/', $main_js_url );

    wp_enqueue_script( $main_js_handle, $main_js_url, [ $swal2_handle ], filemtime( $main_js_path ), true );

    wp_localize_script( $main_js_handle, 'cvyEnvValidator', [
      'errors' => [
        'general' => static::$general_errors,
        'critical' => static::$critical_errors,
      ],
      'env' => Env::get_env(),
    ]);
  }

  static public function die( string $msg ) : void
  {
    if ( static::is_user_authorized_see_errors() )
    {
      $title = 'Setup Error';

      $msg = 'Environment setup <b>critical error</b>: ' . $msg;
    }
    else
    {
      $title = 'Scheduled maintenance';

      $msg = 'We\'re currently undergoing scheduled maintenance. Please come back in 10 minutes. Thank you for your patience!';
    }

    wp_die( $msg, $title );
  }

  static public function is_user_authorized_see_errors() : bool
  {
    return ! Env::is_prod() || current_user_can( 'administrator' );
  }
}