bpc_framework !
=============

lightweight PHP Framework
-------------------------

With bpc_framework, simply create a MVC web application !

Juste write your controller with their actions and the corresponding views, following this architecture :

    + bpcf //the framework source files
    + conf
      - init.php //this recommanded file set framework's constants
    + controller
      - Default.php //contains the Controller_Default class with an index action which are default controller and action
      - your others controllers...
    + view
      + default
        - index.phtml //the view for the index action of the default controller
        - your others views for default controller
      + your others controller's views
    - index.php //this is the root file of your application
  
The index.php file could looks like this :

    <?php 
      require_once 'conf/init.php';
    ?>
    <!DOCTYPE html>
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>
        <?php
          echo Site::getInstance()->getTitle();
        ?>
        </title>
      </head>
      <body>
    	<?php
    	  echo Site::getInstance()->getContent();
    	?>
      </body>
    </html>
  
The conf/init.php file could looks like this :

    //set a title to your application
    define('SITE_NAME', 'My BPCF Application');
    
    //table name prefix for the database
    define('TABLE_PREFIX','mondo2_');
    
    //default controller and action
    define('DEFAULT_CONTROLLER', 'default');
    define('DEFAULT_ACTION', 'index');
    
    //define the location folder of the BPC Framework
    define('BPCF', 'bpcf');
    
    require_once BPCF.'/conf.php';
