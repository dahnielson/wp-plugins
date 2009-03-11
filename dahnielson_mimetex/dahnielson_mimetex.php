<?php
/*
Plugin Name: Dahnielson: mimeTeX
Plugin URI: http://en.dahnielson.com/2006/09/mimetex-plugin.html
Description: Use &lt;tex&gt;&lt;/text&gt; tags to embed LaTeX math in posts, see the <a href="http://www.forkosh.com/mimetex.html">mimeTeX manual</a> for details.
Version: 1.2
Author: Anders Dahnielson
Author URI: http://dahnielson.com
*/

/*  Copyright 2006-2007  Anders Dahnielson (email : anders@dahnielson.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class dahnielson_mimetex
{
	function parse($content)
	{
		$regex = '#<tex>(.*?)</tex>#si';
		return preg_replace_callback($regex, array(&$this, 'replace_tex'), $content);
	}

	function replace_tex($match)
	{
		$formula_text = $match[1];
		$formula_hash = md5($formula_text);
		$formula_filename = 'tex_'.$formula_hash.'.gif';

		$cache_path = ABSPATH . '/wp-content/cache/';
		$cache_formula_path = $cache_path . $formula_filename;
		$cache_url = get_bloginfo('wpurl') . '/wp-content/cache/';
		$cache_formula_url = $cache_url . $formula_filename;
		
 		if (!is_file($cache_formula_path))
 		{
			$mimetex_host = curl_init('http://www.forkosh.dreamhost.com/cgi-bin/mimetexpublic.cgi?formdata='.urlencode($formula_text));
			$cache_file = fopen($cache_formula_path, 'w');
			curl_setopt($mimetex_host, CURLOPT_FILE, $cache_file);
			curl_setopt($mimetex_host, CURLOPT_HEADER, 0);
			curl_exec($mimetex_host);
			curl_close($mimetex_host);
			fclose($cache_file);
 		}
		
		return "<img src=\"$cache_formula_url\" alt=\"$formula_text\" />";
	}
}

$dahnielson_mimetex_object = new dahnielson_mimetex;
add_filter('the_title', array($dahnielson_mimetex_object, 'parse'), 1);
add_filter('the_content', array($dahnielson_mimetex_object, 'parse'), 1);
add_filter('the_excerpt', array($dahnielson_mimetex_object, 'parse'), 1);

?>
