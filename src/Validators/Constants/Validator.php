<?php
namespace Cvy\WP\Env\Validators\Constants;
use Cvy\WP\Env\ErrorsHandler;

if (!defined('ABSPATH')) exit;

class Validator
{
  public static function validate_soft( string $const_name, array $allowed_values = null ) : void
  {
    static::validate( $const_name, false, $allowed_values );
  }

  public static function validate_strict( string $const_name, array $allowed_values = null ) : void
  {
    static::validate( $const_name, true, $allowed_values );
  }

  private static function validate( string $const_name, bool $strict, array $allowed_values = null ) : void
  {
    $error_msg = static::get_error_msg( $const_name, $allowed_values );

    if ( $error_msg )
    {
      if ( $strict )
      {
        ErrorsHandler::die( $error_msg );
      }
      else
      {
        ErrorsHandler::add_error( $error_msg );
      }
    }
  }

  private static function get_error_msg( string $const_name, array $allowed_values = null ) : string
  {
    if ( ! defined( $const_name ) )
    {
      return "$const_name is not defined!";
    }
    else if ( ! empty( $allowed_values ) )
    {
      $value = constant( $const_name );

      if ( ! in_array( $value, $allowed_values, true ) )
      {
        if ( is_bool( $value ) )
        {
          $value_str = $value ? 'true' : 'false';
        }
        else
        {
          $value_str = $value;
        }

        $allowed_values_str = implode( '", "', array_map(
          function( $item ) : string
          {
            if ( is_bool( $item ) )
            {
              return $item ? 'true' : 'false';
            }

            return $item;
          },
          $allowed_values
        ));

        return sprintf( 'Unexpected value of %s!<br> Passed value: "%s".<br> Expected %s: "%s".',
          $const_name,
          $value_str,
          count( $allowed_values ) > 1 ? 'values' : 'value',
          $allowed_values_str
        );
      }
    }

    return '';
  }
}