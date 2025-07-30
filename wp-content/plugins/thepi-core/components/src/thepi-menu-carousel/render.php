<?php

$block_attrs = $attributes ?? array();

// New: images is now an array of image IDs (ints)
$image_ids = array();
if (isset($block_attrs['images']) && is_array($block_attrs['images'])) {
	foreach ($block_attrs['images'] as $img_id) {
		// Only allow valid positive integers
		if (is_numeric($img_id) && intval($img_id) > 0) {
			$image_ids[] = intval($img_id);
		}
	}
}

// Handle aspectRatio attribute
$aspect_ratio = isset($block_attrs['aspectRatio']) && is_string($block_attrs['aspectRatio']) && preg_match('/^\d+\s*\/\s*\d+$/', $block_attrs['aspectRatio'])
	? $block_attrs['aspectRatio']
	: '12/13';

// Calculate padding-top for aspect ratio (format: width/height)
list($ar_width, $ar_height) = array_map('trim', explode('/', $aspect_ratio));
$padding_top = '0';
if (is_numeric($ar_width) && is_numeric($ar_height) && $ar_width > 0) {
	$padding_top = (floatval($ar_height) / floatval($ar_width)) * 100 . '%';
} else {
	$padding_top = (13 / 12) * 100 . '%'; // fallback
}

if (empty($image_ids)) {
	echo '<div ' . get_block_wrapper_attributes() . '>';
	echo '<p>No images in carousel.</p>';
	echo '</div>';
} else {
	$carousel_id = 'menu-carousel-' . uniqid();
	// Preload all image data (url, alt, etc) for each ID
	$images = array();
	foreach ($image_ids as $img_id) {
		$url = wp_get_attachment_image_url($img_id, 'full');
		$alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
		// fallback: use post title if alt is empty
		if ($alt === '') {
			$img_post = get_post($img_id);
			$alt = $img_post ? $img_post->post_title : '';
		}
		if ($url) {
			$images[] = array(
				'url' => $url,
				'alt' => $alt,
			);
		}
	}
?>
	<div <?php echo get_block_wrapper_attributes(['class' => 'thepi-menu-carousel']); ?>>
		<div
			id="<?php echo esc_attr($carousel_id); ?>"
			class="thepi-menu-carousel__container"
			style="padding-top: <?php echo esc_attr($padding_top); ?>;">
			<div class="thepi-menu-carousel__slides">
				<?php foreach ($images as $idx => $img): ?>
					<img
						src="<?php echo esc_url($img['url']); ?>"
						class="thepi-menu-carousel__slide<?php echo $idx === 0 ? ' active' : ''; ?>"
						data-carousel-index="<?php echo esc_attr($idx); ?>"
						alt="<?php echo esc_attr($img['alt']); ?>" />
				<?php endforeach; ?>
			</div>
			<?php if (count($images) > 1): ?>
				<button type="button" class="thepi-menu-carousel__prev"></button>
				<button type="button" class="thepi-menu-carousel__next"></button>
			<?php endif; ?>
		</div>
	</div>
	<script>
		(function() {
			var carousel = document.getElementById('<?php echo esc_js($carousel_id); ?>')
			if (!carousel) return
			var slides = carousel.querySelectorAll('.thepi-menu-carousel__slide')
			var current = 0

			// Fix onload event: add loaded class after image loads with delay
			slides.forEach(function(slide) {
				const addLoadedImageClasses = (slide) => {
					setTimeout(function() {
						slide.classList.add('loaded');
					}, 500);
				}

				if (slide.complete) {
					addLoadedImageClasses(slide)
				} else {
					slide.addEventListener('load', function() {
						addLoadedImageClasses(slide)
					});
				}
			});

			function showSlide(idx) {
				slides.forEach(function(slide, i) {
					if (i === idx) {
						slide.classList.add('active');
					} else {
						slide.classList.remove('active');
					}
				});
			}
			showSlide(current);

			<?php if (count($images) > 1): ?>
				var prevBtn = carousel.querySelector('.thepi-menu-carousel__prev');
				var nextBtn = carousel.querySelector('.thepi-menu-carousel__next');
				prevBtn.addEventListener('click', function() {
					current = (current - 1 + slides.length) % slides.length;
					showSlide(current);
				});
				nextBtn.addEventListener('click', function() {
					current = (current + 1) % slides.length;
					showSlide(current);
				});
			<?php endif; ?>
		})()
	</script>
<?php
}
