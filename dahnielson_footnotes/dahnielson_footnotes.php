<?php
/*
Plugin Name: Dahnielson: Footnotes
Plugin URI: http://en.dahnielson.com/2007/03/footnotes.html
Description:  Adds footnotes to posts
Version: 1.4
Author: Anders Dahnielson.
Author URI: http://dahnielson.com
*/

/*  Copyright 2007  Anders Dahnielson

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

class dahnielson_footnotes
{

	function the_notes()
	{
		if ($this->notes)
		{
			$note_html_open = '<div id="footnotes"><hr />';
			$note_html_content = implode(' ', $this->notes);
			$note_html_close = '</div>';
				
			unset($this->notes);

			return $note_html_open . $note_html_content . $note_html_close;
		}
	}
	
	function replace_footnote($match)
	{
		if ($match[0] == '<!--nextpage-->')
		{
			$content = $this->the_notes() . $match[0];
		}
		else
		{
			$content = '<sup id="citation-'.$this->postid.'-'.$this->notenum.'" class="footnote"><a href="#footnote-'.$this->postid.'-'.$this->notenum.'">'.$this->notenum.'</a></sup>';
			$this->notes[] = '<p id="footnote-'.$this->postid.'-'.$this->notenum.'"><sup><a href="#citation-'.$this->postid.'-'.$this->notenum.'">'.$this->notenum.'</a></sup> '.$match[1].'</p>';
			$this->notenum ++;
		}

		return $content;
	}
	
	function the_content($content)
	{
		$this->notenum = 1;
		$this->notes = array();
		
		$regex = '#<footnote>(.*?)</footnote>|<!--nextpage-->#';
		$content = preg_replace_callback($regex, array(&$this, 'replace_footnote'), $content);
		return $content . $this->the_notes();
	}
	
	function the_posts($posts)
	{
		for ($i = 0; $i < sizeof($posts); $i ++)
		{
			$post = &$posts[$i];
			$this->postid = $post->ID;
			$post->post_content = $this->the_content($post->post_content);
		}
		
		return $posts;
	}	
};

$dahnielson_footnotes_object = new dahnielson_footnotes;
add_filter('the_posts', array($dahnielson_footnotes_object, 'the_posts'));

?>