<?php global $wp_query, $post; ?>
<div id="pagination">
  <?php 
  
  $older_posts = grid_older_posts_link('Older posts');
  $newer_posts = grid_newer_posts_link('Newer posts');
  
  echo $older_posts;
  if (!empty($older_posts) && !empty($newer_posts)) {
    echo '&nbsp;&nbsp;&middot;&nbsp;&nbsp;';
  }
  echo $newer_posts;
  
  ?>
  <div class="posts">
    <?php
    
    $query = array_merge($wp_query->query_vars, array(
      'posts_per_page' => -1,
      'nopaging' => true
    ));
    $all_posts = new WP_Query();
    $all_posts->ignore_id_clause = true;
    $all_posts->query($query);
    
    $num = 0;
    $page = 1;
    $big = 999999999; // need an unlikely integer
    $base = str_replace($big, '%#%', get_pagenum_link($big));
    $format = '?paged=%#%';
    $page_found = false;
    $total = $wp_query->max_num_pages;
    $per_page = $wp_query->query_vars['posts_per_page'];
    
    if (count($all_posts->posts) > $per_page) {
    
			$page_class = 'page';
      while ($all_posts->have_posts()) {
        $all_posts->the_post();
        if ($num % $per_page == 0) {
          $curr_post = $all_posts->posts[$num];
          $curr_before = strtotime($curr_post->post_date) + 1;
          $link = "?before=$curr_before";
          if (!empty($_GET['before'])) {
            $before = $_GET['before'];
          }
          $current = '';
          if (empty($_GET['before']) && empty($page_found) ||
              empty($page_found) && !empty($before) && strtotime($curr_post->post_date) < $before) {
            $page_found = true;
            $page_class .= ' current';
          }
          echo "<span class=\"$page_class\">";
        }
        $title = get_the_title();
        $categories = get_the_category();
        $category = array_shift($categories);
				$title = esc_attr("$category->name: $title");
        
        echo '<a href="' . $link . '#post-' . $post->post_name . '" title="' . $title . '" class="permalink">&equiv;</a>';
        if (($num + 1) % $per_page == 0 || $num == $all_posts->post_count - 1) {
          //echo '<br class="clear" /></a>';
					echo '</span>';
					$page_class = 'page';
          $page++;
        }
        $num++;
      }
    }
    
    ?>
    <br class="clear" />
    <span id="pagination-hover"></span>
  </div>
</div>
