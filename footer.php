			<footer>
				
			</footer>
		</div>
		<script src="<?php
		
		$last_modified = filemtime(get_stylesheet_directory() . "/js/grid.js");
		echo get_stylesheet_directory_uri() . "/js/grid.js?$last_modified";
		
		?>"></script>
		<?php wp_footer(); ?>
	</body>
</html>
