<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// TODO Property ebenfalls behandeln
// TODO Statische Aufrufe ebenfalls behandeln
// TODO Beim Aufruf prüfen ob die Methode im Original Protected ist
// TODO Bei doppelten Properties/Methodes bestimmen welcher Parent gilt

/**
 * Abstrakte Klasse
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 *      
 *       verwendung:
 *       im konstruktor der abgeleiteten klasse die parent classes mit
 *       parent::addExtendedClass($param1, ..., $paramn);
 *       hinzufügen.
 */
abstract class MultiExtender {
	private $methods = array();
	private $properties = array();
	private $objs = array();
	const C_METHOD = 'methods';
	const C_PROPERTY = 'properties';

	/**
	 * ein weitere Extended Class hinzufügen
	 *
	 * @param String $name        	
	 * @param Array $params        	
	 */
	protected function addExtendedClass($className, $params = array()) {
		// Eine Wrapperklasse erstellen, damit die Protected-Methodes Public werden
		$name = self::createWrapperClass($className);
		// Falls nur ein Parameter angegeben wird, diesen in einen Array schreiben
		if (!is_array($params))
			$params = array(
					$params
			);
			// Objecktname defineiren
		$objName = "obj{$name}";
		// Eine Instance der Wrapperklasse anlegen und in den privaten Array speichern
		$this->objs[$objName] = $this->create_user_obj_array($name, $params);
		// Methodennamen der Klasse in einen privaten Array speichern
		$this->addItems($objName, self::C_METHOD, get_class_methods($name));
		// Propertiesnamen der Klasse in einen privaten Array speichern
		$this->addItems($objName, self::C_PROPERTY, array_keys(get_class_vars($name)));
	}

	/**
	 * Methoden oder Properties der Extended-Klasse in den Index einfügen
	 *
	 * @param String $name
	 *        	Objektname
	 * @param Constante $var
	 *        	Art der items
	 * @param Array<String> $array
	 *        	Liste der items
	 */
	private function addItems($name, $list, $array) {
		$newVars = array_fill_keys($array, "\$this->objs['{$name}']");
		$this->$list = array_merge($newVars, $this->$list);
	}

	/**
	 * Aufruf einer Methode.
	 * Der Aufruf wird an das entsprechende Parentobjekt weitergeleitet
	 *
	 * @param String $name        	
	 * @param Array $params        	
	 * @return Variant
	 */
	public function __call($name, $params) {
		if (array_key_exists($name, $this->methods)) {
			$obj = $this->methods[$name];
			$obj = eval("return $obj;");
			return call_user_func_array(array(
					$obj,
					$name
			), $params);
		}
	}

	/**
	 * Aufruf eines Property.
	 * Der Aufruf wird an das entsprechende Parentobjekt weitergeleitet
	 *
	 * @param String $name        	
	 * @return Variant
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->properties)) {
			$obj = $this->properties[$name];
			return eval("return {$obj}->{$name};");
		}
	}

	/**
	 * Erstellen einer Wrapperlasse um die ParentClass
	 *
	 * @param String $className        	
	 * @return String Name der Wrapperklasse
	 */
	final private static function createWrapperClass($className) {
		// ReflectionObject der Klasse zur weiteren Analyse anlegen
		$ref = new ReflectionClass($className);
		$wrapperName = "{$className}Wrapper";
		// Die Classe zusammenstellen
		$lines[] = "class {$wrapperName} extends {$className}{";
		$lines[] = '
			public function __construct(){
				$pStrings  = $params = func_get_args();
				array_walk($pStrings, create_function(\'&$item, $key\', \'$item = "\$params[{$key}]";\'));
				eval(\'parent::__construct(\' . implode(\',\', $pStrings) . \');\');
			}
		';
		// Die Methoden hinzufügen
		self::createWrapperMethodes($lines, $ref);
		$lines[] = '}';
		// Aus allen Zeilen ein String erstellen
		$classPhp = implode("\n", $lines);
		// Die Klasse ausführen
		eval($classPhp);
		return $wrapperName;
	}

	/**
	 * Erstellen der Wrappermethoden für die Wrapperklasse
	 *
	 * @param
	 *        	$lines
	 * @param
	 *        	$ref
	 */
	final static function createWrapperMethodes(&$lines, ReflectionClass $ref) {
		foreach ($ref->getMethods() as $method) {
			if ($method->isProtected()) {
				$params = array();
				$modifiers = $method->getModifiers() - ReflectionMethod::IS_PROTECTED + ReflectionMethod::IS_PUBLIC;
				$modifiers = implode(' ', Reflection::getModifierNames($modifiers));
				foreach ($method->getParameters() as $param) {
					$params[] = $param->getName();
				}
				array_walk($params, create_function('&$item, $key', '$item = "\${$item}";'));
				$paramString = implode(', ', $params);
				$lines[] = "
				{$modifiers} function {$method->name}({$paramString}){
						return parent::{$method->name}({$paramString});
					}
				";
			}
		}
	}

	/**
	 * erstellt ein Object einer Klasse mit dem einer freien Anzahl Paramtern
	 *
	 * @param String $className        	
	 * @param
	 *        	Variant
	 * @return Object
	 *
	 * @example $obj = create_user_obj('myClass', $paramter1, $paramter2);
	 */
	private static function create_user_obj($className) {
		$params = func_get_args();
		$className = array_shift($params);
		return create_user_obj_array($className, $params);
	}

	/**
	 * erstellt ein Object einer Klasse mit dem Array $params als Argumente
	 *
	 * @param String $className        	
	 * @param Array $params        	
	 * @return Object
	 *
	 * @example $obj = create_user_obj('myClass', array($paramter1, $paramter2));
	 */
	private static function create_user_obj_array($className, $params = array()) {
		$pStrings = $params = array_values($params);
		array_walk($pStrings, create_function('&$item, $key', '$item = "\$params[{$key}]";'));
		$php = 'return new $className(' . implode(',', $pStrings) . ');';
		return eval($php);
	}

}
