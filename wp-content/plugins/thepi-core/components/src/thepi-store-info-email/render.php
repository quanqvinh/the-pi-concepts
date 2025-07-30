<?php

// Get block attributes with new attributes support
$enablePrefix = isset($attributes['enablePrefix']) ? (bool)$attributes['enablePrefix'] : true;
$enableSuffix = isset($attributes['enableSuffix']) ? (bool)$attributes['enableSuffix'] : true;
$prefix       = isset($attributes['prefix']) ? $attributes['prefix'] : '';
$suffix       = isset($attributes['suffix']) ? $attributes['suffix'] : '';
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
	$email = '';
} else {
	$query->the_post();
	$raw_email = get_post_meta(get_the_ID(), 'email', true);
	if (is_array($raw_email)) {
		$raw_email = reset($raw_email);
	}
	$email = is_string($raw_email) ? trim($raw_email) : '';
}
wp_reset_postdata();

?>
<p <?php echo get_block_wrapper_attributes(['class' => 'store-info__email']); ?>>

	<?php if ($enablePrefix && !empty($prefix)) : ?>
		<span class="store-info__email-prefix"><?php echo wp_kses_post($prefix); ?></span>
	<?php endif; ?>

	<?php
	if ($email) {
		if ($showAsLink) {
			$link_style = $underline ? 'text-decoration: underline;' : 'text-decoration: none;';
			echo '<a href="mailto:' . esc_attr($email) . '" class="store-info__email-link" style="' . esc_attr($link_style) . '">' . esc_html($email) . '</a>';
		} else {
			echo '<span class="store-info__email-text">' . esc_html($email) . '</span>';
		}
	} else {
		echo '<span class="store-info__email-empty">' . esc_html__('No email address', 'thepi-components') . '</span>';
	}
	?>

	<?php if ($enableSuffix && !empty($suffix)) : ?>
		<span class="store-info__email-suffix"><?php echo wp_kses_post($suffix); ?></span>
	<?php endif; ?>

</p>
