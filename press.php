<?php
/*
Template Name: Press
*/

$background = get_post_meta($post->ID, 'background', true);
if (empty($background)) {
  $background = '#fff';
}

the_post();

global $post;
$title = get_the_title();
$title = esc_attr($title);
$url = get_post_meta($post->ID, 'href', true);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php the_title(); ?></title>
    <style>
    
    body {
      margin: 0;
      text-align: center;
    }
    
    img {
      display: block;
      outline: 0;
    }
    
    #archive {
      background: #333;
      border-bottom: 1px solid #666;
    }
    
    #archive .inner {
      width: 1024px;
      text-align: left;
      margin: 0 auto;
      color: #ccc;
      font: 11px/29px verdana, sans-serif;
    }
    
    #archive a {
      color: #fff;
    }
    
    #screenshot {
      background: <?php echo $background; ?>;
      padding-bottom: 50px;
    }
    
    #screenshot .inner {
      margin: 0 auto;
      width: 1024px;
    }
    
    </style>
  </head>
  <body>
    <div id="archive">
      <div class="inner">
        Image archive of <a href="<?php echo $url; ?>"><?php echo $url; ?></a>
      </div>
    </div>
    <div id="screenshot">
      <div class="inner">
        <?php
        
        $images = get_children(array(
          'post_parent' => $post->ID,
          'post_type' => 'attachment',
          'post_mime_type' => 'image',
          'orderby' => 'menu_order',
          'order'=> 'ASC'
        ));
        
        foreach ($images as $image) {
          list($src, $width, $height) = wp_get_attachment_image_src($image->ID, 'full');
          if (!empty($url)) {
          echo <<<END
            <a href="$url">
              <img src="$src" width="$width" height="$height" alt="$title" />
            </a>
END;
          } else {
            echo <<<END
              <img src="$src" width="$width" height="$height" alt="$title" />
END;
          }
        }
        
        ?>
      </div>
    </div>
  </body>
</html>
