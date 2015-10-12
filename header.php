<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<title><?php
		
		wp_title( '&middot;', true, 'right' );
		bloginfo( 'name' );
		
		?></title>
		<link rel="stylesheet" href="<?php
		
		$last_modified = filemtime(get_stylesheet_directory() . '/style.css');
		echo get_stylesheet_uri() . "?$last_modified";
		
		?>">
		<link rel="shortcut icon" type="image/x-icon" href="<?php
		
		echo get_stylesheet_directory_uri() . '/favicon-e22974.ico';
		
		?>">
		<?php
		
		$feed_type  = feed_content_type();
		$feed_title = esc_attr(get_bloginfo('name'));
		$feed_href  = esc_url(get_feed_link());
		echo "<link rel=\"alternate\" type=\"$feed_type\" title=\"$feed_title\" href=\"$feed_href\">\n";
		
		wp_head();
		
		?>
	</head>
	<body <?php body_class(); ?>>
		<div id="page">
			<header>
				<h1><a href="<?php
				
				echo esc_url( home_url( '/' ) );
				
				?>"><?php
				
				bloginfo( 'name' );
				
				?></a></h1>
				<h2><?php
					
				echo apply_filters( 'grid_description', get_bloginfo( 'description' ) );
					
				?></h2>
			</header>
