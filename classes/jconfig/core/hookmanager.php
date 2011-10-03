<?php
/**
 * Declares JConfig_Core_HookManager
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hookmanager.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_HookManager
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hookmanager.php
 */
abstract class JConfig_Core_HookManager
{
  protected $_field = NULL;      /** Reference to the field */
  protected $_hooks = array();   /** Array of array of JConfig_Hook */


  /**
   * Creates and initialises the JConfig_HookManager
   *
   * Can't be called, the factory() method must be used.
   *
   * @param JConfig_Field &$field Reference to the field
   *
   * @return JConfig_HookManager
   */
  protected function __construct(JConfig_Field & $field)
  {
    $this->_field = $field;
    $this->_hooks = array(
      'formovalue' => array(),
      'validation' => array(),
      'update'     => array(),
    );
  }


  /**
   * Create a chainable instance of the JConfig_HookManager class
   *
   * @param JConfig_Field &$field Reference to the field
   *
   * @return JConfig_HookManager
   */
  public static function factory(JConfig_Field & $field)
  {
    return new JConfig_HookManager($field);
  }


  /**
   * Add hooks into internal container
   *
   * @param array $hooks_config Configuration for the hooks
   *
   * @return this
   */
  public function add_hooks(array $hooks_config)
  {
    foreach ($hooks_config as $alias => $hooks)
    {
      foreach ($hooks as $hook)
      {
        $hook->set_hookmanager($this);
      }

      $this->_hooks[$alias] = $hooks;
    }

    return $this;
  }


  /**
   * Add validation rules to a Validation instance for all the hooks
   *
   * @param Validation &$validation Validation instance
   *
   * @return int Number of rules added
   */
  public function add_validation_rules(Validation & $validation)
  {
    $callback = function(Validation $validation, $alias, $value, Jelly_Model $model, JConfig_HookManager $hookmanager)
    {
      return $hookmanager->check($validation, $alias, $value, $model);
    };

    $validation->rule(
        $this->_field->get_alias(),
        $callback,
        array(':validation', ':field', ':value', ':model', $this)
    );

    return 1;
  }


  /**
   * Validates a field with the hooks
   *
   * if there already are any validation error, skip these checks
   *
   * @param Validation  $validation Validation instance
   * @param string      $alias      Alias of the fied to validate
   * @param mixed       $value      Current value of the field
   * @param Jelly_Model $model      Current state of the model
   *
   * @return bool Is this field valid ?
   */
  public function check(Validation $validation, $alias, $value, Jelly_Model $model)
  {
    // if (sizeof($validation->errors()) > 0)
    //   return FALSE;

    $field = clone $this->_field;
    $field->reset();

    $this->run($model, $field);

    // Error from a hook
    if (is_string($field->get_error()))
    {
      $validation->error($alias, $field->get_error());
      return FALSE;
    }

    // Empty required field
    if ($field->get_required() AND $value == '')
    {
      $validation->error($alias, 'required');
      return FALSE;
    }

    // Mismatching forced value
    if ( ! is_null($field->get_forcedvalue())
        AND $value != $field->get_forcedvalue())
    {
      $validation->error($alias, 'mismatching_forced_value', array(':forcedvalue' => $field->get_forcedvalue()));
      return FALSE;
    }

    // Value not allowed
    if (sizeof($field->get_values()) > 0
        AND ! in_array($value, $field->get_values()))
    {
      $validation->error($alias, 'value_not_allowed', array(':allowedvalues' => implode(', ', $field->get_values())));
      return FALSE;
    }

    return TRUE;
  }


  /**
   * Run optional formovalue hooks on a value
   *
   * @param Jelly_Model   &$model Model instance feed hooks with
   * @param JConfig_Field &$field Field to run hooks from
   * @param mixed         &$value Value to interact on
   *
   * @return this
   */
  public function formo_value(Jelly_Model & $model, JConfig_Field & $field, & $value)
  {
    foreach ($this->_hooks['formovalue'] as $hook)
    {
      $hook->run_update($model, $value);
    }

    return $this;
  }


  /**
   * Get the field linked to this hookmanager
   *
   * @return JConfig_Field
   */
  public function get_field()
  {
    return $this->_field;
  }


  /**
   * Get all possible values for a field
   *
   * @return array Possible values for the field
   */
  public function possible_values()
  {
    $values = array();
    foreach ($this->_hooks['validation'] as $hook)
    {
      $hook->possible_values($values);
    }
    return $values;
  }


  /**
   * Run all the internal hooks on a field
   *
   * @param Jelly_Model   $model  Jelly model instance
   * @param JConfig_Field &$field Field to run hooks on
   *
   * @return this
   */
  public function run(Jelly_Model $model, JConfig_Field & $field)
  {
    foreach ($this->_hooks['validation'] as $hook)
    {
      if ($hook->run($model, $field))
      {
        return $this;
      }
    }

    return $this;
  }


  /**
   * Run optional update hooks on a value
   *
   * @param Jelly_Model   &$model Model to update value in
   * @param JConfig_Field &$field Field to run hooks on
   * @param mixed         &$value Value to populate the model with
   *
   * @return bool is the update hooked ?
   */
  public function update_value(Jelly_Model & $model, JConfig_Field & $field, & $value)
  {
    $ret = FALSE;
    foreach ($this->_hooks['update'] as $hook)
    {
      $hook->run_update($model, $value);

      $ret = TRUE;
    }

    return $ret;
  }

} // End JConfig_Core_HookManager