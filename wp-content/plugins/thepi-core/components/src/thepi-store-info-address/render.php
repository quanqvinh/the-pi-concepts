<?php

// Get block attributes with new attributes support
$enablePrefix = isset($attributes['enablePrefix']) ? (bool)$attributes['enablePrefix'] : true;
$enableSuffix = isset($attributes['enableSuffix']) ? (bool)$attributes['enableSuffix'] : true;
$prefix       = isset($attributes['prefix']) ? $attributes['prefix'] : '';
$suffix       = isset($attributes['suffix']) ? $attributes['suffix'] : '';
$underline    = !empty($attributes['underline']);
$showAsLink   = isset($attributes['showAsLink']) ? (bool)$attributes['showAsLink'] : false;
$linkUrl      = isset($attributes['link']) ? trim($attributes['link']) : '';
$linkTarget   = isset($attributes['linkTarget']) ? $attributes['linkTarget'] : '_blank';

// Query for the latest published store-info post
$query = new WP_Query([
	'post_type'      => 'store-info',
	'post_status'    => 'publish',
	'posts_per_page' => 1,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);

if (! $query->have_posts()) {
	$address = '';
} else {
	$query->the_post();
	$raw_address = get_post_meta(get_the_ID(), 'address', true);
	if (is_array($raw_address)) {
		$raw_address = reset($raw_address);
	}
	$address = is_string($raw_address) ? trim($raw_address) : '';
}
wp_reset_postdata();

?>
<p <?php echo get_block_wrapper_attributes(['class' => 'store-info__address']); ?>>

	<?php if ($enablePrefix && !empty($prefix)) : ?>
		<span class="store-info__address-prefix"><?php echo wp_kses_post($prefix); ?></span>
	<?php endif; ?>

	<?php
	$link_style = $underline ? 'text-decoration: underline;' : 'text-decoration: none;';
	if ($address) {
		if ($showAsLink && $linkUrl) {
			echo '<a href="' . esc_url($linkUrl) . '" class="store-info__address-link" style="' . esc_attr($link_style) . '" target="' . esc_attr($linkTarget) . '"';
			if ($linkTarget === '_blank') {
				echo ' rel="noopener noreferrer"';
			}
			echo '>' . esc_html($address) . '</a>';
		} else {
			echo '<span class="store-info__address-text">' . esc_html($address) . '</span>';
		}
	} else {
		echo '<span class="store-info__address-empty">' . esc_html__('No address', 'thepi-components') . '</span>';
	}
	?>

	<?php if ($enableSuffix && !empty($suffix)) : ?>
		<span class="store-info__address-suffix"><?php echo wp_kses_post($suffix); ?></span>
	<?php endif; ?>

</p>
