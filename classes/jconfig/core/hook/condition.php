<?php
/**
 * Declares JConfig_Core_Hook_Condition
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook/condition.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_Hook_Condition
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook/condition.php
 */
abstract class JConfig_Core_Hook_Condition
{
  protected $_what     = NULL;   /** What does the condition apply to ? */
  protected $_operator = NULL;   /** What operates on the value ? */
  protected $_value    = NULL;   /** What is the value ? */


  /**
   * Creates and initialises the JConfig_Hook_Condition
   *
   * Can't be called, the factory() method must be used.
   *
   * @param string $what     What does the condition apply to ?
   * @param string $operator What operates on the value ?
   * @param mixed  $value    What is the value ?
   *
   * @return JConfig_Hook_Condition
   */
  protected function __construct($what, $operator, $value)
  {
    $this->_what     = $what;
    $this->_operator = $operator;
    $this->_value    = $value;
  }


  /**
   * Checks if the condition applies to a known field
   *
   * @param mixed         $value Value to check against
   * @param Jelly_Model   $model Jelly model instance
   * @param JConfig_Field $field Field to run hooks on
   *
   * @return bool Does the condition apply ?
   *
   * @throws JConfig_Exception Can't check if condition applies: unknown condition operator :condoperator
   */
  protected function _applies_operator($value, Jelly_Model $model, $field)
  {
    switch ($this->_operator)
    {
      case '=' :
        return ($value == $this->_value);

      case 'match' :
        return (preg_match($this->_value, $value) == 1);

      case 'required' :
        $expected_required = (is_bool($this->_value)?($this->_value):(TRUE));
        $field_required    = $field->get_required();
        return ($field_required == $expected_required);
    }

    throw new JConfig_Exception(
      'Can\'t check if condition applies: unknown condition operator :condoperator',
      array(':condoperator' => $this->_operator)
    );
  }


  /**
   * Checks if the condition applies to a known field
   *
   * @param string        $alias Alias of the field
   * @param Jelly_Model   $model Jelly model instance
   * @param JConfig_Field $field Field to run hooks on
   *
   * @return bool Does the condition apply ?
   */
  protected function _applies_to_field($alias, Jelly_Model $model, $field)
  {
    if ( ! isset($model->{$alias}))
    {
      return FALSE;
    }

    $value = $model->{$alias};
    return $this->_applies_operator($value, $model, $field);
  }


  /**
   * Create a chainable instance of the JConfig_Hook_Condition class
   *
   * @param string $what     What does the condition apply to ?
   * @param string $operator What operates on the value ?
   * @param mixed  $value    What is the value ?
   *
   * @return JConfig_Hook_Condition
   */
  public static function factory($what, $operator, $value)
  {
    return new JConfig_Hook_Condition($what, $operator, $value);
  }


  /**
   * Checks if the condition applies
   *
   * @param Jelly_Model   $model Jelly model instance
   * @param JConfig_Field $field Field to run hooks on
   * @param mixed         $value Optional value
   *
   * @return bool Does the condition apply ?
   *
   * @throws JConfig_Exception Can't check if condition applies: unknown condition type :condtype
   * @throws JConfig_Exception Can't check if condition applies: unknown condition :condwhat
   */
  public function applies(Jelly_Model $model, $field, $value = NULL)
  {
    $submatches = array();
    if (preg_match('/^:([^:]+)(:(.+))?$/D', $this->_what, $submatches))
    {
      $type = $submatches[1];

      switch ($type)
      {
        case 'field' :
          if (is_null($field))
          {
            throw new JConfig_Exception(
              'Can\'t check if field condition applies: field conditions not allowed',
              array()
            );
          }
          if (isset($submatches[2]))
          {
            $alias = $submatches[3];
            return $this->_applies_to_field($alias, $model, $field);
          }

          return $this->_applies_operator($value, $model, $field);

        case 'value' :
          if (is_null($value))
          {
            throw new JConfig_Exception(
              'Can\'t check if value condition applies: no value has been given',
              array()
            );
          }

          return $this->_applies_operator($value, $model, $field);

        default :
          throw new JConfig_Exception(
            'Can\'t check if condition applies: unknown condition type :condtype',
            array(':condtype' => $type)
          );
      }
    }

    throw new JConfig_Exception(
      'Can\'t check if condition applies: unknown condition :condwhat',
      array(':condwhat' => $this->_what)
    );
  }

} // End JConfig_Core_Hook_Condition