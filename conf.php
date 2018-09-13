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

/*
 * Define application environment. dev / prod
 * if dev mod, application will log more data by default
 */
if( !defined('APPLICATION_ENV') )
    define('APPLICATION_ENV', 'prod');
    
/*
 * Define application log level
 * every log levels strictly highter than LOG_LEVEL will be ignored
 */
if( !defined('LOG_LEVEL') )
    define('LOG_LEVEL', 3);
    
    
/*
 * Allow entities to install themselve from DB_type data
 */
if( !defined('ENTITIES_AUTO_INSTALL') )
    define('ENTITIES_AUTO_INSTALL', false);
    
if( !defined('BPCF_ROOT') )
    define('BPCF_ROOT', '.');
    
ob_start();

require_once BPCF.'/functions.php';

/*
 * load core classes
 * optional : uncomments the following import line to force all core classes loading.
 * Elsewhere, core classes would be loaded on demand.
 */
//import( BPCF.'.core.*');