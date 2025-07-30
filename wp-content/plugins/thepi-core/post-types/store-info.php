<?php

// Register the Store Info custom post type
add_action('init', function () {
	register_post_type('store-info', array(
		'labels' => array(
			'name' => 'Store Info',
			'singular_name' => 'Store Info',
			'menu_name' => 'Store Info',
			'all_items' => 'All Store Info',
			'edit_item' => 'Edit Store Info',
			'view_item' => 'View Store Info',
			'view_items' => 'View Store Info',
			'add_new_item' => 'Add New Store Info',
			'add_new' => 'Add New Store Info',
			'new_item' => 'New Store Info',
			'parent_item_colon' => 'Parent Store Info:',
			'search_items' => 'Search Store Info',
			'not_found' => 'No store info found',
			'not_found_in_trash' => 'No store info found in Trash',
			'archives' => 'Store Info Archives',
			'attributes' => 'Store Info Attributes',
			'insert_into_item' => 'Insert into store info',
			'uploaded_to_this_item' => 'Uploaded to this store info',
			'filter_items_list' => 'Filter store info list',
			'filter_by_date' => 'Filter store info by date',
			'items_list_navigation' => 'Store Info list navigation',
			'items_list' => 'Store Info list',
			'item_published' => 'Store Info published.',
			'item_published_privately' => 'Store Info published privately.',
			'item_reverted_to_draft' => 'Store Info reverted to draft.',
			'item_scheduled' => 'Store Info scheduled.',
			'item_updated' => 'Store Info updated.',
			'item_link' => 'Store Info Link',
			'item_link_description' => 'A link to a store info.',
		),
		'public' => false,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-store',
		'supports' => array(
			'title',
		),
		'delete_with_user' => false,
	));
});

register_rest_field('store-info', 'meta', array(
	'get_callback' => function ($data) {
		return get_post_meta($data['id'], '');
	},
));

// Add meta boxes for Phone, Email, Address, and Open/Close Time fields
add_action('add_meta_boxes', function () {
	add_meta_box(
		'store_info_details',
		'Store Info Details',
		'render_store_info_meta_box',
		'store-info',
		'normal',
		'default'
	);
});

// Render the meta box fields
function render_store_info_meta_box($post)
{
	wp_nonce_field('save_store_info_meta', 'store_info_meta_nonce');
	$phone   = get_post_meta($post->ID, 'phone', true);
	$email   = get_post_meta($post->ID, 'email', true);
	$address = get_post_meta($post->ID, 'address', true);
	$open_time = get_post_meta($post->ID, 'open_time', true);
	$close_time = get_post_meta($post->ID, 'close_time', true);
?>
	<div style="display: flex; gap: 16px; align-items: flex-end; margin-bottom: 16px;">
		<div>
			<label for="store_info_phone" style="display: inline-flex; align-items: center; margin-bottom: 2px;">
				<span class="dashicons dashicons-phone" style="vertical-align:middle; margin-right:2px; font-size: 16px; height: unset;"></span>
				<strong>Phone</strong>
			</label><br>
			<input type="text" id="store_info_phone" name="store_info_phone" value="<?php echo esc_attr($phone); ?>" style="width:150px;" required>
		</div>
		<div>
			<label for="store_info_email" style="display: inline-flex; align-items: center; margin-bottom: 2px;">
				<span class="dashicons dashicons-email" style="vertical-align:middle; margin-right:2px; font-size: 16px; height: unset;"></span>
				<strong>Email</strong>
			</label><br>
			<input type="email" id="store_info_email" name="store_info_email" value="<?php echo esc_attr($email); ?>" style="width:240px;" required>
		</div>
		<div style="flex: 1;">
			<label for="store_info_address" style="display: inline-flex; align-items: center; margin-bottom: 2px;">
				<span class="dashicons dashicons-location" style="vertical-align:middle; margin-right:2px; font-size: 16px; height: unset;"></span>
				<strong>Address</strong>
			</label><br>
			<input type="text" id="store_info_address" name="store_info_address" value="<?php echo esc_attr($address); ?>" style="width:100%;" required>
		</div>
	</div>
	<div style="display: flex; gap: 16px; align-items: flex-end; margin-bottom: 24px;">
		<div>
			<label for="store_info_open_time" style="display: inline-flex; align-items: center; margin-bottom: 2px;">
				<span class="dashicons dashicons-clock" style="vertical-align:middle; margin-right:2px; font-size: 16px; height: unset;"></span>
				<strong>Open Time</strong>
			</label><br>
			<input type="time" id="store_info_open_time" name="store_info_open_time" value="<?php echo esc_attr($open_time); ?>" style="width:150px;">
		</div>
		<div>
			<label for="store_info_close_time" style="display: inline-flex; align-items: center; margin-bottom: 2px;">
				<span class="dashicons dashicons-clock" style="vertical-align:middle; margin-right:2px; font-size: 16px; height: unset;"></span>
				<strong>Close Time</strong>
			</label><br>
			<input type="time" id="store_info_close_time" name="store_info_close_time" value="<?php echo esc_attr($close_time); ?>" style="width:150px;">
		</div>
	</div>
<?php
}

// Save the meta box fields
add_action('save_post_store-info', function ($post_id) {
	// Verify nonce
	if (!isset($_POST['store_info_meta_nonce']) || !wp_verify_nonce($_POST['store_info_meta_nonce'], 'save_store_info_meta')) {
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

	// Save Phone
	if (isset($_POST['store_info_phone'])) {
		update_post_meta($post_id, 'phone', sanitize_text_field($_POST['store_info_phone']));
	}
	// Save Email
	if (isset($_POST['store_info_email'])) {
		update_post_meta($post_id, 'email', sanitize_email($_POST['store_info_email']));
	}
	// Save Address
	if (isset($_POST['store_info_address'])) {
		update_post_meta($post_id, 'address', sanitize_text_field($_POST['store_info_address']));
	}
	// Save Open Time
	if (isset($_POST['store_info_open_time'])) {
		update_post_meta($post_id, 'open_time', sanitize_text_field($_POST['store_info_open_time']));
	}
	// Save Close Time
	if (isset($_POST['store_info_close_time'])) {
		update_post_meta($post_id, 'close_time', sanitize_text_field($_POST['store_info_close_time']));
	}
	// Remove socials meta if it exists (cleanup from previous versions)
	delete_post_meta($post_id, 'socials');
});

// Prevent creating more than one "store-info" post and remove the "Trash" action

// 1. Prevent creating more than one post
add_filter('post_new_class', function ($classes, $post_type) {
	if ($post_type === 'store-info') {
		$count = wp_count_posts('store-info')->publish + wp_count_posts('store-info')->draft + wp_count_posts('store-info')->pending + wp_count_posts('store-info')->future + wp_count_posts('store-info')->private;
		if ($count >= 1) {
			// Redirect to the edit screen of the existing post
			$existing = get_posts([
				'post_type'      => 'store-info',
				'posts_per_page' => 1,
				'post_status'    => ['publish', 'draft', 'pending', 'future', 'private'],
				'fields'         => 'ids',
			]);
			if (!empty($existing)) {
				wp_redirect(admin_url('post.php?post=' . $existing[0] . '&action=edit'));
				exit;
			}
		}
	}
	return $classes;
}, 10, 2);

// Also block the "Add New" button in the admin menu and list table
add_action('admin_menu', function () {
	global $submenu;
	if (isset($submenu['edit.php?post_type=store-info'])) {
		$count = wp_count_posts('store-info')->publish + wp_count_posts('store-info')->draft + wp_count_posts('store-info')->pending + wp_count_posts('store-info')->future + wp_count_posts('store-info')->private;
		if ($count >= 1) {
			// Remove "Add New" from submenu
			foreach ($submenu['edit.php?post_type=store-info'] as $k => $item) {
				if (in_array('post-new.php?post_type=store-info', $item)) {
					unset($submenu['edit.php?post_type=store-info'][$k]);
				}
			}
		}
	}
});

// Remove "Add New" button from the list table and edit post page
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'store-info') {
		$count = wp_count_posts('store-info')->publish + wp_count_posts('store-info')->draft + wp_count_posts('store-info')->pending + wp_count_posts('store-info')->future + wp_count_posts('store-info')->private;
		if ($count >= 1) {
			// Remove "Add New" button from list table and edit post page
			echo '<style>.page-title-action, .wrap .page-title-action { display: none !important; }</style>';
		}
	}
});

// 2. Remove the "Trash" action for store-info posts
add_filter('post_row_actions', function ($actions, $post) {
	if ($post->post_type === 'store-info') {
		unset($actions['trash']);
	}
	return $actions;
}, 10, 2);

// Remove the "Move to Trash" button on the edit screen
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'store-info' && $screen->base === 'post') {
		echo '<style>#delete-action { display: none !important; }</style>';
	}
});

// Prevent trashing via direct request
add_filter('user_has_cap', function ($allcaps, $caps, $args, $user) {
	// $args[0] is the capability being checked
	// $args[2] is the post ID
	if (isset($args[0], $args[2]) && $args[0] === 'delete_post') {
		$post = get_post($args[2]);
		if ($post && $post->post_type === 'store-info') {
			$allcaps['delete_post'] = false;
		}
	}
	return $allcaps;
}, 10, 4);
