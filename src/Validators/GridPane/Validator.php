<?php
namespace Cvy\WP\Env\Validators\GridPane;
use Cvy\WP\Env\Env;
use Cvy\WP\Env\ErrorsHandler;

if (!defined('ABSPATH')) exit;

class Validator
{
  static public function validate() : void
  {
    if ( ! Env::is_loc() && file_exists( ABSPATH . '/wp-config.php' ) )
    {
      ErrorsHandler::add_error( 'wp-config.php file is detected in htdocs dir. It must be removed.' );
    }
  }
}