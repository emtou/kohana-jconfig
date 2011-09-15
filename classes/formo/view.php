<?php
/**
 * Declares Formo_View
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/formo_view.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides Formo_View
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
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/classes/formo_view.php
 */
class Formo_View extends Formo_Core_View
{

  /**
   * Retrieve error
   *
   * @access public
   * @return mixed
   */
  public function error()
  {
    $errors         = array();
    $internal_error = $this->_field->error();

    if ($internal_error)
      return 'INTERNAL: '.$internal_error;

    if (isset($this->_field->external_errors))
    {
      if (is_array($this->_field->external_errors))
        return 'EXTERNAL: '.$this->_field->external_errors[0];

      return 'EXTERNAL: '.$this->_field->external_errors;
    }

    return '';
  }

}