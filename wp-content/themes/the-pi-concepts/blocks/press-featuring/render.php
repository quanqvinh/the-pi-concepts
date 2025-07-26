<?php

$query = new WP_Query([
	'post_type'      => 'press_featuring',
	'posts_per_page' => 3,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);

if (!$query->have_posts()) {
	return '<p>No press featuring posts found.</p>';
}

ob_start();
echo '<div class="press-featuring-grid">';
while ($query->have_posts()) {
	$query->the_post();
	$subtitle = get_post_meta(get_the_ID(), 'press_featuring_subtitle', true);
	$link     = get_post_meta(get_the_ID(), 'press_featuring_link', true);

	echo '<div class="press-featuring-item">';
	echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener">';
	if (has_post_thumbnail()) {
		echo '<div class="press-featuring-thumb">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
	}
	echo '<h3 class="press-featuring-title">' . esc_html(get_the_title()) . '</h3>';
	if ($subtitle) {
		echo '<p class="subtitle">' . esc_html($subtitle) . '</p>';
	}
	echo '</a>';
	echo '</div>';
}
echo '</div>';
wp_reset_postdata();
return ob_get_clean();
