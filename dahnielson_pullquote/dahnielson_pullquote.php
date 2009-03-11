<?php
/*
Plugin Name: Dahnielson: Pullquote
Plugin URI: http://dahnielson.com
Description: Pullquote plugin using the 456bereastreet JavaScript soution.
Version: 1.0
Author: Anders Dahnielson.
Author URI: http://dahnielson.com
*/

function dahnielson_pullquote()
{
	if (is_single() || is_page())
	{
		echo "\t<script src=\"http://static.dahnielson.com/js/custom/pullquote.js\" type=\"text/javascript\"></script>\n";
	}
}

add_action('wp_head', 'dahnielson_pullquote');

?>