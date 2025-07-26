<?php

add_action('rest_api_init', function () {
	register_rest_route('thepi/v1', '/press-featuring/show-more', array(
		'methods' => 'GET',
		'callback' => function ($request) {
			// Get offset and limit from query string, with defaults
			$offset = intval($request->get_param('offset'));
			$limit = intval($request->get_param('limit'));
			if ($limit <= 0) {
				$limit = 3;
			}

			$args = array(
				'post_type'      => 'press-featuring',
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			);

			$query = new WP_Query($args);
			$items = array();

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$items[] = array(
						'id'             => get_the_ID(),
						'title'          => get_the_title(),
						'subtitle'       => get_post_meta(get_the_ID(), 'press_featuring_subtitle', true),
						'thumbnail'      => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
						'external_link'  => get_post_meta(get_the_ID(), 'press_featuring_external_link', true),
					);
				}
				wp_reset_postdata();
			}

			return rest_ensure_response($items);
		},
		'permission_callback' => '__return_true',
	));
});
