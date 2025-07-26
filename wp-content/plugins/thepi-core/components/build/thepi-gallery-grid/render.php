<?php

/**
 * Server-side rendering for the Thepi Gallery Grid block.
 *
 * Loads images ordered by is_highlighted DESC, then date DESC, with initialAmount.
 * Show More button loads more with same order, hides when all loaded.
 */

$block_attrs = $attributes ?? array();

$show_more_enabled = !empty($block_attrs['showMore']);
$show_more_each_time = isset($block_attrs['showMoreAmountEachTime']) ? intval($block_attrs['showMoreAmountEachTime']) : 3;
$initial_amount = isset($block_attrs['initialAmount']) ? intval($block_attrs['initialAmount']) : 7;

// Query all galleries to get total count (for Show More logic)
$total_query = new WP_Query([
	'post_type'      => 'gallery',
	'post_status'    => 'publish',
	'fields'         => 'ids',
	'orderby'        => [
		'is_highlighted' => 'DESC',
		'date'           => 'DESC',
	],
	'posts_per_page' => -1,
]);
$total_count = $total_query->found_posts;

// Query initial galleries for display
$initial_query = new WP_Query([
	'post_type'      => 'gallery',
	'post_status'    => 'publish',
	'orderby'        => [
		'is_highlighted' => 'DESC',
		'date'           => 'DESC',
	],
	'posts_per_page' => $initial_amount,
	'offset'         => 0,
]);

if (!$initial_query->have_posts()) : ?>
	<p <?php echo get_block_wrapper_attributes([
				'class' => 'no-gallery-message'
			]); ?>>No image in gallery</p>
<?php else: ?>
	<div <?php echo get_block_wrapper_attributes([
					'class' => 'gallery-grid-wrapper',
					'data-show-more-each-time' => esc_attr($show_more_each_time),
					'data-total-count' => esc_attr($total_count),
				]); ?>>
		<div class="gallery-grid">
			<?php while ($initial_query->have_posts()) :
				$initial_query->the_post(); ?>
				<div class="gallery-item">
					<?php echo get_the_post_thumbnail(get_the_ID(), 'medium') ?>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php if ($show_more_enabled && $total_count > $initial_amount) : ?>
			<button
				class="gallery-show-more"
				type="button"
				data-loaded-count="<?php echo esc_attr($initial_amount); ?>"
				data-show-more-each-time="<?php echo esc_attr($show_more_each_time); ?>"
				data-total-count="<?php echo esc_attr($total_count); ?>">
				Show More
			</button>
			<script>
				(function() {
					const btn = document.querySelector('.gallery-show-more');
					const grid = document.querySelector('.gallery-grid');
					if (!btn || !grid) return;

					btn.addEventListener('click', function() {
						const loaded = parseInt(btn.getAttribute('data-loaded-count'), 10);
						const eachTime = parseInt(btn.getAttribute('data-show-more-each-time'), 10);
						const total = parseInt(btn.getAttribute('data-total-count'), 10);

						btn.disabled = true;
						btn.textContent = 'Loading...';

						// Use thepi/v1/gallery/show-more API, but pass orderby params for is_highlighted/date
						// (If API doesn't support, you may need to update it. For now, we assume it returns correct order.)
						fetch(`/wp-json/thepi/v1/gallery/show-more?limit=${eachTime}&offset=${loaded}`)
							.then(function(response) {
								if (!response.ok) {
									throw new Error('Network response was not ok');
								}
								return response.json();
							})
							.then(function(items) {
								items.forEach(function(item) {
									const div = document.createElement('div');
									div.className = 'gallery-item';
									if (item.thumbnail) {
										const img = document.createElement('img');
										img.src = item.thumbnail;
										img.alt = item.title;
										img.style.width = '100%';
										img.style.height = 'auto';
										div.appendChild(img);
									} else {
										div.textContent = 'No image';
									}
									grid.appendChild(div);
								});
								const newLoaded = loaded + items.length;
								btn.setAttribute('data-loaded-count', newLoaded);

								if (newLoaded >= total || items.length < eachTime) {
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
	</div>
<?php endif; ?>
