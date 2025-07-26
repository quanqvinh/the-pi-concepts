<?php

add_action('init', function () {
	register_post_type('press-featuring', array(
		'labels' => array(
			'name' => 'Press Featuring',
			'singular_name' => 'Press Featuring',
			'menu_name' => 'Press Featuring',
			'all_items' => 'All Press Featuring',
			'edit_item' => 'Edit Press Featuring',
			'view_item' => 'View Press Featuring',
			'view_items' => 'View Press Featuring',
			'add_new_item' => 'Add New Press Featuring',
			'add_new' => 'Add New Press Featuring',
			'new_item' => 'New Press Featuring',
			'parent_item_colon' => 'Parent Press Featuring:',
			'search_items' => 'Search Press Featuring',
			'not_found' => 'No press featuring found',
			'not_found_in_trash' => 'No press featuring found in Trash',
			'archives' => 'Press Featuring Archives',
			'attributes' => 'Press Featuring Attributes',
			'insert_into_item' => 'Insert into press featuring',
			'uploaded_to_this_item' => 'Uploaded to this press featuring',
			'filter_items_list' => 'Filter press featuring list',
			'filter_by_date' => 'Filter press featuring by date',
			'items_list_navigation' => 'Press Featuring list navigation',
			'items_list' => 'Press Featuring list',
			'item_published' => 'Press Featuring published.',
			'item_published_privately' => 'Press Featuring published privately.',
			'item_reverted_to_draft' => 'Press Featuring reverted to draft.',
			'item_scheduled' => 'Press Featuring scheduled.',
			'item_updated' => 'Press Featuring updated.',
			'item_link' => 'Press Featuring Link',
			'item_link_description' => 'A link to a press featuring.',
		),
		'public' => false, // No detail page on frontend
		'show_ui' => true,
		'show_in_rest' => true,
		'publicly_queryable' => false,
		'has_archive' => false,
		'exclude_from_search' => true,
		'menu_icon' => 'dashicons-star-filled',
		'supports' => array(
			'title',
			'thumbnail',
		),
		'delete_with_user' => false,
	));
});

register_rest_field('press-featuring', 'meta', array(
	'get_callback' => function ($data) {
		return get_post_meta($data['id'], '');
	},
));

// Register custom meta fields for REST API
add_action('init', function () {
	// Press Featuring Subtitle
	register_post_meta('press-featuring', 'press_featuring_subtitle', array(
		'show_in_rest' => array(
			'schema' => array(
				'type' => 'string',
				'single' => true,
				'description' => 'Press Featuring Subtitle',
			),
		),
		'type' => 'string',
		'single' => true,
		'auth_callback' => function () {
			return current_user_can('edit_posts');
		},
		'sanitize_callback' => 'sanitize_text_field',
	));
	// Press Featuring External Link
	register_post_meta('press-featuring', 'press_featuring_external_link', array(
		'show_in_rest' => array(
			'schema' => array(
				'type' => 'string',
				'single' => true,
				'description' => 'Press Featuring External Link',
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

// Move Featured Image meta box to main content area and set order
add_action('do_meta_boxes', function ($post_type, $context, $post) {
	if ($post_type === 'press-featuring' && $context === 'side') {
		remove_meta_box('postimagediv', 'press-featuring', 'side');
	}
}, 10, 3);

add_action('add_meta_boxes', function () {
	// Add Featured Image to main content area, high priority (after title, before custom meta)
	add_meta_box(
		'postimagediv',
		__('Featured Image'),
		'post_thumbnail_meta_box',
		'press-featuring',
		'normal',
		'high'
	);

	// Add meta box for Press Featuring Subtitle and External Link, normal context, default priority (after featured image)
	add_meta_box(
		'press_featuring_details',
		'Press Featuring Details',
		'render_press_featuring_meta_box',
		'press-featuring',
		'normal',
		'default'
	);
});

// Render the meta box fields
function render_press_featuring_meta_box($post)
{
	wp_nonce_field('save_press_featuring_meta', 'press_featuring_meta_nonce');
	$subtitle = get_post_meta($post->ID, 'press_featuring_subtitle', true);
	$external_link = get_post_meta($post->ID, 'press_featuring_external_link', true);
?>
	<p>
		<label for="press_featuring_subtitle"><strong>Press Featuring Subtitle</strong></label><br>
		<input type="text" id="press_featuring_subtitle" name="press_featuring_subtitle" value="<?php echo esc_attr($subtitle); ?>" style="width:100%;">
	</p>
	<p>
		<label for="press_featuring_external_link"><strong>Press Featuring External Link <span style="color:red">*</span></strong></label><br>
		<input type="url" id="press_featuring_external_link" name="press_featuring_external_link" value="<?php echo esc_attr($external_link); ?>" style="width:100%;" required>
	</p>
<?php
}

// Save the meta box fields
add_action('save_post_press-featuring', function ($post_id) {
	// Verify nonce
	if (!isset($_POST['press_featuring_meta_nonce']) || !wp_verify_nonce($_POST['press_featuring_meta_nonce'], 'save_press_featuring_meta')) {
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

	// Save Subtitle
	if (isset($_POST['press_featuring_subtitle'])) {
		update_post_meta($post_id, 'press_featuring_subtitle', sanitize_text_field($_POST['press_featuring_subtitle']));
	}

	// Save External Link (required)
	if (isset($_POST['press_featuring_external_link'])) {
		update_post_meta($post_id, 'press_featuring_external_link', esc_url_raw($_POST['press_featuring_external_link']));
	}
});

// Optionally, require the External Link field before publishing
add_action('save_post_press-featuring', function ($post_id) {
	// Only run on non-autosave, non-revision, and only for publish/update
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (wp_is_post_revision($post_id)) return;

	// Only check if post is being published or updated
	$post = get_post($post_id);
	if ($post->post_status !== 'publish') return;

	$external_link = get_post_meta($post_id, 'press_featuring_external_link', true);
	if (empty($external_link)) {
		// Unpublish the post and show an admin notice
		remove_action('save_post_press-featuring', __FUNCTION__);
		wp_update_post(array(
			'ID' => $post_id,
			'post_status' => 'draft',
		));
		add_filter('redirect_post_location', function ($location) {
			return add_query_arg('press_featuring_external_link_missing', 1, $location);
		});
	}
});

// Show admin notice if external link is missing
add_action('admin_notices', function () {
	if (isset($_GET['press_featuring_external_link_missing'])) {
		echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> The <em>External Link</em> field is required for Press Featuring.</p></div>';
	}
});

// Display featured image and external link columns in Press Featuring admin list table
add_filter('manage_press-featuring_posts_columns', function ($columns) {
	$new_columns = array();
	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;
		if ($key === 'cb') {
			$new_columns['featured_image'] = __('Featured Image');
		}
		if ($key === 'title') {
			$new_columns['external_link'] = __('External Link');
		}
	}
	return $new_columns;
});

add_action('manage_press-featuring_posts_custom_column', function ($column, $post_id) {
	if ($column === 'featured_image') {
		$thumb = get_the_post_thumbnail($post_id, array(80, 80));
		if ($thumb) {
			echo $thumb;
		} else {
			echo '<span style="color:#aaa;">—</span>';
		}
	}
	if ($column === 'external_link') {
		$link = get_post_meta($post_id, 'press_featuring_external_link', true);
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
	if ($screen && $screen->post_type === 'press-featuring') {
		echo '<style>
			.column-featured_image { width: 120px; }
			.column-featured_image img { max-width: 80px; max-height: 80px; }
			.column-external_link { width: 300px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
			.column-external_link a { display: inline-block; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
		</style>';
	}
});


// Prevent direct access to single press-featuring posts on the frontend
add_action('template_redirect', function () {
	if (is_singular('press-featuring')) {
		wp_redirect(home_url('/press'));
		exit;
	}
});
