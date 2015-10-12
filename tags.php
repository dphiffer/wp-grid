<?php
/*
Template Name: Tags
*/

get_header();
$id = "post-{$post->post_name}";

?>
<div id="main">
  <article id="<?php echo $id; ?>" <?php post_class(); ?>>
		<h2 class="title"><?php the_title(); ?></h2>
		<div class="content">
		  <?php
		  
		  global $sorted_tags;
		  $sorted_tags = array(
		    0 => array(),
		    1 => array(),
		    2 => array()
		  );
		  $proper_noun_whitelist = array(133, 563, 683, 650);
		  $proper_noun_blacklist = array(323);
		  $tag_headings = array(
		    'Frequently used',
		    'Proper nouns',
		    'Adjectives and common nouns'
		  );
		  $tags = get_tags();
		  array_walk($tags, 'sort_tag');
		  
		  function sort_tag($tag) {
		    global $sorted_tags, $proper_noun_whitelist, $proper_noun_blacklist;
		    if ($tag->count > 4) {
		      $sorted_tags[0][] = $tag;
		    }
		    if (!in_array($tag->term_id, $proper_noun_blacklist) &&
		        strtolower($tag->name) !== $tag->name ||
		        in_array($tag->term_id, $proper_noun_whitelist)) {
		      $sorted_tags[1][] = $tag;
		    } else {
		      $sorted_tags[2][] = $tag;
		    }
		  }
		  
		  foreach ($sorted_tags as $group => $tags) {
		    $title = $tag_headings[$group];
		    echo "<h3>$title</h3>\n<div class=\"tags\">\n";
		    foreach ($sorted_tags[$group] as $i => $tag) {
		      $s = ($tag->count == 1) ? '' : 's';
		      $color = 0;
		      if ($tag->count > 16) {
		        $color = 5;
		      } else if ($tag->count > 8) {
		        $color = 4;
		      } else if ($tag->count > 4) {
		        $color = 3;
		      } else if ($tag->count > 2) {
		        $color = 2;
		      } else if ($tag->count > 1) {
		        $color = 1;
		      }
		      echo "<a href=\"/tags/$tag->slug/\" title=\"$tag->count post$s\" class=\"tag-color-$color\">$tag->name</a> ";
		    }
		    echo "</div>\n";
		  }
		  
		  ?>
		</div>
	</article>
</div>
<?php

get_sidebar();
get_footer();
