<?php
/*
Plugin Name: Dahnielson: IE7
Plugin URI: http://dahnielson.com
Description: Dean Edwards IE7 "patch".
Version: 1.0
Author: Anders Dahnielson.
Author URI: http://dahnielson.com
*/

function dahnielson_ie7()
{
	echo "\t<!--[if lt IE 7]><script src=\"http://static.dahnielson.com/js/ie7/ie7-standard-p.js\" type=\"text/javascript\"></script><![endif]-->\n";
}

add_action('wp_head', 'dahnielson_ie7');

?>