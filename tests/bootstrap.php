<?php
spl_autoload_register(function($className) {
	$filepath = 'classes/' . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
	if ( is_readable($filepath) )
		require $filepath;
});