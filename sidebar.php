<!-- Hardcoding this for now -->
<div id="sidebar">
	<div class="holder">
		<section>
			<div class="column">
				<h2>Currently</h2>
				<p>
					I help with data at <a href="https://mapzen.com/">Mapzen</a>
				</p>
				<h2>Previously</h2>
				<ul>
					<li>
						<a href="http://www.newyorker.com/">New Yorker Tech</a>
					</li>
					<li>
						<a href="http://www.moma.org/">MoMA Digital Media</a>
					</li>
					<li>
						<a href="https://www.crunchbase.com/organization/outside-in">Outside.in</a>
					</li>
					<li>
						<a href="http://hellodesign.com/">Hello Design</a>
					</li>
				</ul>
			</div>
			<div class="column">
				<h2>Projects</h2>
				<ul>
					<li>
						<a href="http://youarehere.network/">You Are Here</a>
					</li>
					<li>
						<a href="http://ellieirons.com/flight-lines/">Flight Lines</a>
					</li>
					<li>
						<a href="http://occupyhere.org/">Occupy.here</a>
					</li>
					<li>
						<a href="https://wordpress.org/plugins/json-api/">JSON API</a>
					</li>
					<li>
						<a href="http://futurearchaeology.org/">Future Archaeology</a>
					</li>
					<li>
						<a href="http://youarenothere.org/">You Are Not Here</a>
					</li>
					<li>
						<a href="https://en.wikipedia.org/wiki/ShiftSpace">ShiftSpace</a>
					</li>
					<li>
						<a href="http://www.moma.org/interactives/exhibitions/2008/elasticmind/#/17/">Atlas Gloves</a>
					</li>
				</ul>
				<h2>Smaller Projects</h2>
				<ul>
					<li><a href="/linky/">Linky</a> (formerly <i>The Whale</i>)</li>
					<li><a href="http://onerothkoperhour.com/">One Rothko Per Hour</a></li>
					<li><a href="/slowtv/beefra/">Beefra Chill Time</a></li>
					<li><a href="/mubi/">What’s Playing on MUBI?</a></li>
				</ul>
			</div>
			<br class="clear">
		</section>
		<section>
			<div class="column">
				<h2>Social Media</h2>
				<ul>
					<li>
						<a href="https://twitter.com/dphiffer">Twitter</a>
					</li>
					<li>
						<a href="https://instagram.com/dphiffer">Instagram</a>
					</li>
					<li>
						<a href="http://mltshp.com/user/dphiffer">mltshp</a>
					</li>
					<li>
						<?php

						$month = date('m');

						if ($month < '03') {
							$playlist = '1pE8LCCCRbdiVNZkeivxcq'; // Almost Spring
						} else if ($month < '06') {
							$playlist = '5va5ltqJkbyiqb8JmDmKyb'; // Almost Summer
						} else if ($month < '09') {
							$playlist = '31Zx7HkUwNL7l4HlmVVDI0'; // Almost Fall
						} else {
							$playlist = '0a1vvEXHl6gT90dgvcl1CI'; // Almost Winter
						}

						?>
						<a href="https://open.spotify.com/user/dphiffer/playlist/<?php echo $playlist; ?>">Spotify</a>
					</li>
					<li>
						<a href="https://getpocket.com/@dphiffer">Pocket</a>
					</li>
					<li>
						<a href="https://pinboard.in/u:dphiffer">Pinboard</a>
					</li>
					<li>
						<a href="https://www.goodreads.com/user/show/777908-dan">Goodreads</a>
					</li>
					<li>
						<a href="https://www.flickr.com/photos/dphiffer">Flickr</a>
					</li>
				</ul>
			</div>
			<div class="column">
				<h2>Meta</h2>
				<ul>
					<li>
						<a href="/feed/">RSS feed</a>
					</li>
					<li>
						<a href="mailto:dan@phiffer.org">Contact</a>
					</li>
					<li>
						<a href="http://phiffer.org/etc/BA9280C9.asc">PGP key</a> / <a href="https://en.wikipedia.org/wiki/Public-key_cryptography">What is PGP?</a>
					</li>
					<li>
						<a href="https://github.com/dphiffer">GitHub</a>
					</li>
					<li>
						<a href="http://eepurl.com/ccb7n">Announcement list</a>
					</li>
					<li>
						<a href="/press/">Selected press</a>
					</li>
					<li>
						<a href="/dan_phiffer_cv.pdf">CV / résumé</a>
					</li>
				</ul>
			</div>
			<br class="clear">
		</section>
		<section>
			<div class="column">
				<h2><?php bloginfo('name'); ?></h2>
				<ul>
					<li>
						<a href="<?php bloginfo('url'); ?>">Home</a>
					</li>
					<?php
					
					$categories = get_categories(array(
						'orderby' => 'count',
						'order'   => 'DESC',
						'exclude' => '111'
					));
					foreach ($categories as $category) {
						echo "
							<li>
								<a href=\"/$category->slug/\">$category->name</a>
							</li>
						";
					}
					
					?>
				</ul>
			</div>
			<div class="column">
				<h2>Tags</h2>
				<div class="tags">
					<?php
					
					$tags = get_tags(array(
						'number'  => 15,
						'orderby' => 'count',
						'order'   => 'DESC'
					));
					
					$tag_links = array();
					foreach ($tags as $tag) {
						$tag_links[] = "<a href=\"/tags/$tag->slug/\" title=\"$tag->count posts\">$tag->name</a>";
					}
					
					echo implode(' ', $tag_links);
					
					?>
				</div>
				<a href="/tags/">More tags</a>
			</div>
			<br class="clear">
		</section>
	</div>
</div>
