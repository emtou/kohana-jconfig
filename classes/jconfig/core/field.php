<?php
/**
 * Declares JConfig_Core_Field
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/field.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_Field
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/field.php
 */
abstract class JConfig_Core_Field
{
  protected $_alias        = '';      /** alias of the field */
  protected $_config       = NULL;    /** driver for the field */
  protected $_description  = '';      /** description of the field */
  protected $_driver       = NULL;    /** configuration array */
  protected $_error        = FALSE;   /** is this field in error state ? */
  protected $_extraparams  = array(); /** extra params for the jelly field */
  protected $_forcedvalue  = NULL;    /** forced value for the field */
  protected $_formo_params = NULL;    /** extra formo params */
  protected $_help         = NULL;    /** help message */
  protected $_hookmanager  = NULL;    /** hook manager */
  protected $_label        = '';      /** label of the field */
  protected $_required     = FALSE;   /** is this field required ? */
  protected $_rules        = array(); /** standard constraint rules */
  protected $_values       = NULL;    /** allowed values for the field */


  /**
   * Creates and initialises the JConfig_Field
   *
   * Can't be called, the factory() method must be used.
   *
   * @param string $alias  Alias of the field
   * @param array  $config Configuration of the field
   *
   * @return JConfig_Field
   */
  protected function __construct($alias, array $config)
  {
    $this->_alias  = $alias;
    $this->_config = $config;

    $this->_load();
  }


  /**
   * Load internal parameters, hookmanager and rules
   *
   * @return null
   */
  protected function _load()
  {
    $this->reset();

    $this->_load_hookmanager();

    $this->_load_rules();
  }


  /**
   * Load the hookmanager
   *
   * @return null
   *
   * @see JConfig_Field::load()
   */
  protected function _load_hookmanager()
  {
    $this->_hookmanager = JConfig_HookManager::factory($this);

    if (isset($this->_config['hooks']))
    {
      $this->_hookmanager->add_hooks($this->_config['hooks']);
    }
  }


  /**
   * Load the hooked values regex rule if needed
   *
   * @return null
   *
   * @see JConfig_Field::load()
   */
  protected function _load_rule_hooked_values()
  {
    $values = $this->_hookmanager->possible_values(clone $this);
    if (sizeof($values) > 0)
    {
      $values = array_unique(array_values($values));
      $regex  = '/^('.implode('|', $values).')$/D';

      $this->_rules[] = array('regex', array(':value', $regex));
    }
  }


  /**
   * Load the mandatory values regex rule if needed
   *
   * @return null
   *
   * @see JConfig_Field::load()
   */
  protected function _load_rule_mandatory_values()
  {
    if (sizeof($this->_values) > 0)
    {
      $req            = ( ! $this->_required)?'?':'';
      $this->_rules[] = array(
        'regex',
        array(
          ':value',
          '/^('.implode('|', $this->_values).')'.$req.'$/D',
        )
      );
    }
  }


  /**
   * Load the required rule if needed
   *
   * @return null
   *
   * @see JConfig_Field::load()
   */
  protected function _load_rule_required()
  {
    if ($this->_required)
    {
      $this->_rules[] = array('not_empty');
    }
  }


  /**
   * Load rules
   *
   * @return null
   *
   * @see JConfig_Field::load()
   */
  protected function _load_rules()
  {
    $this->_load_rule_required();

    $this->_load_rule_mandatory_values();

    $this->_load_rule_hooked_values();
  }


  /**
   * Checks if the field is already loaded
   *
   * @return bool Field is loaded ?
   */
  protected function _loaded()
  {
    return ($this->_config != NULL);
  }


  /**
   * Create a chainable instance of the JConfig_Field class
   *
   * @param string $alias  Alias of the field
   * @param array  $config Configuration of the field
   *
   * @return JConfig_Field
   */
  public static function factory($alias, array $config)
  {
    return new JConfig_Field($alias, $config);
  }


  /**
   * Add validation rules to a Validation instance for this field
   *
   * @param Validation &$validation Validation instance
   *
   * @return int Number of rules added
   */
  public function add_validation_rules(Validation & $validation)
  {
    return $this->_hookmanager->add_validation_rules($validation);
  }


  /**
   * Generates a formo field configuration for this field
   *
   * @param Jelly_Model $model Model instance
   *
   * @return array Formo field configuration
   *
   * @todo rename the 'aide' parameter to english !
   */
  public function formo($model)
  {
    // Run hooks
    $this->_hookmanager->run($model, $this);

    $formo_params = array();

    if (is_array($this->_formo_params))
    {
      $formo_params = $this->_formo_params;
    }

    $formo_params['alias']    = $this->_alias;
    $formo_params['label']    = $this->_label;
    $formo_params['required'] = $this->_required;
    $formo_params['rules']    = $this->_rules;

    if ($this->_help)
    {
      $formo_params['aide'] = $this->_help;
    }

    if ($this->_values)
    {
      $formo_params['options'] = array_merge(array('--select--'=>''), $this->_values);
    }

    // disable field ?
    if ( ! is_null($this->get_forcedvalue()))
    {
      $formo_params['editable'] = FALSE;

      if (isset($formo_params['options']))
      {
        $formo_params['options'] = array_merge(array(''=>$this->get_forcedvalue()), $formo_params['options']);
      }
      else
      {
        $formo_params['value'] = $this->get_forcedvalue();
      }
    }

    return $formo_params;
  }


  /**
   * Get formo field value from the model
   *
   * @param Jelly_Model &$model Model to fetch values from
   *
   * @return mixed formo value
   */
  public function formo_value(Jelly_Model & $model)
  {
    $value = $model->{$this->_alias};

    // Run hooks
    $this->_hookmanager->formo_value($model, $this, $value);

    return $value;
  }


  /**
   * Get this fields' alias
   *
   * @return string Alias
   */
  public function get_alias()
  {
    return $this->_alias;
  }


  /**
   * Get this fields' description
   *
   * @return string Description of the field
   */
  public function get_description()
  {
    return $this->_description;
  }


  /**
   * Get this fields' error state
   *
   * @return mixed Error state
   */
  public function get_error()
  {
    return $this->_error;
  }


  /**
   * Get this fields' forced value
   *
   * @return mixed Forced value
   */
  public function get_forcedvalue()
  {
    return $this->_forcedvalue;
  }


  /**
   * Get this fields' label
   *
   * @return string Label
   */
  public function get_label()
  {
    return $this->_label;
  }

  /**
   * Get this fields' required flag
   *
   * @return bool Is this field required ?
   */
  public function get_required()
  {
    return $this->_required;
  }


  /**
   * Get this fields' values
   *
   * @return array Values
   */
  public function get_values()
  {
    return $this->_values;
  }


  /**
   * Initialises the Jelly field
   *
   * @return Jelly_Field instance
   */
  public function initialize()
  {
    $this->_load();

    $params          = array();
    $params['label'] = $this->_label;
    $params['name']  = $this->_label;
    $params['rules'] = $this->_rules;

    $params = array_merge($params, $this->_extraparams);

    $driver = $this->_driver;
    return new $driver($params);
  }


  /**
   * Resets all parameters from configuration (except hooks)
   *
   * @return this
   */
  public function reset()
  {
    // Load values from config
    $this->_description  = (isset($this->_config['description'])?($this->_config['description']):'');
    $this->_driver       = $this->_config['driver'];
    $this->_extraparams  = (isset($this->_config['extraparams'])?($this->_config['extraparams']):(array()));
    $this->_forcedvalue  = (isset($this->_config['forcedvalue'])?($this->_config['forcedvalue']):(NULL));
    $this->_formo_params = (isset($this->_config['formo_params'])?($this->_config['formo_params']):(NULL));
    $this->_label        = $this->_config['label'];
    $this->_help         = (isset($this->_config['help'])?($this->_config['help']):(NULL));
    $this->_required     = (isset($this->_config['required'])?($this->_config['required']):(FALSE));
    $this->_rules        = (isset($this->_config['rules'])?($this->_config['rules']):(array()));
    $this->_values       = (isset($this->_config['values'])?($this->_config['values']):(NULL));
  }


  /**
   * Set internal description
   *
   * @param string $description Description of the field
   *
   * @return this
   */
  public function set_description($description)
  {
    $this->_description = $description;
  }


  /**
   * Set internal error state
   *
   * @param mixed $error FALSE or error label
   *
   * @return this
   */
  public function set_error($error)
  {
    $this->_error = $error;
  }


  /**
   * Set internal required flag
   *
   * @param bool $required Is this field required ?
   *
   * @return this
   */
  public function set_required($required)
  {
    $this->_required = $required;
  }


  /**
   * Set internal forced value
   *
   * @param mixed $forcedvalue Forced value
   *
   * @return this
   */
  public function set_forcedvalue($forcedvalue)
  {
    $this->_forcedvalue = $forcedvalue;
  }


  /**
   * Set internal values
   *
   * @param array $values Values
   *
   * @return this
   */
  public function set_values(array $values)
  {
    $this->_values = $values;

    return $this;
  }


  /**
   * Update value in the model
   *
   * @param Jelly_Model &$model Model to update value in
   * @param mixed       $value  Value to populate the model with
   *
   * @return this
   */
  public function update_value(Jelly_Model & $model, $value)
  {
    // Run hooks
    $this->_hookmanager->update_value($model, $this, $value);

    $model->{$this->_alias} = $value;

    return $this;
  }

}