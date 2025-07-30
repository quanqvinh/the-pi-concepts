<?php

// Get block attributes with new attributes support
$enablePrefix = isset($attributes['enablePrefix']) ? (bool)$attributes['enablePrefix'] : true;
$enableSuffix = isset($attributes['enableSuffix']) ? (bool)$attributes['enableSuffix'] : true;
$prefix       = isset($attributes['prefix']) ? $attributes['prefix'] : '';
$suffix       = isset($attributes['suffix']) ? $attributes['suffix'] : '';
$separator    = isset($attributes['separator']) ? $attributes['separator'] : ' ';
$underline    = !empty($attributes['underline']);
$showAsLink   = isset($attributes['showAsLink']) ? (bool)$attributes['showAsLink'] : false;

// Query for the latest published store-info post
$query = new WP_Query([
	'post_type'      => 'store-info',
	'post_status'    => 'publish',
	'posts_per_page' => 1,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);

if (! $query->have_posts()) {
	$phone = '';
} else {
	$query->the_post();
	$raw_phone = get_post_meta(get_the_ID(), 'phone', true);
	if (is_array($raw_phone)) {
		$raw_phone = reset($raw_phone);
	}
	$phone = is_string($raw_phone) ? trim($raw_phone) : '';
}
wp_reset_postdata();

// Format phone number with separator (inline, no function)
if (!is_string($phone) || $phone === '') {
	$formatted_phone = '';
	$href_phone = '';
} else {
	// Replace all whitespace, dot, or dash with the separator for display
	$formatted_phone = preg_replace('/[\s\.\-]+/', $separator, $phone);
	// For href, replace leading ^0 with +84, but do not change display
	$href_phone = preg_replace('/^0/', '+84', $phone);
}

?>
<p <?php echo get_block_wrapper_attributes(['class' => 'store-info__phone']); ?>>

	<?php if ($enablePrefix && !empty($prefix)) : ?>
		<span class="store-info__phone-prefix"><?php echo wp_kses_post($prefix); ?></span>
	<?php endif; ?>

	<?php
	if ($formatted_phone) {
		if ($showAsLink) {
			$link_style = $underline ? 'text-decoration: underline;' : 'text-decoration: none;';
			echo '<a href="tel:' . esc_attr($href_phone) . '" class="store-info__phone-link" style="' . esc_attr($link_style) . '">' . esc_html($formatted_phone) . '</a>';
		} else {
			echo '<span class="store-info__phone-text">' . esc_html($formatted_phone) . '</span>';
		}
	} else {
		echo '<span class="store-info__phone-empty">' . esc_html__('No phone number', 'thepi-components') . '</span>';
	}
	?>

	<?php if ($enableSuffix && !empty($suffix)) : ?>
		<span class="store-info__phone-suffix"><?php echo wp_kses_post($suffix); ?></span>
	<?php endif; ?>

</p>
