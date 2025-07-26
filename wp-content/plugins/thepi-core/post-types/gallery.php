<?php
add_action('init', function () {
	register_post_type('gallery', array(
		'labels' => array(
			'name' => 'Galleries',
			'singular_name' => 'Gallery',
			'menu_name' => 'Galleries',
			'all_items' => 'All Galleries',
			'edit_item' => 'Edit Gallery',
			'view_item' => 'View Gallery',
			'view_items' => 'View Galleries',
			'add_new_item' => 'Add New Gallery',
			'add_new' => 'Add New Gallery',
			'new_item' => 'New Gallery',
			'parent_item_colon' => 'Parent Gallery:',
			'search_items' => 'Search Galleries',
			'not_found' => 'No galleries found',
			'not_found_in_trash' => 'No galleries found in Trash',
			'archives' => 'Gallery Archives',
			'attributes' => 'Gallery Attributes',
			'insert_into_item' => 'Insert into gallery',
			'uploaded_to_this_item' => 'Uploaded to this gallery',
			'filter_items_list' => 'Filter galleries list',
			'filter_by_date' => 'Filter galleries by date',
			'items_list_navigation' => 'Galleries list navigation',
			'items_list' => 'Galleries list',
			'item_published' => 'Gallery published.',
			'item_published_privately' => 'Gallery published privately.',
			'item_reverted_to_draft' => 'Gallery reverted to draft.',
			'item_scheduled' => 'Gallery scheduled.',
			'item_updated' => 'Gallery updated.',
			'item_link' => 'Gallery Link',
			'item_link_description' => 'A link to a gallery.',
		),
		'public' => false, // Prevents frontend single page
		'show_ui' => true, // Show in admin
		'show_in_rest' => true,
		'publicly_queryable' => true, // Prevents query on frontend
		'exclude_from_search' => true,
		'menu_icon' => 'dashicons-images-alt2',
		'supports' => array(
			'title',
			'thumbnail',
		),
		'delete_with_user' => false,
	));
});

// Register the is_highlighted meta as public and available in the REST API
add_action('init', function () {
	register_post_meta('gallery', 'is_highlighted', array(
		'show_in_rest' => true,
		'type' => 'string',
		'single' => true,
		'auth_callback' => '__return_true', // allow public read
	));
});

// Display featured image and highlight flag columns in Gallery admin list table
add_filter('manage_gallery_posts_columns', function ($columns) {
	// Insert the featured image and highlight flag columns after the checkbox
	$new_columns = array();
	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;
		if ($key === 'cb') {
			$new_columns['featured_image'] = __('Featured Image');
			$new_columns['highlighted_flag'] = __('Highlighted', 'thepi-core');
		}
	}
	return $new_columns;
});

add_action('manage_gallery_posts_custom_column', function ($column, $post_id) {
	if ($column === 'featured_image') {
		$thumb = get_the_post_thumbnail($post_id, array(80, 80));
		if ($thumb) {
			echo $thumb;
		} else {
			echo '<span style="color:#aaa;">—</span>';
		}
	}
	if ($column === 'highlighted_flag') {
		$is_highlighted = get_post_meta($post_id, 'is_highlighted', true);
		if ($is_highlighted == '1') {
			echo '<span style="color: #46b450; font-weight: bold;" title="Highlighted">&#10003;</span>';
		} else {
			echo '<span style="color:#aaa;">—</span>';
		}
	}
}, 10, 2);

// Make the Highlighted column sortable
add_filter('manage_edit-gallery_sortable_columns', function ($columns) {
	$columns['highlighted_flag'] = 'highlighted_flag';
	return $columns;
});

// Handle sorting by Highlighted column
add_action('pre_get_posts', function ($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}
	$orderby = $query->get('orderby');
	$post_type = $query->get('post_type');
	if ($post_type === 'gallery' && $orderby === 'highlighted_flag') {
		$order = strtoupper($query->get('order')) === 'ASC' ? 'ASC' : 'DESC';

		// Remove meta_key to avoid INNER JOIN and allow LEFT JOIN for posts without the meta
		$query->set('meta_query', array(
			'relation' => 'OR',
			array(
				'key' => 'is_highlighted',
				'compare' => 'EXISTS',
			),
			array(
				'key' => 'is_highlighted',
				'compare' => 'NOT EXISTS',
			),
		));
		$query->set('orderby', array(
			'mt1' => $order,
			'date' => 'DESC',
		));
	}
});

// Optionally, make the columns narrow
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'gallery') {
		echo '<style>
			.column-featured_image { width: 120px; }
			.column-featured_image img { max-width: 80px; max-height: 80px; }
			.column-highlighted_flag { width: 120px; text-align: center; vertical-align: middle !important; }
			td.column-highlighted_flag span { font-size: 20px; }
		</style>';
	}
});

// Add meta box for "is_highlighted"
add_action('add_meta_boxes', function () {
	add_meta_box(
		'gallery_is_highlighted',
		__('Highlight Gallery Image', 'thepi-core'),
		function ($post) {
			$value = get_post_meta($post->ID, 'is_highlighted', true);
			wp_nonce_field('gallery_is_highlighted_nonce', 'gallery_is_highlighted_nonce_field');
?>
		<p>
			<label>
				<input type="checkbox" name="is_highlighted" value="1" <?php checked($value, '1'); ?> />
				<?php esc_html_e('Mark this image as highlighted', 'thepi-core'); ?>
			</label>
		</p>
<?php
		},
		'gallery',
		'side',
		'low'
	);
});

// Save the "is_highlighted" meta value
add_action('save_post_gallery', function ($post_id) {
	// Verify nonce
	if (!isset($_POST['gallery_is_highlighted_nonce_field']) || !wp_verify_nonce($_POST['gallery_is_highlighted_nonce_field'], 'gallery_is_highlighted_nonce')) {
		return;
	}
	// Don't autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	// Check user permission
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	// Save or delete meta
	if (isset($_POST['is_highlighted'])) {
		update_post_meta($post_id, 'is_highlighted', '1');
	} else {
		delete_post_meta($post_id, 'is_highlighted');
	}
});

// Prevent direct access to single gallery posts on the frontend
add_action('template_redirect', function () {
	if (is_singular('gallery')) {
		wp_redirect(home_url());
		exit;
	}
});
