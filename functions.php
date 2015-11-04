<?php

$dir = __DIR__;
require("$dir/lib/breadcrumbs.php");

add_theme_support('post-thumbnails');
add_theme_support('html5', array('caption'));
add_image_size('thumbnail-2x', 956, 590, true);
add_image_size('medium-2x', 1000);
add_image_size('large-2x', 1960);
add_image_size('half-vertical', 488);
add_image_size('half-vertical-2x', 976);

if (!function_exists('dbug')) {
	function dbug() {
		$args = func_get_args();
		$log = array();
		foreach ($args as $arg) {
			if (!is_scalar($arg)) {
				$arg = print_r($arg, true);
				$arg = trim($arg);
			}
			$log[] = $arg;
		}
		$log = implode("\n", $log);
		error_log($log);
	}
}

function grid_widgets_init() {
	register_sidebar(array(
		'name' => __('Main Sidebar', 'grid'),
		'id' => 'sidebar-1',
		'description' => __('Sidebar.', 'grid'),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	));
}
add_action('widgets_init', 'grid_widgets_init');

function grid_description_image($description) {
	// TODO: This can be optimized using wp_options and a save_post hook
  list($latest) = get_posts(array(
    'numberposts' => 1
  ));
  $user_info = get_userdata($latest->post_author);
  $alt = esc_attr($user_info->display_name);
  $image = grid_get_gravatar_url($user_info->user_email, 96);
	$description = "<span class=\"text\"><span class=\"bg\">$description</span></span>";
  return "<img src=\"$image\" alt=\"$alt\" width=\"48\" height=\"48\" />\n$description";
}

function grid_figure_tag($content) {
	$doc = new DOMDocument();
	$doc->loadHTML($content);
	return $doc->saveHTML();
}

//add_filter('the_content', 'grid_figure_tag');

function grid_get_gravatar_url($email, $size) {
	$hash = md5($email);
	$upload_dir = wp_upload_dir();
	$dir = $upload_dir['basedir'] . '/gravatar';
	if (!file_exists("$dir/$hash-$size.jpg")) {
		if (!file_exists($dir)) {
			wp_mkdir_p($dir);
		}
		$image = file_get_contents("http://gravatar.com/avatar/$hash?s=$size&amp;d=mm");
		file_put_contents("$dir/$hash-$size.jpg", $image);
	}
	$base_url = str_replace('http:', '', $upload_dir['baseurl']);
	return "$base_url/gravatar/$hash-$size.jpg";
}

add_filter('grid_description', 'grid_description_image');

function grid_comment($comment) {
  
  $GLOBALS['comment'] = $comment;

  ?>
  <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
    <div class="comment-meta">
      <span class="author"><?php comment_author_link(); ?></span>
      &middot;
      <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>" class="date" title="Posted at <?php comment_time(); ?>"><?php comment_date(); ?></a>
    </div>
    <?php if ( $comment->comment_approved == '0' ) { ?>
      <p class="moderation">Your comment is awaiting moderation.</p>
    <?php } ?>
    <div class="comment-content">
      <?php echo $comment->comment_content; ?>
    </div>
  </li>
<?php
}

function grid_link() {
  global $post;
  $url = grid_post_href($post);
  if (!empty($url)) {
    return $url;
  } else {
    return get_permalink($post->ID);
  }
}

function grid_link_domain() {
  $href = grid_post_href();
  if (!empty($href)) {
    $url = parse_url($href);
    return '<a href="' . $href . '" class="domain">' . $url['host'] . '</a>';
  } else {
    return '';
  }
}

function grid_post_href($post = null) {
	if (empty($post)) {
		global $post;
	}
	$href = get_post_meta($post->ID, 'href', true);
	if (!empty($href)) {
		return $href;
	}
	$content = apply_filters('the_content', $post->post_content);
	if (preg_match('/<a([^>]+)>Link<\/a>/ms', $content, $matches)) {
		if (preg_match('/href="([^"]+)"/', $matches[1], $href)) {
			return $href[1];
		}
	}
	return null;
}

function grid_image_single_content($content) {
  global $post;
  $content = str_replace('500x333', '924x615', $content);
  $images = grid_get_images($post);
  $permalink = get_permalink($post->ID);
  $next = get_adjacent_post(true, null, true);
  if (count($images) == 1 && !empty($next)) {
    $next_permalink = get_permalink($next->ID);
    $content = str_replace($permalink, $next_permalink, $content);
  }
  return $content;
}

function grid_image_archive_content($content) {
  global $post;
  remove_filter('the_content', 'grid_image_archive_content');
  $featured = get_the_post_thumbnail($post->ID, 'medium');
  $featured_id = get_post_thumbnail_id($post->ID);
  $images = grid_get_images($post);
  if (empty($featured) && !empty($images)) {
    $first_image = $images[0];
    $featured = wp_get_attachment_image($first_image->ID, 'medium');
    $featured_id = $first_image->ID;
  }
  if (!empty($post->post_excerpt)) {
    return $post->post_excerpt;
  } else if (!empty($featured)) {
    $permalink = get_permalink($post->ID);
    $srcset = '';
    list($src_full) = wp_get_attachment_image_src($featured_id, 'full');
    list($src) = wp_get_attachment_image_src($featured_id, 'medium');
    list($src2x) = wp_get_attachment_image_src($featured_id, 'medium-2x');
    if ($src_full != $src2x) {
    	$srcset = " srcset=\"$src 1x, $src2x 2x\"";
    }
    $featured = preg_replace('/title="[^"]*"/', '', $featured);
    $featured = preg_replace('#/>$#', "$srcset>", $featured);
    $image_count = count($images);
    if (preg_match('/\[gallery[^\]]+ids="([^"]+)"/', $post->post_content, $matches)) {
    	$ids = explode(',', $matches[1]);
    	$image_count = count($ids);
    }
    $view_all = ($image_count > 1) ? "<span class=\"view-all\">View all $image_count photos</span>" : "";
    return "<figure><a href=\"$permalink#skip-nav\">$featured $view_all</a></figure>";
  } else {
    return $content;
  }
}

function grid_gallery_single_content($content) {
  global $post;
  $title = get_the_title();
  $content = "<div class=\"gallery-intro\">$content</div>\n";
  $content .= "<div class=\"gallery-single\">\n";
  $attachments = get_children(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_parent' => $post->ID,
    'orderby' => 'menu_order',
    'order' => 'ASC'
  ));
  $content .= grid_post_gallery_images($attachments);
  $content .= "</div>\n";
  return $content;
}

function grid_post_gallery_images($attachments) {
  $floated = false;
	$content = '';
  foreach ($attachments as $index => $attachment) {
    list($src, $width, $height) = wp_get_attachment_image_src($attachment->ID, 'large');
    list($src_full) = wp_get_attachment_image_src($attachment->ID, 'full');
    list($src2x) = wp_get_attachment_image_src($attachment->ID, 'large-2x');
    $srcset = '';
		$class = '';
    if ($width < $height) {
    	$next_width = 0;
    	$next_height = 0;
    	if (count($attachments) > $index + 1) {
    		$next = $attachments[$index + 1];
    		list($next_src, $next_width, $next_height) = wp_get_attachment_image_src($next->ID, 'large');
    	}
    	if ($floated || $next_width < $next_height) {
				list($src, $width, $height) = wp_get_attachment_image_src($attachment->ID, 'half-vertical');
				list($src2x) = wp_get_attachment_image_src($attachment->ID, 'half-vertical-2x');
				$class = ' class="float-left"';
				$floated = true;
			}
    } else if (!empty($floated)) {
			$content .= "<br class=\"clear\">\n";
			$floated = false;
    }
    if ($src_full != $src2x) {
    	$srcset = " srcset=\"$src 1x, $src2x 2x\"";
    }
    $content .= "<figure$class><img src=\"$src\" width=\"$width\" height=\"$height\" alt=\"\"$srcset></figure>\n";
  }
  return $content;
}

function grid_post_gallery_get_attachment_by_id($attachments, $id) {
  foreach ($attachments as $attachment) {
    if ($attachment->ID == $id) {
      return $attachment;
    }
  }
  return null;
}

function grid_post_gallery($output, $attr) {
  global $post;
  if (isset($attr['ids'])) {
    $attachments = get_posts(array(
      'post_type' => 'attachment',
      'post_mime_type' => 'image',
      'include' => $attr['ids']
    ));
    $ordered_attachments = array();
    $ids = explode(',', $attr['ids']);
    foreach ($ids as $id) {
      $ordered_attachments[] = grid_post_gallery_get_attachment_by_id($attachments, $id);
    }
  }
  $content = "<div class=\"gallery\">\n";
  $content .= grid_post_gallery_images($ordered_attachments);
  $content .= "</div>\n";
  return $content;
}
remove_all_filters('post_gallery');
add_filter('post_gallery', 'grid_post_gallery', 10, 2);

function grid_single_category_photos_body_class($body_class) {
  global $post;
  if (is_single() && has_category('photos', $post)) {
    $body_class[] = "single-category-photos";
  }
  return $body_class;
}
add_filter('body_class', 'grid_single_category_photos_body_class');

function grid_category_link($url) {
  $category_base = get_option('category_base');
  if (empty($category_base)) {
    $category_base = 'category';
  }
  return str_replace("/$category_base", '', $url);
}
add_filter('category_link', 'grid_category_link');

add_filter('posts_request', 'grid_posts_request', 10, 2);
add_filter('the_posts', 'grid_the_posts');
  
function grid_posts_request($sql, $query) {
  //echo 'grid_posts_request ';
  global $wpdb, $grid_original_sql;
  $grid_original_sql = $sql;
  if (empty($query->ignore_id_clause) &&
      !empty($_GET['before']) && preg_match('/^\d+$/', $_GET['before'], $matches)) {
    $before = date('Y-m-d H:i:s', $matches[0]);
    return str_replace('WHERE', "WHERE $wpdb->posts.post_date < '$before' AND", $sql);
  }
  return $sql;
}
  
function grid_the_posts($posts) {
  global $wpdb, $grid_original_sql, $grid_newer_id, $grid_older_id;
  $grid_newer_id = null;
  $grid_older_id = null;
  if (!empty($posts) && !empty($grid_original_sql)) {
    $posts_per_page = get_option('posts_per_page');
    $first = $posts[0];
    $newer_sql = str_replace('WHERE', "WHERE $wpdb->posts.post_date > '$first->post_date' AND", $grid_original_sql);
    $newer_sql = str_replace(' DESC', '', $newer_sql);
    $newer_page = $wpdb->get_results($newer_sql);
    if (!empty($newer_page)) {
      $last_newer = array_pop($newer_page);
      $grid_newer_id = strtotime($last_newer->post_date) + 1;
    }
    if (count($posts) == $posts_per_page) {
      $index = count($posts) - 1;
      $last = $posts[$index];
      $older_sql = str_replace('WHERE', "WHERE $wpdb->posts.post_date < '$last->post_date' AND", $grid_original_sql);
      $older_page = $wpdb->get_results($older_sql);
      if (!empty($older_page)) {
        $first_older = $older_page[0];
        $grid_older_id = strtotime($first_older->post_date) + 1;
      }
    }
  }
  return $posts;
}
  
function grid_newer_posts_link($text) {
  global $grid_newer_id;
  if (!empty($grid_newer_id)) {
    return "<a href=\"?before=$grid_newer_id\">$text</a>";
  }
  return '';
}

function grid_older_posts_link($text) {
  global $grid_older_id;
  if (!empty($grid_older_id)) {
    return "<a href=\"?before=$grid_older_id\">$text</a>";
  }
  return '';
}

function grid_og_title() {
  if (is_single()) {
    return strip_tags(get_the_title());
  } else {
    return strip_tags(get_bloginfo('title'));
  }
}

function grid_og_url() {
  if (is_single()) {
    return get_permalink();
  } else {
    $parts = parse_url($_SERVER['REQUEST_URI']);
    $url = "http://{$_SERVER['HTTP_HOST']}{$parts['path']}";
    if (!empty($parts['query'])) {
      $url .= "?{$parts['query']}";
    }
    return $url;
  }
}

function grid_og_image() {
  if (is_single()) {
    global $post, $wp_query;
    the_post();
    $meta_image = get_post_meta($post->ID, 'og_image', true);
    if (!empty($meta_image)) {
      $wp_query->rewind_posts();
      return $meta_image;
    } else if (!empty($post)) {
      $featured = get_post_thumbnail_id($post->ID);
      if (!empty($featured)) {
        list($src) = wp_get_attachment_image_src($featured, 'large-2x');
        $wp_query->rewind_posts();
        return $src;
			} else if (has_category('videos')) {
				$content = $post->post_content;
				$iframe_regex = '#<iframe([^>]+)>.*?</iframe>#m';
				$src_regex = '/src\s*=\s*"([^"]+)"/m';
				if (preg_match($iframe_regex, $content, $iframe_matches)) {
					if (preg_match($src_regex, $iframe_matches[1], $src_matches)) {
						if ($youtube_id = grid_youtube_video($src_matches[1])) {
							$poster = grid_youtube_poster_image($youtube_id);
						} else if ($vimeo_id = grid_vimeo_video($src_matches[1])) {
							$poster = grid_vimeo_poster_image($vimeo_id);
						}
						if ($poster == grid_default_poster_image()) {
							return null;
						}
						return $poster;
					}
				}
      } else {
        $images = get_children(array(
          'post_parent' => $post->ID,
          'post_type' => 'attachment',
          'post_mime_type' => 'image',
          'orderby' => 'menu_order',
          'order'=> 'ASC'
        ));
        $wp_query->rewind_posts();
        $images = array_values($images);
        if (!empty($images)) {
          $image = $images[0];
          list($src) = wp_get_attachment_image_src($image->ID, 'large-2x');
          return $src;
        }
      }
    }
  }
  list($latest) = get_posts(array(
    'numberposts' => 1
  ));
  $user_info = get_userdata($latest->post_author);
  return grid_get_gravatar_url($user_info->user_email, 200);
}

function grid_youtube_video($src) {
	$regex = grid_youtube_regex();
	if (preg_match($regex, $src, $matches)) {
		return $matches[1];
	}
	return null;
}

function grid_vimeo_video($post) {
	$regex = grid_vimeo_regex();
	if (preg_match($regex, $src, $matches)) {
		return $matches[1];
	}
	return null;
}

function grid_og_locale() {
  $language = get_bloginfo('language');
  return str_replace('-', '_', $language);
}

function grid_og_site_name() {
  return get_bloginfo('title');
}

function grid_og_description() {
  global $post, $wp_query;
  $description = get_bloginfo('description');
	if (is_single()) {
		$description = grid_description($post->post_content);
		$description = strip_tags($description);
		$description = preg_replace('/\s+/ms', ' ', $description);
		$description = trim($description);
		$wp_query->rewind_posts();
  }
  return $description;
}

function grid_get_images($post) {
  $attachments = get_children(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_parent' => $post->ID,
    'orderby' => 'menu',
    'order' => 'ASC'
  ));
  return array_values($attachments);
}

function grid_more_photos() {
	echo '<div id="more-photos">';
	echo '<h3>More photos</h3>';
  $next = get_adjacent_post(true, null, true);
  grid_adjacent_photo($next, 'Older');
	$prev = get_adjacent_post(true, null, false);
  grid_adjacent_photo($prev, 'Newer');
  echo '<br class="clear"></div>';
}

function grid_adjacent_photo($photo, $label) {
  if ($photo) {
    $photo_permalink = get_permalink($photo->ID);
    $photo_thumbnail = get_the_post_thumbnail($photo->ID, 'thumbnail');
    $thumbnail_id = get_post_thumbnail_id($photo->ID);
    if (empty($photo_thumbnail)) {
      list($first_image) = grid_get_images($photo);
      $photo_thumbnail = wp_get_attachment_image($first_image->ID, 'thumbnail');
      $thumbnail_id = $first_image->ID;
    }
    if (!empty($photo_thumbnail)) {
      $photo_thumbnail = preg_replace('/title="[^"]*"/', '', $photo_thumbnail);
      $srcset = '';
      list($src_full) = wp_get_attachment_image_src($thumbnail_id, 'full');
      list($src) = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
      list($src2x) = wp_get_attachment_image_src($thumbnail_id, 'thumbnail-2x');
      if ($src_full != $src2x) {
      	$srcset = " srcset=\"$src 1x, $src2x 2x\"";
      }
      $photo_thumbnail = preg_replace('#/>$#', "$srcset>", $photo_thumbnail);
      $class = strtolower($label);
      echo "<a href=\"$photo_permalink#skip-nav\" class=\"$class\">
        $label
        $photo_thumbnail
      </a>";
    }
  }
}

function grid_save_boxes() {
  $boxes_json = get_option('grid_header_boxes', '{}');
  $boxes = json_decode($boxes_json);
  $new_boxes_json = stripslashes($_POST['boxes']);
  $new_boxes = json_decode($new_boxes_json);
  $new_boxes = get_object_vars($new_boxes);
  foreach ($new_boxes as $key => $value) {
    $boxes->$key = $value;
  }
  $boxes_json = json_encode($boxes);
  update_option('grid_header_boxes', $boxes_json);
  exit;
}
add_action('wp_ajax_save_boxes', 'grid_save_boxes');
add_action('wp_ajax_nopriv_save_boxes', 'grid_save_boxes');

function grid_shift_boxes() {
  $boxes_json = get_option('grid_header_boxes', '{}');
  $boxes = json_decode($boxes_json);
  $new_boxes = array();
  header('Content-Type: text/plain');
  print_r($boxes);
  foreach ($boxes as $box_id => $box_value) {
    if (preg_match('/^box(\d+)-(\d+)$/', $box_id, $matches)) {
      $box_col = intval($matches[1]);
      $box_row = intval($matches[2]);
      $new_box_col = $box_col + 1;
      if ($new_box_col < 70) {
        $new_box_id = "box{$new_box_col}-{$box_row}";
        $new_boxes[$new_box_id] = $box_value;
      }
    }
  }
  $new_box_json = json_encode($new_boxes);
  echo $new_box_json;
  //update_option('grid_header_boxes', $new_box_json);
  exit;
}

function grid_check_whether_to_shift_boxes() {
  if (!empty($_GET['shift_boxes'])) {
    grid_shift_boxes();
  }
}
add_action('init', 'grid_check_whether_to_shift_boxes');

function grid_add_tag_count_title($links) {
  $filtered = array();
  foreach ($links as $link) {
    if (preg_match('/\/([^\/]+)\/"/', $link, $slug_match)) {
      $term = get_term_by('slug', $slug_match[1], 'post_tag');
      $s = ($term->count == 1) ? '' : 's';
      $filtered[] = preg_replace('/<a([^>]+)>/', '<a$1 title="' . $term->count . ' post' . $s . '">', $link);
    } else {
      $filtered[] = $link;
    }
  }
  return $filtered;
}

add_filter('term_links-post_tag', 'grid_add_tag_count_title');

function grid_exclude_private($query) {
  global $post;
  if (!is_single() && !is_admin()) {
    $query->set('tag__not_in', array('880'));
  } else {
    //setcookie('show-private-post-' . $post->postname, 1, time() + 60 * 60 * 24 * 360, '/');
  }
}
add_action('pre_get_posts', 'grid_exclude_private');

function grid_strip_rel_attr($link) {
  return preg_replace('/\s+rel="[^"]*"/', '', $link);
}
add_filter('the_category', 'grid_strip_rel_attr');

function grid_embed_placeholder($content) {
	if (is_feed()) {
		return $content;
	}
	$regex = '#<iframe([^>]+)>.*?</iframe>#';
	$content = preg_replace_callback($regex, function($matches) {
		$embed = array();
		$atts = $matches[1];
		preg_match_all('/(\w+)\s*=\s*"([^"]+)"/', $atts, $attr_matches);
		foreach ($attr_matches[1] as $index => $key) {
			$value = $attr_matches[2][$index];
			if ($key == 'src') {
				$embed['src'] = $value;
				$url = parse_url($value);
				$embed['host'] = $url['host'];
			} else if ($key == 'width' || $key == 'height') {
				$embed[$key] = $value;
			}
		}
		$youtube_hosts = grid_youtube_hosts();
		$vimeo_hosts = grid_vimeo_hosts();
		if (!empty($embed['host']) && in_array($embed['host'], $youtube_hosts)) {
			return grid_youtube_placeholder($embed, $matches[0]);
		} else if (!empty($embed['host']) && in_array($embed['host'], $vimeo_hosts)) {
			return grid_vimeo_placeholder($embed, $matches[0]);
		} else {
			return grid_generic_placeholder($embed, $matches[0]);
		}
	}, $content);
	return $content;
}
add_filter('the_content', 'grid_embed_placeholder');

function grid_youtube_placeholder($embed, $default) {
	extract($embed);
	$src = esc_attr(add_query_arg('autoplay', '1', $src));
	$regex = grid_youtube_regex();
	if (preg_match($regex, $embed['src'], $matches)) {
		$id = $matches[1];
		$poster_image = grid_youtube_poster_image($id);
		return "
			<figure class=\"embed-placeholder\" data-embed-src=\"$src\">
				<img src=\"$poster_image\" width=\"$width\" height=\"$height\" alt=\"Video\">
			</figure>
		";
	} else {
		return $default;
	}
}

function grid_youtube_poster_image($id) {
	global $post;
	$image_url = get_post_meta($post->ID, 'youtube_poster_image', true);
	if (empty($image_url)) {
		$image_url = "http://img.youtube.com/vi/$id/maxresdefault.jpg";
		$image = grid_download($image_url);
		if (!empty($image)) {
			update_post_meta($post->ID, 'youtube_poster_image', $image_url);
		} else {
			$image_url = "http://img.youtube.com/vi/$id/mqdefault.jpg";
			$image = grid_download($image_url);
			if (empty($image)) {
				$image_url = 'default';
			}
			update_post_meta($post->ID, 'youtube_poster_image', $image_url);
		}
	}
	if ($image_url == 'default') {
		$image_url = grid_default_poster_image();
	}
	return $image_url;
}

function grid_vimeo_hosts() {
	return array(
		'player.vimeo.com'
	);
}

function grid_vimeo_regex() {
	return '#/video/(\w+)#';
}

function grid_youtube_regex() {
	return '#/embed/(\w+)#';
}

function grid_youtube_hosts() {
	return array(
		'www.youtube.com',
		'www.youtube-nocookie.com'
	);
}

function grid_vimeo_placeholder($embed, $default) {
	extract($embed);
	$src = esc_attr(add_query_arg('autoplay', '1', $src));
	$regex = grid_vimeo_regex();
	if (preg_match($regex, $embed['src'], $matches)) {
		$id = $matches[1];
		$poster_image = grid_vimeo_poster_image($id);
		return "
			<figure class=\"embed-placeholder\" data-embed-src=\"$src\">
				<img src=\"$poster_image\" width=\"$width\" height=\"$height\">
			</figure>
		";
	} else {
		return $default;
	}
}

function grid_vimeo_poster_image($vimeo_id) {
	global $post;
	$meta_key = "vimeo_poster_image_$vimeo_id";
	$poster_image = get_post_meta($post->ID, $meta_key, true);
	if (empty($poster_image)) {
		$vimeo_info = grid_vimeo_info($vimeo_id);
		if (empty($vimeo_info)) {
			return grid_default_poster_image();
		}
		$poster_image = $vimeo_info[0]['thumbnail_large'];
		update_post_meta($post->ID, $meta_key, $poster_image);
	}
	return $poster_image;
}

function grid_vimeo_info($vimeo_id) {
	$response = grid_download("http://vimeo.com/api/v2/video/$vimeo_id.php");
	if (!empty($response)) {
		return unserialize($response);
	}
	return null;
}

function grid_generic_placeholder($embed, $default) {
	extract($embed);
	$src = esc_attr(add_query_arg('autoplay', '1', $src));
	$poster_image = grid_default_poster_image();
	return "
		<figure class=\"embed-placeholder\" data-embed-src=\"$src\">
			<img src=\"$poster_image\" width=\"$width\" height=\"$height\">
		</figure>
	";
}

function grid_default_poster_image() {
	global $post;
	$featured = get_post_thumbnail_id($post->ID);
	if (!empty($featured)) {
		list($src) = wp_get_attachment_image_src($featured, 'full');
		return $src;
	}
	return get_template_directory_uri() . '/img/embed-poster.png';
}

function grid_img_caption_shortcode($output, $attr, $content) {
	// Based on:
	// http://justintadlock.com/archives/2011/07/01/captions-in-wordpress
	
	if (is_feed()) {
		return $output;
	}

	// Set up the default arguments.
	$defaults = array(
		'id' => '',
		'align' => 'alignnone',
		'width' => '',
		'caption' => ''
	);
	$attr = shortcode_atts($defaults, $attr);

	if ($attr['width'] < 1 || empty($attr['caption'])) {
		return $content;
	}

	// Set up the attributes
	$attributes = ( !empty( $attr['id'] ) ? ' id="' . esc_attr( $attr['id'] ) . '"' : '' );
	$attributes .= ' class="wp-caption ' . esc_attr( $attr['align'] ) . '"';

	$output = "<figure$attributes>";
	$output .= do_shortcode($content);
	$output .= "<figcaption class=\"wp-caption-text\">{$attr['caption']}</figcaption>";
	$output .= '</figure>';

	return $output;
}
add_filter('img_caption_shortcode', 'grid_img_caption_shortcode', 10, 3);

function grid_download($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$response = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($status == 200) {
		return $response;
	} else {
		return null;
	}
}

function grid_thisamericanlife_widget($atts) {
	if (empty($atts['episode'])) {
		return '';
	}
	$id = intval($atts['episode']);
	wp_enqueue_script('thisamericanlife-js', get_template_directory_uri() . '/js/thisamericanlife.js', array(), '2015-09-23', true);
	return "
		<div id=\"this-american-life-$id\" class=\"this-american-life\"></div>
	";
}
add_shortcode('thisamericanlife', 'grid_thisamericanlife_widget');

function grid_dashboard_setup() {
	wp_add_dashboard_widget(
		'grid_dashboard_publish_widget',
		'Publish',
		'grid_dashboard_publish_widget'
	);
	wp_add_dashboard_widget(
		'grid_dashboard_filter_widget',
		'Filter',
		'grid_dashboard_filter_widget'
	);
}
add_action( 'wp_dashboard_setup', 'grid_dashboard_setup' );

function grid_dashboard_publish_widget() {
	$categories = '';
	$category_list = get_categories(array(
		'orderby' => 'count',
		'order'   => 'DESC'
	));
	foreach ($category_list as $category) {
		$categories .= "<option value=\"$category->term_id\">$category->name</option>\n";
	}
	echo "
		<form action=\"/wp-admin/admin-ajax.php\" method=\"post\" id=\"grid-publish\">
			<input name=\"action\" value=\"grid_save_draft\" type=\"hidden\">
			<input name=\"post_title\" type=\"text\" class=\"regular-text\" placeholder=\"Title\">
			<textarea name=\"post_content\" placeholder=\"Content\" cols=\"60\" rows=\"4\"></textarea>
			<div class=\"meta\">
				<select name=\"post_category\">$categories</select>
				<input name=\"post_tags\" type=\"text\" class=\"regular-text\" placeholder=\"Tags (comma-separated)\">
			</div>
			<input type=\"submit\" value=\"Save Draft\" class=\"button button-primary\">
		</form>
	";
}

function grid_dashboard_filter_widget() {
	$feeds = array(
		'grid_filter' => array(
			'link' => 'http://www.themorningnews.org/headlines',
			'url' => 'http://feeds.feedburner.com/TheMorningNews/headlines',
			'title' => 'TMN',
			'items' => 1,
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0
		)
	);
	$widget_options = get_option( 'widget_options' );
	
	wp_dashboard_cached_rss_widget( 'grid_filter', 'wp_dashboard_primary_output', $feeds );
}

function grid_widget_scripts() {
	wp_register_style( 'grid_admin_css', get_template_directory_uri() . '/admin-style.css', false );
	wp_enqueue_style( 'grid_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'grid_widget_scripts' );

function grid_save_draft() {
	$id = wp_insert_post( array(
		'post_title'    => $_POST['post_title'],
		'post_content'  => $_POST['post_content'],
		'post_status'   => 'draft',
		'post_author'   => get_current_user_id(),
		'post_category' => array( $_POST['post_category'] ),
		'tags_input'    => $_POST['post_tags']
	) );
	wp_safe_redirect( "/wp-admin/post.php?post=$id&action=edit" );
	exit;
}
add_action( 'wp_ajax_grid_save_draft', 'grid_save_draft' );

function grid_description($description) {
	if (function_exists('Markdown')) {
		$description = Markdown($description);
		$description = preg_replace('/<p><a href=".+?">Link<.+?<\/p>/ms', '', $description);
	}
	return $description;
}
add_filter( 'twitter_card_description', 'grid_description' );

function grid_og_meta_tags() {
	$og_tags = array(
		'title',
		'url',
		'image',
		'locale',
		'site_name',
		'description'
	);
	foreach ($og_tags as $tag) {
		$function = "grid_og_$tag";
		$value = esc_attr($function());
		echo "<meta property=\"og:$tag\" content=\"$value\">\n";
	}
}
add_action('wp_head', 'grid_og_meta_tags');

function grid_publish_pinboard() {
	if (empty($_REQUEST['id'])) {
		die("Error: no 'id' argument.");
	}
	$post = get_post($_REQUEST['id']);
	$url = grid_post_href($post);
	if (empty($url)) {
		$url = get_permalink($post->ID);
	}
	$title = get_the_title($post->ID);
	$title = strip_tags($title);
	$title = html_entity_decode($title, ENT_HTML5, 'UTF-8');
	$content = apply_filters('the_content', $post->post_content);
	$content = grid_description($content);
	$content = strip_tags($content);
	$content = preg_replace('/\s+/ms', ' ', $content);
	$content = html_entity_decode($content, ENT_HTML5, 'UTF-8');
	$pinboard['url'] = urlencode($url);
	$pinboard['description'] = urlencode($title);
	$pinboard['extended'] = urlencode($content);
	$tags = get_the_tags($post->ID);
	$tag_list = array();
	foreach ($tags as $tag) {
		$tag_list[] = $tag->slug;
	}
	$pinboard['tags'] = urlencode(implode(' ', $tag_list));
	$pinboard['auth_token'] = 'dphiffer:C4F8D2A78B27C1AEF4CB';
	$pinboard['format'] = 'json';
	$url = 'https://api.pinboard.in/v1/posts/add?' . build_query($pinboard);
	$response_details = wp_remote_get($url);
	$response_json = wp_remote_retrieve_body($response_details);
	$response = json_decode($response_json);
	print_r($response);
	exit;
}
add_action('wp_ajax_grid_publish_pinboard', 'grid_publish_pinboard');

remove_action('wp_head', 'wp_generator');
