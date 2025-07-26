<?php

// Register the Event custom post type
add_action('init', function () {
	register_post_type('event', array(
		'labels' => array(
			'name' => 'Events',
			'singular_name' => 'Event',
			'menu_name' => 'Events',
			'all_items' => 'All Events',
			'edit_item' => 'Edit Event',
			'view_item' => 'View Event',
			'view_items' => 'View Events',
			'add_new_item' => 'Add New Event',
			'add_new' => 'Add New Event',
			'new_item' => 'New Event',
			'parent_item_colon' => 'Parent Event:',
			'search_items' => 'Search Events',
			'not_found' => 'No events found',
			'not_found_in_trash' => 'No events found in Trash',
			'archives' => 'Event Archives',
			'attributes' => 'Event Attributes',
			'insert_into_item' => 'Insert into event',
			'uploaded_to_this_item' => 'Uploaded to this event',
			'filter_items_list' => 'Filter events list',
			'filter_by_date' => 'Filter events by date',
			'items_list_navigation' => 'Events list navigation',
			'items_list' => 'Events list',
			'item_published' => 'Event published.',
			'item_published_privately' => 'Event published privately.',
			'item_reverted_to_draft' => 'Event reverted to draft.',
			'item_scheduled' => 'Event scheduled.',
			'item_updated' => 'Event updated.',
			'item_link' => 'Event Link',
			'item_link_description' => 'A link to an event.',
		),
		'public' => false, // Prevents frontend single page
		'show_ui' => true, // Show in admin
		'show_in_rest' => true,
		'publicly_queryable' => true, // Prevents query on frontend
		'exclude_from_search' => true,
		'menu_icon' => 'dashicons-calendar-alt',
		'supports' => array(
			'title',
			'thumbnail',
		),
		'delete_with_user' => false,
	));
});

register_rest_field('event', 'meta', array(
	'get_callback' => function ($data) {
		return get_post_meta($data['id'], '');
	},
));

// Register custom meta fields for REST API
add_action('init', function () {
	// Event Subtitle
	register_post_meta('event', 'event_subtitle', array(
		'show_in_rest' => array(
			'schema' => array(
				'type' => 'string',
				'single' => true,
				'description' => 'Event Subtitle',
			),
		),
		'type' => 'string',
		'single' => true,
		'auth_callback' => function () {
			return current_user_can('edit_posts');
		},
		'sanitize_callback' => 'sanitize_text_field',
	));
	// Event External Link
	register_post_meta('event', 'event_external_link', array(
		'show_in_rest' => array(
			'schema' => array(
				'type' => 'string',
				'single' => true,
				'description' => 'Event External Link',
				'format' => 'uri',
			),
		),
		'type' => 'string',
		'single' => true,
		'auth_callback' => function () {
			return current_user_can('edit_posts');
		},
		'sanitize_callback' => 'esc_url_raw',
	));
});

// Move Featured Image meta box to main content area and set order for Event CPT
add_action('do_meta_boxes', function ($post_type, $context, $post) {
	if ($post_type === 'event' && $context === 'side') {
		remove_meta_box('postimagediv', 'event', 'side');
	}
}, 10, 3);

add_action('add_meta_boxes', function () {
	// Add Featured Image to main content area, high priority (after title, before custom meta)
	add_meta_box(
		'postimagediv',
		__('Featured Image'),
		'post_thumbnail_meta_box',
		'event',
		'normal',
		'high'
	);

	// Add meta box for Event Subtitle and External Link, normal context, default priority (after featured image)
	add_meta_box(
		'event_details',
		'Event Details',
		'render_event_meta_box',
		'event',
		'normal',
		'default'
	);
});

// Render the meta box fields
function render_event_meta_box($post)
{
	wp_nonce_field('save_event_meta', 'event_meta_nonce');
	$event_subtitle = get_post_meta($post->ID, 'event_subtitle', true);
	$event_external_link = get_post_meta($post->ID, 'event_external_link', true);
?>
	<p>
		<label for="event_subtitle"><strong>Event Subtitle</strong></label><br>
		<input type="text" id="event_subtitle" name="event_subtitle" value="<?php echo esc_attr($event_subtitle); ?>" style="width:100%;">
	</p>
	<p>
		<label for="event_external_link"><strong>Event External Link <span style="color:red">*</span></strong></label><br>
		<input type="url" id="event_external_link" name="event_external_link" value="<?php echo esc_attr($event_external_link); ?>" style="width:100%;" required>
	</p>
<?php
}

// Save the meta box fields
add_action('save_post_event', function ($post_id) {
	// Verify nonce
	if (!isset($_POST['event_meta_nonce']) || !wp_verify_nonce($_POST['event_meta_nonce'], 'save_event_meta')) {
		return;
	}
	// Check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	// Check permissions
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Save Event Subtitle
	if (isset($_POST['event_subtitle'])) {
		update_post_meta($post_id, 'event_subtitle', sanitize_text_field($_POST['event_subtitle']));
	}

	// Save External Link (required)
	if (isset($_POST['event_external_link'])) {
		update_post_meta($post_id, 'event_external_link', esc_url_raw($_POST['event_external_link']));
	}
});

// Optionally, require the External Link field before publishing
add_action('save_post_event', function ($post_id) {
	// Only run on non-autosave, non-revision, and only for publish/update
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (wp_is_post_revision($post_id)) return;

	// Only check if post is being published or updated
	$post = get_post($post_id);
	if ($post->post_status !== 'publish') return;

	$event_external_link = get_post_meta($post_id, 'event_external_link', true);
	if (empty($event_external_link)) {
		// Unpublish the post and show an admin notice
		remove_action('save_post_event', __FUNCTION__);
		wp_update_post(array(
			'ID' => $post_id,
			'post_status' => 'draft',
		));
		add_filter('redirect_post_location', function ($location) {
			return add_query_arg('event_external_link_missing', 1, $location);
		});
	}
});

// Show admin notice if external link is missing
add_action('admin_notices', function () {
	if (isset($_GET['event_external_link_missing'])) {
		echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> The <em>External Link</em> field is required for Events.</p></div>';
	}
});

// Display featured image and external link columns in Event admin list table
add_filter('manage_event_posts_columns', function ($columns) {
	$new_columns = array();
	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;
		if ($key === 'cb') {
			$new_columns['featured_image'] = __('Featured Image');
		}
		if ($key === 'title') {
			$new_columns['event_external_link'] = __('External Link');
		}
	}
	return $new_columns;
});

add_action('manage_event_posts_custom_column', function ($column, $post_id) {
	if ($column === 'featured_image') {
		$thumb = get_the_post_thumbnail($post_id, array(80, 80));
		if ($thumb) {
			echo $thumb;
		} else {
			echo '<span style="color:#aaa;">—</span>';
		}
	}
	if ($column === 'event_external_link') {
		$link = get_post_meta($post_id, 'event_external_link', true);
		if ($link) {
			$display = esc_url($link);
			echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr($display) . '" style="display:inline-block; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle;">' . esc_html($display) . '</a>';
		} else {
			echo '<span style="color:#aaa;">—</span>';
		}
	}
}, 10, 2);

// Optionally, make the columns narrow
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'event') {
		echo '<style>
			.column-featured_image { width: 120px; }
			.column-featured_image img { max-width: 80px; max-height: 80px; }
			.column-event_external_link { width: 300px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
			.column-event_external_link a { display: inline-block; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
		</style>';
	}
});

// Prevent direct access to single event posts on the frontend
add_action('template_redirect', function () {
	if (is_singular('event')) {
		wp_redirect(home_url('/events'));
		exit;
	}
});
