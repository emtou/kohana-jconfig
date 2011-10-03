<?php
/**
 * Declares JConfig_Core_Hook_Result
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook/result.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_Hook_Result
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/hook/result.php
 */
abstract class JConfig_Core_Hook_Result
{
  protected $_what      = NULL;   /** What does the result apply to ? */
  protected $_operation = NULL;   /** What operation to do ? */
  protected $_value     = NULL;   /** Optionnal value to the operation ? */


  /**
   * Creates and initialises the JConfig_Hook_Result
   *
   * Can't be called, the factory() method must be used.
   *
   * @param string $what      What does the result apply to ?
   * @param string $operation What operation to do ?
   * @param mixed  $value     Optionnal value to the operation ?
   *
   * @return JConfig_Hook_Result
   */
  protected function __construct($what, $operation, $value)
  {
    $this->_what      = $what;
    $this->_operation = $operation;
    $this->_value     = $value;
  }


  /**
   * Apply the result action to a field with a known alias
   *
   * @param mixed         $alias  Alias of the field
   * @param Jelly_Model   $model  Jelly model instance
   * @param JConfig_Field &$field Field to run hooks on
   *
   * @return bool Has the result been applied ?
   *
   * @throws JConfig_Exception Can't apply result: unknown result operation :resoperation
   *
   * @todo handle results on model fields
   */
  protected function _apply_operation($alias, Jelly_Model $model, JConfig_Field & $field)
  {
    switch ($this->_operation)
    {
      case 'description' :
        if (is_null($alias))
        {
          $field->set_description(( ! is_null($this->_value)?($this->_value):''));
        }
        return TRUE;

      case 'error' :
        if (is_null($alias))
        {
          $field->set_error(( ! is_null($this->_value)?($this->_value):':fieldname has an unknown error'));
        }
        return TRUE;

      case 'forcedvalue' :
        if (is_null($alias))
        {
          $field->set_forcedvalue(( ! is_null($this->_value)?($this->_value):(NULL)));
        }
        return TRUE;

      case 'required' :
        if (is_null($alias))
        {
          $field->set_required(( ! is_null($this->_value)?($this->_value):(TRUE)));
        }
        return TRUE;

      case 'values' :
        if (is_null($alias))
        {
          $field->set_values(( ! is_null($this->_value)?($this->_value):array()));
        }
        return TRUE;

    }

    throw new JConfig_Exception(
      'Can\'t apply result: unknown result operation :resoperation',
      array(':resoperation' => $this->_operation)
    );
  }


  /**
   * Apply the result action to a field with a known alias
   *
   * If the alias is NULL, the action is to be executed on the
   * field.
   *
   * @param mixed         $alias  Alias of the field
   * @param Jelly_Model   $model  Jelly model instance
   * @param JConfig_Field &$field Field to run hooks on
   *
   * @return bool Has the result been applied ?
   *
   * @throws JConfig_Exception Can't apply result: unknown field :alias in the model :modelname
   * @throws JConfig_Exception Can't apply result: don\'t handle results on model fields for now
   *
   * @todo handle results on model fields
   */
  protected function _apply_to_field($alias, Jelly_Model $model, JConfig_Field & $field)
  {
    if ( ! is_null($alias)
         AND ! isset($model->{$alias}))
    {
      throw new JConfig_Exception(
        'Can\'t apply result: unknown field :alias in the model :modelname',
        array(':alias' => $alias, ':modelname' => get_class($model))
      );
    }

    // @hack don't handle results on model fields for now
    if ( ! is_null($alias))
    {
      throw new JConfig_Exception(
        'Can\'t apply result: don\'t handle results on model fields for now',
        array()
      );
    }

    return $this->_apply_operation($alias, $model, $field);
  }


  /**
   * Create a chainable instance of the JConfig_Hook_Result class
   *
   * @param string $what      What does the result apply to ?
   * @param string $operation What operation to do ?
   * @param mixed  $value     Optionnal value to the operation ?
   *
   * @return JConfig_Hook_Result
   */
  public static function factory($what, $operation, $value)
  {
    return new JConfig_Hook_Result($what, $operation, $value);
  }


  /**
   * This result's summary
   *
   * @return string summary
   */
  public function __tostring()
  {
    return 'Hook Result '.$this->_what.' '.$this->_operation.' '.$this->_value;
  }


  /**
   * Apply the result action to a field
   *
   * @param Jelly_Model   $model  Jelly model instance
   * @param JConfig_Field &$field Field to run hooks on
   * @param mixed         &$value Optional value
   *
   * @return bool Has the result been applied ?
   *
   * @see JConfig_Hook::run()
   *
   * @throws JConfig_Exception Can't apply result: unknown result type :restype
   * @throws JConfig_Exception Can't apply result: unknown result :reswhat
   */
  public function apply(Jelly_Model $model, JConfig_Field & $field, & $value = NULL)
  {
    $submatches = array();
    if (preg_match('/^:([^:]+)(:(.+))?$/D', $this->_what, $submatches))
    {
      $type = $submatches[1];

      switch ($type)
      {
        case 'field' :
          if (isset($submatches[2]))
            return $this->_apply_to_field($submatches[2], $model, $field);
          else
            return $this->_apply_to_field(NULL, $model, $field);

        case 'value' :
          if (is_callable($this->_operation))
          {
            $value = call_user_func($this->_operation, $model, $field);
          }
          else
          {
            $value = $this->_operation;
          }
          return TRUE;

        default :
          throw new JConfig_Exception(
            'Can\'t apply result: unknown result type :restype',
            array(':restype' => $type)
          );
      }
    }

    throw new JConfig_Exception(
      'Can\'t apply result: unknown result :reswhat',
      array(':reswhat' => $this->_what)
    );
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
    if ($this->_what == ':field'
        AND $this->_operation == 'values')
    {
      foreach ($this->_value as $key => $value)
      {
        $values[$key] = $value;
      }
    }

    if ($this->_what == ':field'
        AND $this->_operation == 'value')
    {
      $values[] = $this->_value;
    }
  }

} // End JConfig_Core_Hook_Result