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
  protected $_conditions = array();   /** Array of JConfig_Hook_Condition */
  protected $_results    = array();   /** Array of JConfig_Hook_Result */


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
   * Add validation rules to a Validation instance for this hook
   *
   * @param Validation &$validation Validation instance
   *
   * @return int Number of rules added
   */
  public function add_validation_rules(Validation & $validation)
  {
    $nb_rules = 0;

    // @hack add a validation rule
    $nb_rules++;
    // $validation->rule($field, $callback, array($parameter1, $parameter2));

    return $nb_rules;
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
  public function condition($what, $operator, $value)
  {
    $this->_conditions[] = JConfig_Hook_Condition::factory($what, $operator, $value);

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
    foreach ($this->_results as $result)
    {
      $values = array_merge($values, $result->possible_values($field));
    }
    return $values;
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

} // End JConfig_Core_Hook