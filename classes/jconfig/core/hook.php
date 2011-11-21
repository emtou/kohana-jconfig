<?php
/**
 * Declares JConfig_Core_Hook
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_Hook
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook.php
 */
abstract class JConfig_Core_Hook
{
  protected $_conditions  = array();   /** Array of JConfig_Hook_Condition */
  protected $_hookmanager = NULL;      /** Reference to the hookmanager instance */
  protected $_results     = array();   /** Array of JConfig_Hook_Result */


  /**
   * Creates and initialises the JConfig_Hook
   *
   * Can't be called, the factory() method must be used.
   *
   * @return JConfig_Hook
   */
  protected function __construct()
  {
  }


  /**
   * Create a chainable instance of the JConfig_Hook class
   *
   * @return JConfig_Hook
   */
  public static function factory()
  {
    return new JConfig_Hook;
  }


  /**
   * Add a namespace all the hook's conditions
   *
   * @param string $namespace Namespace to add
   *
   * @return this
   */
  public function add_namespace($namespace)
  {
    foreach (array_keys($this->_conditions) as $condition_nb)
    {
      $this->_conditions[$condition_nb]->add_namespace($namespace);
    }

    return $this;
  }


  /**
   * Adds a condition to the internal container
   *
   * @param string $what     What does the condition apply to ?
   * @param string $operator What operates on the value ?
   * @param mixed  $value    What is the value ?
   *
   * @return this
   */
  public function condition($what, $operator, $value = NULL)
  {
    $this->_conditions[] = JConfig_Hook_Condition::factory($what, $operator, $value);

    return $this;
  }


  /**
   * Initialise the hook before adding it to the manager
   *
   * - Encapsulate all results' callbacks if caching and cache.use_superclosure configuration flags are set
   *
   * @return this
   */
  public function init()
  {
    if ($this->_jconfig->config['caching']
        and $this->_jconfig->config['cache']['use_superclosure'])
    {
      foreach ($this->_results as $result)
      {
        $result->encapsulate_callbacks_in_superclosure();
      }
    }
  }


  /**
   * Get all possible values for a field
   *
   * @param array &$values Array of possible values to fill
   *
   * @return null
   */
  public function possible_values(array & $values)
  {
    foreach ($this->_results as $result)
    {
      $result->possible_values($values);
    }
  }


  /**
   * Adds a result action to the internal container
   *
   * @param string $what      What does the result apply to ?
   * @param string $operation What operation to do ?
   * @param mixed  $value     Optionnal value to the operation ?
   *
   * @return this
   */
  public function result($what, $operation, $value = NULL)
  {
    $this->_results[] = JConfig_Hook_Result::factory($what, $operation, $value);

    return $this;
  }


  /**
   * Run the hook on a field
   *
   * @param Jelly_Model   $model  Jelly model instance
   * @param JConfig_Field &$field Field to run hooks on
   *
   * @return this
   */
  public function run(Jelly_Model $model, JConfig_Field & $field)
  {
    $must_run = TRUE;
    foreach ($this->_conditions as $condition)
    {
      if ( ! $condition->applies($model, $field))
      {
        $must_run = FALSE;
        break;
      }
    }

    if ( ! $must_run)
      return FALSE;

    foreach ($this->_results as $result)
    {
      $result->apply($model, $field);
    }

    return TRUE;
  }


  /**
   * Run this update hook on a value
   *
   * @param Jelly_Model &$model Model to update value in
   * @param mixed       &$value Value to populate the model with
   *
   * @return this
   */
  public function run_update(Jelly_Model & $model, & $value)
  {
    $must_run = TRUE;
    foreach ($this->_conditions as $condition)
    {
      if ( ! $condition->applies($model, NULL, $value))
      {
        $must_run = FALSE;
      }
    }

    if ( ! $must_run)
      return FALSE;

    foreach ($this->_results as $result)
    {
      $field = $this->_hookmanager->get_field();

      $result->apply($model, $field, $value);
    }

    return TRUE;
  }


  /**
   * Set the internal reference to the hookmanager
   *
   * @param JConfig_HookManager &$hookmanager Reference to the hookmanager instance
   *
   * @return this
   */
  public function set_hookmanager(JConfig_HookManager & $hookmanager)
  {
    $this->_hookmanager = $hookmanager;

    return $this;
  }


  /**
   * Set the internal reference to the JConfig core
   *
   * @param JConfig $jconfig Reference to the JConfig core instance
   *
   * @return this
   */
  public function set_jconfig(JConfig $jconfig)
  {
    $this->_jconfig = $jconfig;

    return $this;
  }

} // End JConfig_Core_Hook