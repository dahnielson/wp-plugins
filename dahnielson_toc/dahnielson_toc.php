<?php
/*
Plugin Name: Dahnielson: Table of Contents
Plugin URI: http://en.dahnielson.com/2006/08/toc-plugin.html 
Description: A forked version of <a href="http://scott.yang.id.au/">Scott Yangs</a> great <a href="http://fucoder.com/code/toc-generator/">ToC generator</a>. Scans through HTML headings and creates a "Table of Contents" list for your posts/pages. Access it via &lt;!--TOC--&gt; inside your post, there are some additional arguments available as well.
Version: 1.4
Author: Anders Dahnielson.
Author URI: http://dahnielson.com
*/

/*  Copyright 2006  Scott Yang

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

load_plugin_textdomain('dahnielson_toc', 'wp-content/plugins/dahnielson_toc');

class dahnielson_toc
{
	
	function add_toc($level, $tocid, $text)
	{
		$this->toc[] = array($this->pagenum, $level, $tocid, $text);
		$this->minlevel = min($this->minlevel, $level);
	}
	
	function get_tocid($text)
	{
		$text = sanitize_title_with_dashes($text);
		$text = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $text);
		$tocid = $text;
		$count = 0;
		while (isset($this->tocmap[$tocid]))
			$tocid = $text.strval(++ $count);
		$this->tocmap[$tocid] = true;
		return "toc-$tocid";
	}
	
	function get_toc($params = array())
	{
		global $wp_rewrite;
		if (isset($this->toccache))
			return $this->toccache;
		if (!isset($this->toc))
			return '';

		if (!array_key_exists('h', $params))
			$params['h'] = 3;
		
		if (isset($params['title']))
			$html = "<h".$params['h']." id=\"toc\" class=\"toc\">".$params['title']."</h".$params['h'].">";
		else
			$html = "<a id=\"toc\"></a>";
		
		$permalink = get_permalink($this->postid);
		for ($i = 0; $i < sizeof($this->toc); $i ++)
		{
			list($pagenum, $level, $tocid, $text) = $this->toc[$i];
			$link = $permalink;
			if ($pagenum > 1) {
				if ($wp_rewrite->using_permalinks())
					$link = trailingslashit($link)."$pagenum/";
				else
					$link .= "&page=$pagenum";
			}
			$link = "<a href=\"$link#$tocid\">$text</a>";
			if ($i == 0)
			{
				$level = min($level, $this->minlevel);
				$stack = array($level);
				$html .= "<ol class=\"toc\"><li>$link";
			}
			else
			{
				$prev = $stack[sizeof($stack)-1];
				if ($level == $prev)
				{
					$html .= "</li><li>$link";
				}
				elseif ($level < $prev)
				{
					while (sizeof($stack) > 1)
					{
						array_pop($stack);
						$html .= "</li></ol>";
						$prev = $stack[sizeof($stack)-1];
						if ($level >= $prev)
							break;
					}
					$html .= "</li><li>$link";
				}
				else
				{
					$stack[] = $level;
					$html .= "<ol><li>$link";
				}
			}
		}
		
		while (sizeof($stack) > 0)
		{
			array_pop($stack);
			$html .= "</li></ol>";
		}
        
		$this->toccache = $html;
		return $this->toccache;
	}

	function replace_heading($match)
	{
		if ($match[0] == '<!--nextpage-->')
		{
			error_log('next');
			$this->pagenum ++;
			return $match[0];
		}
		$tocid = $this->get_tocid($match[3]);
		$this->add_toc(intval($match[1]), $tocid, $match[3]);
		return "<h$match[1] id=\"$tocid\"$match[2]>$match[3]</h$match[1]>";
	}

	function replace_nextpage($match)
	{
		global $wp_rewrite;
		
		$this->pagenum ++;

		foreach ($this->toc as $toc)
		{
			if ($toc[0] == $this->pagenum)
			{
				list($pagenum, $level, $tocid, $text) = $toc;
				$link = get_permalink($this->postid);
				if ($wp_rewrite->using_permalinks())
					$link = trailingslashit($link)."$pagenum/";
				else
					$link .= "&page=$pagenum";
				return "<div class=\"toc-page-link\">".sprintf( __('Next page: <a href="%1$s">&ldquo;%2$s&rdquo;</a>', 'dahnielson_toc'), $link, $text )."</div><!--nextpage-->";
			}
		}
		
		return "<!--nextpage-->";
	}

	function find_toc($match)
	{
		global $post, $wp_rewrite;
		
		if ($match[0] == '<!--nextpage-->')
		{
			error_log('next');
			$this->pagenum ++;
			return $match[0];
		}

		parse_str($match[1], $params);
		$title = $params['title'];

		$link = get_permalink($post->ID);
		if ($wp_rewrite->using_permalinks())
			$link = trailingslashit($link).$this->pagenum."/";
		else
			$link .= "&page=".$this->pagenum;
			
		echo "\t<link rel=\"contents\" href=\"$link#toc\" title=\"$title\" />\n";
		return $match[0];
	}
    
	// "the_content" was originally designed to be a filter for "the_content" 
	// so it takes original content and replace with content with TOC added.
	function the_content($content)
	{
		$this->toc = array();
		$this->tocmap = array();
		$this->toccache = null;
		$this->minlevel = 6;

		// Get params
		$regex = '/<!--TOC\??(([\&]?[\w]+\=[\w\d\s\pL]+)*)-->/u';
		preg_match($regex, $content, $matches);
		parse_str($matches[1], $params);

		// Replace heading
		$this->pagenum = 1;
		$regex = '#<h([1-6])(.*?)>(.*?)</h\1>|<!--nextpage-->#';
		$content = preg_replace_callback($regex, array(&$this, 'replace_heading'), $content);

		// Replace nextpage
		if (!isset($params['nextpage']) || $params['nextpage'] == "yes")
		{
			$this->pagenum = 1;
			$regex = '/<!--nextpage-->/';
			$content = preg_replace_callback($regex, array(&$this, 'replace_nextpage'), $content);
		}
		
		return preg_replace('|(<p>)?<!--TOC\??(([\&]?[\w]+\=[\w\d\s\pL]+)*)-->(</p>)?|u', $this->get_toc($params), $content);
	}
	
	function the_posts($posts)
	{
		for ($i = 0; $i < sizeof($posts); $i ++)
		{
			$post = &$posts[$i];
			$this->postid = $post->ID;
			$post->post_content = $this->the_content($post->post_content);
			$post->post_toc = $this->get_toc();
		}
		
		return $posts;
	}

	function wp_head()
	{
		global $post, $wpdb;
		
		if (is_single())
		{
			$this->pagenum = 1;
			$regex = '/<!--TOC\??(([\&]?[\w]+\=[\w\d\s\pL]+)*)-->|<!--nextpage-->/u';
			$result = @$wpdb->get_row("SELECT post_content FROM $wpdb->posts WHERE ID = $post->ID");
			preg_replace_callback($regex, array(&$this, 'find_toc'), $result->post_content);
		}
	}	
};

$dahnielson_toc_object = new dahnielson_toc;
add_action('wp_head', array($dahnielson_toc_object, 'wp_head'));
add_filter('the_posts', array($dahnielson_toc_object, 'the_posts'));

?>