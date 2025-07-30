<?php

/**
 * Server-side rendering for the Thepi Gallery Grid block.
 *
 * Loads images ordered by is_highlighted DESC, then date DESC, with initialAmount.
 * Show More button loads more with same order, hides when all loaded.
 */

$block_attrs = $attributes ?? array();

// Adjust attribute type handling to match block.json (numbers, booleans)
$show_more_enabled = !empty($block_attrs['showMore']) && filter_var($block_attrs['showMore'], FILTER_VALIDATE_BOOLEAN);
$show_more_each_time = isset($block_attrs['showMoreAmountEachTime']) ? intval($block_attrs['showMoreAmountEachTime']) : 3;
$initial_amount = isset($block_attrs['initialAmount']) ? intval($block_attrs['initialAmount']) : 7;

// For new attributes in block.json
$columns = isset($block_attrs['columns']) ? intval($block_attrs['columns']) : 9;
$column_gap = isset($block_attrs['columnGap']) ? intval($block_attrs['columnGap']) : 17;
$row_gap = isset($block_attrs['rowGap']) ? intval($block_attrs['rowGap']) : 17;
$row_height = isset($block_attrs['rowHeight']) ? intval($block_attrs['rowHeight']) : 400;
$enable_lightbox = !isset($block_attrs['enableLightBox']) || filter_var($block_attrs['enableLightBox'], FILTER_VALIDATE_BOOLEAN);

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
		<div
			id="gallery-grid"
			data-lightbox-enable="<?php echo $enable_lightbox ? 'true' : 'false' ?>"
			style="<?php
							// Inline style for grid based on block.json attributes
							echo 'display: grid;';
							echo 'grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr);';
							echo 'column-gap: ' . esc_attr($column_gap) . 'px;';
							echo 'row-gap: ' . esc_attr($row_gap) . 'px;';
							echo 'grid-auto-rows: ' . esc_attr($row_height) . 'px;';
							?>"
			<?php
			// Add attribute to indicate if show more button is displayed
			$show_more_displayed = ($show_more_enabled && $total_count > $initial_amount) ? 'true' : 'false';
			echo 'data-show-more-displayed="' . esc_attr($show_more_displayed) . '"';
			?>>
			<?php while ($initial_query->have_posts()) :
				$initial_query->the_post(); ?>
				<?php
				$image_id = get_post_thumbnail_id(get_the_ID());
				$full_image_url = wp_get_attachment_image_url($image_id, 'full');
				?>
				<?php if ($enable_lightbox) : ?>
					<a
						class="gallery-item"
						target="_blank"
						href="<?php echo esc_url($full_image_url); ?>"
						data-pswp-width="4000"
						data-pswp-height="4000"
						data-cropped="true">
						<?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
					</a>
				<?php else : ?>
					<div class="gallery-item">
						<?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
					</div>
				<?php endif; ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php if ($show_more_enabled && $total_count > $initial_amount) : ?>

			<div class="wp-block-button">
				<button
					class="wp-element-button gallery-show-more"
					type="button"
					data-loaded-count="<?php echo esc_attr($initial_amount); ?>"
					data-show-more-each-time="<?php echo esc_attr($show_more_each_time); ?>"
					data-total-count="<?php echo esc_attr($total_count); ?>">
					Show More
				</button>
			</div>
			<script>
				(function() {
					const btn = document.querySelector('.gallery-show-more');
					const grid = document.querySelector('#gallery-grid');
					if (!btn || !grid) return;

					// Pass PHP variable to JS for enableLightBox
					const enableLightBox = <?php echo $enable_lightbox ? 'true' : 'false'; ?>;

					btn.addEventListener('click', function() {
						const loaded = parseInt(btn.getAttribute('data-loaded-count'), 10);
						const eachTime = parseInt(btn.getAttribute('data-show-more-each-time'), 10);
						const total = parseInt(btn.getAttribute('data-total-count'), 10);

						btn.disabled = true;
						btn.textContent = 'Loading...';

						fetch(`/wp-json/thepi/v1/gallery/show-more?limit=${eachTime}&offset=${loaded}`)
							.then(function(response) {
								if (!response.ok) {
									throw new Error('Network response was not ok');
								}
								return response.json();
							})
							.then(async function(items) {
								const newLoaded = loaded + items.length;
								btn.setAttribute('data-loaded-count', newLoaded);

								if (newLoaded >= total || items.length < eachTime) {
									btn.style.display = 'none';
								} else {
									btn.disabled = false;
									btn.textContent = 'Show More';
								}

								for (var item of items) {
									let galleryEl;
									if (enableLightBox) {
										galleryEl = document.createElement('a');
										galleryEl.href = item.thumbnail;
										galleryEl.target = '_blank';
										galleryEl.setAttribute('data-pswp-width', 4000);
										galleryEl.setAttribute('data-pswp-height', 4000);
										galleryEl.setAttribute('data-cropped', true);
									} else {
										galleryEl = document.createElement('div');
									}
									galleryEl.className = 'gallery-item appear-animation';

									if (item.thumbnail) {
										const img = document.createElement('img');
										img.src = item.thumbnail;
										img.alt = item.title;
										img.style.width = '100%';
										img.style.height = 'auto';
										galleryEl.appendChild(img);
									} else {
										galleryEl.textContent = 'No image';
									}
									grid.appendChild(galleryEl);

									await new Promise(resolve => setTimeout(resolve, 150));
								}

								grid.dispatchEvent(new Event('grid-load-more-done'));
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
