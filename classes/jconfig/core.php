<?php
/**
 * Declares JConfig_Core
 *
 * PHP version 5
 *
 * @group jconfig
 *
 * @category  JConfig
 * @package   JConfig
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core
 *
 * PHP version 5
 *
 * @group jconfig
 *
 * @category  JConfig
 * @package   JConfig
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core.php
 */
abstract class JConfig_Core
{
  protected $_models = array();   /** Loaded models' configurations */


  /**
   * Creates and initialises the JConfig
   *
   * Can't be called, the factory() method must be used.
   *
   * @return JConfig
   */
  protected function __construct()
  {
  }


  /**
   * Create a chainable instance of the JConfig class
   *
   * @return JConfig
   */
  public static function factory()
  {
    return new JConfig;
  }


  /**
   * Generates a list of formo fields' configuration
   *
   * @param string      $model_alias   Alias of the model
   * @param Jelly_Model $model         Model instance
   * @param array       $field_aliases Aliases of the fields
   *
   * @return array Formo fields configuration
   */
  public function formo_fields($model_alias, Jelly_Model $model, array $field_aliases)
  {
    $this->load($model_alias);

    return $this->_models[$model_alias]->formo_fields($model, $field_aliases);
  }


  /**
   * Returns an internal model
   *
   * @param string $model_alias Alias of the model
   *
   * @return JConfig_Model
   */
  public function get_model($model_alias)
  {
    $this->load($model_alias);

    return $this->_models[$model_alias];
  }


  /**
   * Get the validation rules for the model
   *
   * @param string $model_alias Alias of the model
   *
   * @return Validation validation instance
   */
  public function get_validation_rules($model_alias)
  {
    $this->load($model_alias);

    return $this->_models[$model_alias]->get_validation_rules();
  }


  /**
   * Initialises a Jelly model from configuration file
   *
   * @param string     $model_alias Alias of the model to load
   * @param Jelly_Meta &$meta       Jelly meta instance
   *
   * @return null
   */
  public function initialize($model_alias, Jelly_Meta & $meta)
  {
    $this->load($model_alias);

    $this->_models[$model_alias]->initialize($meta);
  }


  /**
   * Load a model from configuration file
   *
   * @param string $model_alias Alias of the model to load
   *
   * @return null
   */
  public function load($model_alias)
  {
    if ($this->loaded($model_alias))
    {
      return;
    }

    $this->_models[$model_alias] = JConfig_Model::factory($model_alias);
  }


  /**
   * Checks if a model is already loaded
   *
   * @param string $model_alias Alias of the model to load
   *
   * @return bool Model is loaded ?
   */
  public function loaded($model_alias)
  {
    return isset($this->_models[$model_alias]);
  }


  /**
   * Parse errors from a validation exception
   *
   * @param Jelly_Validation_Exception &$exception Validaiton exception
   *
   * @return array errors
   */
  public function parse_validation_exception(Jelly_Validation_Exception & $exception)
  {
    $errors = array();

    foreach ($exception->errors('jconfig') as $alias => $error)
    {
      if ($alias == '_external')
      {
        foreach ($error as $alias => $error2)
        {
          $error2 = $this->translate_error($error2);

          if ( ! isset($errors[$alias]))
          {
            $errors[$alias] = array();
          }

          $errors[$alias][] = $error2;
        }
      }
      else
      {
        $error = $this->translate_error($error);

        if ( ! isset($errors[$alias]))
        {
          $errors[$alias] = array();
        }

        $errors[$alias][] = $error;
      }
    }

    return $errors;
  }


  /**
   * Translates an error path in plain human language
   *
   * @param string $error Error text
   *
   * @return string Error in plain human language
   */
  public function translate_error($error)
  {
    // Extract model alias from error text
    $submatches = array();
    if (preg_match('/^jconfig\/([^\/]+)\//', $error, $submatches))
    {
      $model_alias = $submatches[1];

      $this->load($model_alias);

      return $this->_models[$model_alias]->translate_error($error);
    }

    return $error;
  }

}