<?php
/**
 * Declares Jelly_Model
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jelly/model.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides Jelly_Model
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/jelly/model.php
 */
class Jelly_Model extends Jelly_Core_Model
{

  /**
   * Validates the current model's data
   *
   * Difference with mint Jelly_Model::check() :
   *
   * - Only validate fields with aliases found in extra validation if present
   *
   * @param Validation|null $extra_validation extra validation
   *
   * @return Jelly_Core_Model
   *
   * @throws Jelly_Validation_Exception
   */
  public function check($extra_validation = NULL)
  {
    $key = $this->_original[$this->_meta->primary_key()];

    // Determine if any external validation failed
    $extra_errors = ($extra_validation instanceof Validation AND ! $extra_validation->check());

    // For loaded models, we're only checking what's changed, otherwise we check it all
    $data = $key ? ($this->_changed) : ($this->_changed + $this->_original);

    // Only difference with mint Jelly_Model::check()
    if ($extra_validation instanceof Validation)
    {
      $new_data = array();

      foreach (array_keys($extra_validation->getArrayCopy()) as $alias)
      {
        if (isset($data[$alias]))
        {
          $new_data[$alias] = $data[$alias];
        }
      }
      $data = $new_data;
      $key  = 42; /** Fake key to force update mode (only check data fields) */
    }

    // Always build a new validation object
    $this->_validation($data, (bool) $key);

    // Run validation
    if ( ! $this->_valid)
    {
      $array = $this->_validation;

      $this->_meta->events()->trigger(
          'model.before_validate',
          $this, array($this->_validation)
      );

      if (($this->_valid = $array->check()) === FALSE OR $extra_errors)
      {
        $exception = new Jelly_Validation_Exception($this->_meta->model(), $array);

        if ($extra_errors)
        {
          // Merge any possible errors from the external object
          $exception->add_object('_external', $extra_validation);
        }

        throw $exception;
      }

      $this->_meta->events()->trigger(
          'model.after_validate',
          $this, array($this->_validation)
      );
    }
    else
    {
      $this->_valid = TRUE;
    }

    return $this;
  }

} // end class Jelly_Model