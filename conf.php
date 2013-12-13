<?php
/*
 * default plugin folder
 */
if( !defined('PLUGINS_FOLDER') )
    define('PLUGINS_FOLDER', BPCF.'/plugin');

/*
 * define if server return are JSON by default
 * This could be overide in actions by defining $this->isJSON
 */
if( !defined('DEFAULT_JSON') )
    define('DEFAULT_JSON', false);

/*
 * define if server's JSON returns can be Cross domain by default
 * This could be overide in actions by defining $this->allowAllOrigin
 */
if( !defined('DEFAULT_ALLOW_ALL_ORIGIN') )
    define('DEFAULT_ALLOW_ALL_ORIGIN', false);

require_once BPCF.'/functions.php';

/*
 * load core classes
 * optional : uncomments the following import line to force all core classes loading.
 * Elsewhere, core classes would be loaded on demand.
 */
//import( BPCF.'.core.*');