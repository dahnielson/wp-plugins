<?php
/*
Plugin Name: Dahnielson: Caption
Plugin URI: http://dahnielson.com
Description: Caption plugin turning the title attribute into a caption.
Version: 1.0
Author: Anders Dahnielson.
Author URI: http://dahnielson.com
*/

function dahnielson_caption()
{
//	if (is_single() || is_page())
//	{
		echo "\t<script src=\"http://static.dahnielson.com/js/mootools/mootools.dom.83.js\" type=\"text/javascript\"></script>\n\t<script src=\"http://static.dahnielson.com/js/custom/caption.js\" type=\"text/javascript\"></script>\n";
//	}
}

add_action('wp_head', 'dahnielson_caption');

?>