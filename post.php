<?php

if (is_single() && has_category('photos', $post)) {
	$id = 'skip-nav';
} else {
	$id = "post-{$post->post_name}";
}

?>
<article id="<?php echo $id; ?>" <?php post_class(); ?>>
  <h2 class="title"><a href="<?php echo grid_link(); ?>"><?php the_title(); ?></a><?php echo grid_link_domain(); ?></h2>
  <div class="content">
    <?php
    
    if (!is_single() && has_category('photos')) {
      add_filter('the_content', 'grid_image_archive_content');
    }
    the_content();
    
    ?>
		<div class="meta">
			<?php

			if (!is_page()) {
				$separator = '&nbsp;&nbsp;&middot;&nbsp;&nbsp;';
				$permalink = get_permalink();
				echo "<a href=\"$permalink\" class=\"permalink\" title=\"Permanent link\">&equiv;</a>&nbsp;&nbsp;";
				the_category(', ');
				echo $separator;
				/*if (!is_single() && $post->comment_status == 'open') {
					comments_popup_link('Comment', '1 comment', '% comments');
					echo $separator;
				}*/
				$time = get_the_time('g:i a');
				$date = get_the_time('F j, Y');
				echo "<a href=\"$permalink\" title=\"Posted at $time\">$date</a>";
				echo "<div class=\"tags-bottom\">$separator";
				echo '<ul class="tags">';
		    the_tags('<li>', "</li>\n<li>", "</li>\n");
			  echo "</ul></div>\n";
				edit_post_link('Edit', $separator);
			} else {
				edit_post_link('Edit');
			}

			?>
			<div class="clear"></div>
    </div>
  </div>
  <ul class="tags tags-top">
    <?php the_tags('<li>', "</li>\n<li>", "</li>\n"); ?>
  </ul>
	<div class="clear"></div>
  <?php
  
  if (is_single() && has_category('photos')) {
  	grid_more_photos();
  }
  
  if (is_single()) {
    //comments_template();
  }
  
  ?>
</article>
