<?php

// Get block attributes with new attributes support
$enablePrefix   = isset($attributes['enablePrefix']) ? (bool)$attributes['enablePrefix'] : true;
$enableSuffix   = isset($attributes['enableSuffix']) ? (bool)$attributes['enableSuffix'] : true;
$prefix         = isset($attributes['prefix']) ? $attributes['prefix'] : '';
$suffix         = isset($attributes['suffix']) ? $attributes['suffix'] : '';
$displayFormat  = isset($attributes['displayFormat']) ? $attributes['displayFormat'] : '24h';
$separator      = isset($attributes['separator']) ? $attributes['separator'] : ' - ';
$amPmCase       = isset($attributes['amPmCase']) ? $attributes['amPmCase'] : 'upper';
$amPmSpacing    = isset($attributes['amPmSpacing']) ? (bool)$attributes['amPmSpacing'] : false;

// Query for the latest published store-info post
$query = new WP_Query([
	'post_type'      => 'store-info',
	'post_status'    => 'publish',
	'posts_per_page' => 1,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);

if (! $query->have_posts()) {
	$open_time = '';
	$close_time = '';
} else {
	$query->the_post();
	$raw_open_time = get_post_meta(get_the_ID(), 'open_time', true);
	$raw_close_time = get_post_meta(get_the_ID(), 'close_time', true);
	if (is_array($raw_open_time)) {
		$raw_open_time = reset($raw_open_time);
	}
	if (is_array($raw_close_time)) {
		$raw_close_time = reset($raw_close_time);
	}
	$open_time = is_string($raw_open_time) ? trim($raw_open_time) : '';
	$close_time = is_string($raw_close_time) ? trim($raw_close_time) : '';
}
wp_reset_postdata();

// Inline helper: Format time string according to displayFormat, amPmCase, and amPmSpacing
$format_time = function ($time_str, $displayFormat = '24h', $amPmCase = 'upper', $amPmSpacing = false) {
	if (!is_string($time_str) || $time_str === '') {
		return '';
	}
	if ($displayFormat === '24h') {
		return $time_str;
	}
	// 12h format
	$parts = explode(':', $time_str);
	$h = isset($parts[0]) ? (int)$parts[0] : 0;
	$m = isset($parts[1]) ? $parts[1] : '00';
	$ampm = $h >= 12 ? 'PM' : 'AM';
	if ($amPmCase === 'lower') {
		$ampm = strtolower($ampm);
	} else {
		$ampm = strtoupper($ampm);
	}
	$h12 = $h % 12;
	if ($h12 === 0) $h12 = 12;
	$space = $amPmSpacing ? ' ' : '';
	return sprintf('%d:%s%s%s', str_pad($h12, 2, '0', STR_PAD_LEFT), str_pad($m, 2, '0', STR_PAD_LEFT), $space, $ampm);
};

?>
<p <?php echo get_block_wrapper_attributes(['class' => 'store-info__open-time']); ?>>

	<?php if ($enablePrefix && !empty($prefix)) : ?>
		<span class="store-info__open-time-prefix"><?php echo wp_kses_post($prefix); ?></span>
	<?php endif; ?>

	<?php
	if ($open_time || $close_time) :
	?>
		<span class="store-info__open-time-value">
			<?php
			echo esc_html($format_time($open_time, $displayFormat, $amPmCase, $amPmSpacing));
			echo esc_html($separator);
			echo esc_html($format_time($close_time, $displayFormat, $amPmCase, $amPmSpacing));
			?>
		</span>
	<?php else : ?>
		<span class="store-info__open-time-empty"><?php echo esc_html__('No open/close time', 'thepi-components'); ?></span>
	<?php endif; ?>

	<?php if ($enableSuffix && !empty($suffix)) : ?>
		<span class="store-info__open-time-suffix"><?php echo wp_kses_post($suffix); ?></span>
	<?php endif; ?>

</p>
