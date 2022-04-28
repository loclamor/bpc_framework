<?php
/**
 * http://stackoverflow.com/questions/9742564/how-to-find-annotations-in-a-php5-object
 * http://php.net/manual/fr/reflectionclass.construct.php
 */
class Entity_Loader {
	
	private static $parsedEntities = array();
	
	public static function parse(Entite $entity, $force = false) {
		$class = get_class($entity);
		if( $force || !array_key_exists($class, self::$parsedEntities) ) {
			
			$entityInfo = new Entity_Info($class);
			
			self::$parsedEntities[$class] = $entityInfo;
		}
		return self::$parsedEntities[$class];
	}
}