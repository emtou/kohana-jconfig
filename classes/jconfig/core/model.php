<?php
/**
 * Declares JConfig_Core_Model
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/model.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides JConfig_Core_Model
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jconfig/core/model.php
 */
abstract class JConfig_Core_Model
{
  protected $_alias  = '';        /** alias of the model */
  protected $_config = NULL;      /** configuration array */
  protected $_fields = array();   /** fields' configurations */


  /**
   * Creates and initialises the JConfig_Model
   *
   * Can't be called, the factory() method must be used.
   *
   * @param string $alias Alias of the model
   *
   * @return JConfig_Model
   */
  protected function __construct($alias)
  {
    $this->_alias = $alias;

    $this->_load();
  }


  /**
   * Load the model from configuration file
   *
   * @return null
   *
   * @throws JConfig_Exception Can't load model :alias: configuration file :fname not found
   */
  protected function _load()
  {
    if ($this->_loaded())
    {
      return;
    }

    $this->_config = Kohana::config('jelly/'.$this->_alias);
    if ( ! $this->_config)
    {
      throw new JConfig_Exception(
        'Can\'t load model :alias: configuration file :fname not found',
        array(
          'alias' => $this->_alias,
          'fname' => 'jelly/'.$this->_alias,
        )
      );
    }

    foreach ($this->_config['fields'] as $field_alias => $field_config)
    {
      $this->_fields[$field_alias] = JConfig_Field::factory($field_alias, $field_config);
    }
  }


  /**
   * Checks if the model is already loaded
   *
   * @return bool Model is loaded ?
   */
  protected function _loaded()
  {
    return ($this->_config != NULL);
  }


  /**
   * Create a chainable instance of the JConfig_Model class
   *
   * @param string $alias Alias of the model
   *
   * @return JConfig_Model
   */
  public static function factory($alias)
  {
    return new JConfig_Model($alias);
  }


  /**
   * Add validation rules to a Validation instance for this model
   *
   * @param Validation &$validation Validation instance
   *
   * @return int Number of rules added
   */
  public function add_validation_rules(Validation & $validation)
  {
    $nb_rules = 0;

    foreach ($this->_fields as $field)
    {
      $nb_rules += $field->add_validation_rules($validation);
    }

    return $nb_rules;
  }


  /**
   * Generates a list of formo fields' configuration
   *
   * @param Jelly_Model $model         Model instance
   * @param array       $field_aliases Aliases of the fields
   *
   * @return array Formo fields configuration
   */
  public function formo_fields($model, array $field_aliases)
  {
    $formo_fields = array();

    foreach ($field_aliases as $field_alias)
    {
      $formo_fields[] = $this->_fields[$field_alias]->formo($model);
    }

    return $formo_fields;
  }


  /**
   * Fill in array with model fields values
   *
   * @param Jelly_Model &$model  Model to fetch values from
   * @param array       &$values Array to fill in
   *
   * @return this
   */
  public function formo_values(Jelly_Model & $model, array & $values)
  {
    foreach (array_keys($values) as $alias)
    {
      if (isset($this->_fields[$alias]))
      {
        $values[$alias] = $this->_fields[$alias]->formo_value($model);
      }
    }

    return $this;
  }


  /**
   * Initialises the Jelly model
   *
   * @param Jelly_Meta &$meta Jelly meta instance
   *
   * @return null
   */
  public function initialize(Jelly_Meta & $meta)
  {
    $fields = array();

    foreach ($this->_fields as $field_alias => $field)
    {
      $fields[$field_alias] = $field->initialize();
    }

    $meta->table($this->_config['tablename'])->fields($fields);
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
    if (preg_match('/^jconfig\/[^\/]+\/_external\.([^\.]+)\.(.+)$/D', $error, $submatches))
    {
      $alias     = $submatches[1];
      $errorcode = $submatches[2];

      switch ($errorcode)
      {
        case 'required':
          return __(
              ':field must not be empty',
              array(':field' => $this->_fields[$alias]->get_label())
          );

        case 'mismatching_forced_value':
        case 'value_not_allowed':
          return __(
              ':field cannot have this value',
              array(':field' => $this->_fields[$alias]->get_label())
          );

        default :
          return $errorcode;
      }
    }

    return $error;
  }


  /**
   * Update fields' values from an array
   *
   * @param Jelly_Model &$model Model to update values in
   * @param array       $values Values to populate the model with
   *
   * @return this
   */
  public function update_values(Jelly_Model & $model, array $values)
  {
    foreach ($values as $alias => $value)
    {
      if (isset($this->_fields[$alias]))
      {
        $this->_fields[$alias]->update_value($model, $value);
      }
    }

    return $this;
  }

}