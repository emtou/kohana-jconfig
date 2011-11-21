<?php
/**
 * Declares JConfig default configuration
 *
 * PHP version 5
 *
 * @group JConfig
 *
 * @category  JConfig
 * @package   JConfig
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-jconfig/tree/master/config/jconfig.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

return array(
  'caching' => FALSE,
  'cache' => array(
    'prefix'           => 'jconfig_',
    'use_superclosure' => FALSE,
  ),
);