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
  protected $_hooks = array();   /** Array of JConfig_Hook */


  /**
   * Creates and initialises the JConfig_HookManager
   *
   * Can't be called, the factory() method must be used.
   *
   * @return JConfig_HookManager
   */
  protected function __construct()
  {
  }


  /**
   * Create a chainable instance of the JConfig_HookManager class
   *
   * @return JConfig_HookManager
   */
  public static function factory()
  {
    return new JConfig_HookManager;
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
    foreach ($hooks_config as $hook)
    {
      $this->_hooks[] = $hook;
    }

    return $this;
  }


  /**
   * Get all possible values for a field
   *
   * @param JConfig_Field $field Field to look into
   *
   * @return array Possible values for the field
   */
  public function possible_values(JConfig_Field $field)
  {
    $values = array();
    foreach ($this->_hooks as $hook)
    {
      $values = array_merge($values, $hook->possible_values($field));
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
    foreach ($this->_hooks as $hook)
    {
      if ($hook->run($model, $field))
      {
        return $this;
      }
    }

    return $this;
  }

} // End JConfig_Core_HookManager