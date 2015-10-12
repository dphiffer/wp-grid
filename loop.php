<div id="main">
	<?php
  
  do_action('grid_pre_loop');
  
  if (have_posts()) {
    while (have_posts()) {
      the_post();
      get_template_part('post');
    }
  } else {
    echo '<div class="empty">';
    echo '<h2>Nothing found!</h2>';
    echo '<p>The content has gone missing somehow, which is slightly different than a normal 404 error. Basically there are no posts where we thought there would be some.</p>';
    echo '</div>';
  }
  
  do_action('grid_post_loop');
	if (is_home() || is_archive()) {
	  get_template_part('pagination');
	}
  
  ?>
</div>
