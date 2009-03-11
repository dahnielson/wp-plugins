<?php
/*
Plugin Name: Dahnielson: rel-link
Plugin URI: http://en.dahnielson.com/2006/08/rellink-plugin.html
Description: Adds relational resource links to the head for improved accessability and navigation.
Version: 1.3
Author: Anders Dahnielson
Author URI: http://dahnielson.com
*/

/*  Copyright 2006  Anders Dahnielson (email : anders@dahnielson.com)

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

load_plugin_textdomain('dahnielson_rellink', 'wp-content/plugins/dahnielson_rellink');

function dahnielson_get_last_post($in_same_cat = false, $excluded_categories = '')
{
	global $post, $wpdb;

	if( !is_single() || is_attachment() )
		return null;

	$join = '';
	if ( $in_same_cat ) {
		$join = " INNER JOIN $wpdb->post2cat ON $wpdb->posts.ID= $wpdb->post2cat.post_id ";
		$cat_array = get_the_category($post->ID);
		$join .= ' AND (category_id = ' . intval($cat_array[0]->cat_ID);
		for ( $i = 1; $i < (count($cat_array)); $i++ ) {
			$join .= ' OR category_id = ' . intval($cat_array[$i]->cat_ID);
		}
		$join .= ')'; 
	}

	$sql_exclude_cats = '';
	if ( !empty($excluded_categories) ) {
		$blah = explode('and', $excluded_categories);
		foreach ( $blah as $category ) {
			$category = intval($category);
			$sql_exclude_cats .= " AND post_category != $category";
		}
	}

	$result = @$wpdb->get_row("SELECT ID, post_title FROM $wpdb->posts $join WHERE post_status = 'publish' $sqlcat $sql_exclude_cats ORDER BY post_date DESC LIMIT 1");
	
	if ($result->ID != $post->ID)
		return $result;	
}

function dahnielson_get_first_post($in_same_cat = false, $excluded_categories = '') {
	global $post, $wpdb;

	if( !is_single() || is_attachment() )
		return null;
	
	$join = '';
	if ( $in_same_cat ) {
		$join = " INNER JOIN $wpdb->post2cat ON $wpdb->posts.ID= $wpdb->post2cat.post_id ";
		$cat_array = get_the_category($post->ID);
		$join .= ' AND (category_id = ' . intval($cat_array[0]->cat_ID);
		for ( $i = 1; $i < (count($cat_array)); $i++ ) {
			$join .= ' OR category_id = ' . intval($cat_array[$i]->cat_ID);
		}
		$join .= ')'; 
	}

	$sql_exclude_cats = '';
	if ( !empty($excluded_categories) ) {
		$blah = explode('and', $excluded_categories);
		foreach ( $blah as $category ) {
			$category = intval($category);
			$sql_exclude_cats .= " AND post_category != $category";
		}
	}
	
	$result = @$wpdb->get_row("SELECT ID, post_title FROM $wpdb->posts $join WHERE post_status = 'publish' $sqlcat $sql_exclude_cats ORDER BY post_date ASC LIMIT 1");

	if ($result->ID != $post->ID)
		return $result;	
}

function dahnielson_get_max_page($max_page)
{
	global $request, $posts_per_page, $wpdb, $max_num_pages;
	
	if ( isset($max_num_pages) )
	{
		$max_page = $max_num_pages;
	}
	else
	{
//		preg_match('#FROM\s(.*)\sGROUP BY#siU', $request, $matches);
//		$fromwhere = $matches[1];
//		$numposts = $wpdb->get_var("SELECT COUNT(DISTINCT ID) FROM $fromwhere");
//		$max_page = $max_num_pages = ceil($numposts / $posts_per_page);
	}

	return $max_page;
}

function dahnielson_first_posts_href($max_page = 0)
{
	global $paged;
	
	$max_page = dahnielson_get_max_page($max_page);
	if ($paged != $max_page)
		return get_pagenum_link($max_page);
}


function dahnielson_get_previous_posts($max_page = 0)
{
	global $paged, $pagenow;

	if ( !is_single() )
	{
		if ( !$paged )
			$paged = 1;
		$nextpage = intval($paged) + 1;
		if ( !$max_page || $max_page >= $nextpage )
			return get_pagenum_link($nextpage);
	}
}

function dahnielson_previous_posts_href($max_page = 0)
{
	global $paged, $result, $request, $posts_per_page, $wpdb, $max_num_pages;
	if ( !$max_page )
		$max_page = dahnielson_get_max_page($max_page);
	if ( !$paged )
		$paged = 1;
	$nextpage = intval($paged) + 1;
	if ( (! is_single()) && (empty($paged) || $nextpage <= $max_page) )
		return dahnielson_get_previous_posts($max_page);
}

function dahnielson_last_posts_href()
{
	global $paged;
	
	if ($paged != 1)
		return get_pagenum_link(1);
}

function dahnielson_get_next_posts()
{
	global $paged, $pagenow;

	if ( !is_single() )
	{
		$nextpage = intval($paged) - 1;
		if ( $nextpage < 1 )
			$nextpage = 1;
		return get_pagenum_link($nextpage);
	}
}

function dahnielson_next_posts_href()
{
	global $paged;
	if ( (!is_single()) && ($paged > 1) )
		return dahnielson_get_next_posts();
}

function dahnielson_print_rellink($rel, $href, $title)
{
	echo "\t<link rel=\"$rel\" href=\"$href\" title=\"$title\" />\n";
}


function dahnielson_head()
{
	global $posts, $post, $post_meta_cache, $paged;

	$start_href = get_bloginfo('home');
	$start_title = get_bloginfo('name');
	dahnielson_print_rellink('start', $start_href, $start_title);

	if (function_exists('bccl_get_license_deed_url'))
	{
		$copyright_href = bccl_get_license_deed_url();
		$copyright_title = 'Creative Commons';
		dahnielson_print_rellink('copyright', $copyright_href, $copyright_title);
	}

	if (is_archive())
	{
		if (is_year())
		{
			$up_href = get_bloginfo('home');
			$up_title = get_bloginfo('name');
			dahnielson_print_rellink('up', $up_href, $up_title);
		}
		elseif (is_month())
		{
			$up_href = get_year_link(get_the_time('Y'));	
			$up_title = get_the_time('Y');
			dahnielson_print_rellink('up', $up_href, $up_title);
		}
		elseif (is_day())
		{
			$up_href = get_month_link(get_the_time('Y'), get_the_time('m'));
			$up_title = get_the_time('F Y');
			dahnielson_print_rellink('up', $up_href, $up_title);
		}
	}

	if (is_single())
	{
		$author_href = get_author_link(false, $post->post_author);
		$userdata = get_userdata($post->post_author); $author_title = $userdata->display_name;
		dahnielson_print_rellink('author', $author_href, $author_title);

		$bookmark_href = get_permalink($post->ID);
		$bookmark_title = $post->post_title;
		dahnielson_print_rellink('bookmark', $bookmark_href, $bookmark_title);

		$up_href = dirname(get_permalink($post->ID)).'/';
		$up_title = __('Up', 'dahnielson_rellink');
		dahnielson_print_rellink('up', $up_href, $up_title);

		$prev_post = get_previous_post();
		if (isset($prev_post->ID))
		{
			$prev_href = get_permalink($prev_post->ID);
			$prev_title = apply_filters('the_title', $prev_post->post_title, $prev_post);
			dahnielson_print_rellink('previous', $prev_href, $prev_title);
		}

		$next_post = get_next_post();
		if (isset($next_post->ID))
		{
			$next_href = get_permalink($next_post->ID);
			$next_title =  apply_filters('the_title', $next_post->post_title, $next_post);
			dahnielson_print_rellink('next', $next_href, $next_title);
		}

		$first_post = dahnielson_get_first_post();
		if (isset($first_post->ID))
		{
			$first_href = get_permalink($first_post->ID);
			$first_title = __('First post', 'dahnielson_rellink');
			dahnielson_print_rellink('first', $first_href, $first_title);			
		}

		$last_post = dahnielson_get_last_post();
		if (isset($last_post->ID))
		{
			$last_href = get_permalink($last_post->ID);
			$last_title = __('Last post', 'dahnielson_rellink');
			dahnielson_print_rellink('last', $last_href, $last_title);			
		}
	}
	else
	{
		$prev_posts = dahnielson_previous_posts_href();
		if (isset($prev_posts))
		{
			$prev_href = $prev_posts;
			$prev_title = __('Previous posts', 'dahnielson_rellink');
			dahnielson_print_rellink('previous', $prev_href, $prev_title);
		}
		
		$next_posts = dahnielson_next_posts_href();
		if (isset($next_posts))
		{
			$next_href = $next_posts;
			$next_title = __('Next posts', 'dahnielson_rellink');
			dahnielson_print_rellink('next', $next_href, $next_title);
		}

		$first_posts = dahnielson_first_posts_href();
		if (isset($first_posts))
		{
			$first_href = $first_posts;
			$first_title = __('First posts', 'dahnielson_rellink');
			dahnielson_print_rellink('first', $first_href, $first_title);
		}

		$last_posts = dahnielson_last_posts_href();
		if (isset($last_posts))
		{
			$last_href = $last_posts;
			$last_title = __('Last posts', 'dahnielson_rellink');
			dahnielson_print_rellink('last', $last_href, $last_title);
		}		
	}
}

add_action('wp_head', 'dahnielson_head');

?>