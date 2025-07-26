<?php

/**
 * Server-side rendering for Thepi Events Grid block with "Show More" functionality.
 *
 * Best practice:
 * - Render initial items server-side.
 * - Use AJAX to fetch more items on "Show More" click.
 * - Hide "Show More" button if no more items.
 * - Use a REST endpoint for fetching more items.
 */

$block_attrs = $attributes ?? array();

$initial_count = isset($block_attrs['initialAmount']) ? intval($block_attrs['initialAmount']) : 3;
$show_more_enabled = !empty($block_attrs['showMore']);
$show_more_each_time = isset($block_attrs['showMoreAmountEachTime']) ? intval($block_attrs['showMoreAmountEachTime']) : 3;

// Get gapX and gapY attributes for inline style
$gap_x = isset($block_attrs['gapX']) ? trim($block_attrs['gapX']) : '';
$gap_y = isset($block_attrs['gapY']) ? trim($block_attrs['gapY']) : '';
$inline_style = '';

if (!function_exists('thepi_validate_gap')) {
	function thepi_validate_gap($val, $default)
	{
		if ($val === '' || !is_numeric($val)) {
			return $default;
		}
		return $val . 'px';
	}
}

if ($gap_x !== '' || $gap_y !== '') {
	$gap_x_val = thepi_validate_gap($gap_x, '19px');
	$gap_y_val = thepi_validate_gap($gap_y, '50px');
	$inline_style = 'gap: ' . esc_attr($gap_y_val) . ' ' . esc_attr($gap_x_val) . ';';
}

// Query total count for "Show More" logic
$total_query = new WP_Query([
	'post_type'      => 'event',
	'post_status'    => 'publish',
	'fields'         => 'ids',
	'posts_per_page' => -1,
]);
$total_count = $total_query->found_posts;

// Query initial items
$query = new WP_Query([
	'post_type'      => 'event',
	'posts_per_page' => $initial_count,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
]);

if (!$query->have_posts()) : ?>
	<p <?php echo get_block_wrapper_attributes([
				'class' => 'no-event-message'
			]); ?>>No events found.</p>
<?php else: ?>
	<div <?php echo get_block_wrapper_attributes([
					'class' => 'thepi-events-container',
					'data-initial-count' => esc_attr($initial_count),
					'data-show-more-each-time' => esc_attr($show_more_each_time),
					'data-total-count' => esc_attr($total_count),
				]); ?>>
		<div class="thepi-events-grid" style="<?php echo $inline_style ?>">
			<?php while ($query->have_posts()) :
				$query->the_post();
				$subtitle = get_post_meta(get_the_ID(), 'event_subtitle', true);
				$external_link = get_post_meta(get_the_ID(), 'event_external_link', true); ?>
				<a href="<?php echo $external_link ?>" target="_blank" rel="noopener noreferrer" class="thepi-event-card" data-event-id="<?php echo esc_attr(get_the_ID()); ?>">
					<?php if (has_post_thumbnail()) : ?>
						<div class="thepi-event-thumb"><?php echo get_the_post_thumbnail(get_the_ID(), 'medium') ?></div>
						<h3 class="thepi-event-title"><?php echo esc_html(get_the_title()) ?></h3>
						<p class="thepi-event-subtitle"><?php echo esc_html($subtitle) ?></p>
					<?php endif; ?>
				</a>
			<?php endwhile;
			wp_reset_postdata(); ?>
		</div>
		<?php if ($show_more_enabled && $total_count > $initial_count) : ?>
			<button
				class="thepi-events-show-more"
				type="button"
				data-loaded-count="<?php echo esc_attr($initial_count); ?>"
				data-show-more-each-time="<?php echo esc_attr($show_more_each_time); ?>"
				data-total-count="<?php echo esc_attr($total_count); ?>">
				Show More
			</button>
	</div>
	<script>
		(function() {
			const btn = document.querySelector('.thepi-events-show-more');
			const grid = document.querySelector('.thepi-events-grid');
			if (!btn || !grid) return;

			btn.addEventListener('click', function() {
				const loaded = parseInt(btn.getAttribute('data-loaded-count'), 10);
				const eachTime = parseInt(btn.getAttribute('data-show-more-each-time'), 10);
				const total = parseInt(btn.getAttribute('data-total-count'), 10);

				btn.disabled = true;
				btn.textContent = 'Loading...';

				// Use the custom API endpoint for loading more events
				fetch(`/wp-json/thepi/v1/event-promotion/show-more?limit=${eachTime}&offset=${loaded}`)
					.then(function(response) {
						if (!response.ok) {
							throw new Error('Network response was not ok');
						}
						return response.json();
					})
					.then(function(posts) {
						posts.forEach(function(post) {
							const card = document.createElement('div');
							card.className = 'thepi-event-card';
							card.setAttribute('data-event-id', post.id);

							let thumb = '';
							if (post.thumbnail) {
								thumb = `<div class="thepi-event-thumb"><img src="${post.thumbnail.replace(/"/g, '&quot;')}" alt="${(post.title || '').replace(/"/g, '&quot;')}" /></div>`;
							}
							const title = `<h3 class="thepi-event-title">${post.title ? post.title.replace(/</g, "&lt;").replace(/>/g, "&gt;") : ''}</h3>`;
							const subtitle = post.subtitle ? `<p class="thepi-event-subtitle">${post.subtitle.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>` : '';

							card.innerHTML = thumb + title + subtitle;
							grid.appendChild(card);
						});
						const newLoaded = loaded + posts.length;
						btn.setAttribute('data-loaded-count', newLoaded);

						if (newLoaded >= total || posts.length < eachTime) {
							btn.style.display = 'none';
						} else {
							btn.disabled = false;
							btn.textContent = 'Show More';
						}
					})
					.catch(function() {
						btn.disabled = false;
						btn.textContent = 'Show More';
					});
			});
		})();
	</script>
<?php endif; ?>
<?php endif; ?>
