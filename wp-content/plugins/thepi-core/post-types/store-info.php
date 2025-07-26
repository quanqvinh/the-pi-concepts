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

// Add meta boxes for Phone, Email, and Address fields
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
?>
	<p>
		<label for="store_info_phone"><strong>Phone</strong></label><br>
		<input type="text" id="store_info_phone" name="store_info_phone" value="<?php echo esc_attr($phone); ?>" style="width:100%;" required>
	</p>
	<p>
		<label for="store_info_email"><strong>Email</strong></label><br>
		<input type="email" id="store_info_email" name="store_info_email" value="<?php echo esc_attr($email); ?>" style="width:100%;" required>
	</p>
	<p>
		<label for="store_info_address"><strong>Address</strong></label><br>
		<input type="text" id="store_info_address" name="store_info_address" value="<?php echo esc_attr($address); ?>" style="width:100%;" required>
	</p>
	<p>
		<?php
		// Get existing socials from post meta
		$socials_json = get_post_meta($post->ID, 'socials', true);
		$socials = [];
		if (!empty($socials_json)) {
			$socials = json_decode($socials_json, true);
		}
		if (!is_array($socials)) {
			$socials = array();
		}
		?>
	<div id="store-info-socials-wrapper">
		<table style="width:100%; border-collapse:collapse; margin-bottom: 10px;" id="store-info-socials-table">
			<thead>
				<tr>
					<th>Social Name</th>
					<th>Social Icon (<a href="https://developer.wordpress.org/resource/dashicons" target="_blank" rel="noopener noreferrer">Search Dashicon key</a>)</th>
					<th>Social Link</th>
					<th>Hyper Text</th>
					<th>Visible?</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($socials)) : ?>
					<?php foreach ($socials as $idx => $social) : ?>
						<tr>
							<td>
								<input type="text" name="store_info_socials[<?php echo $idx; ?>][name]" value="<?php echo esc_attr($social['name'] ?? ''); ?>" style="width:100%;" required>
							</td>
							<td>
								<input type="text" name="store_info_socials[<?php echo $idx; ?>][icon]" value="<?php echo esc_attr($social['icon'] ?? ''); ?>" style="width:100%;" placeholder="e.g. dashicons-facebook" required>
							</td>
							<td>
								<input type="url" name="store_info_socials[<?php echo $idx; ?>][link]" value="<?php echo esc_attr($social['link'] ?? ''); ?>" style="width:100%;" required>
							</td>
							<td>
								<input type="text" name="store_info_socials[<?php echo $idx; ?>][hyper_text]" value="<?php echo esc_attr($social['hyper_text'] ?? ''); ?>" style="width:100%;">
							</td>
							<td style="text-align:center;">
								<input type="checkbox" name="store_info_socials[<?php echo $idx; ?>][visible]" value="1" <?php checked(!isset($social['visible']) || $social['visible']); ?>>
							</td>
							<td style="text-align:center;">
								<button type="button" class="button remove-social-row" title="Remove Social">&times;</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<button type="button" class="button" id="add-social-row">Add Social</button>
	</div>
	<script>
		(function($) {
			$(document).ready(function() {
				let $table = $('#store-info-socials-table tbody');
				let rowIdx = $table.find('tr').length;
				$('#add-social-row').on('click', function(e) {
					e.preventDefault();
					let newRow = `<tr>
					<td>
						<input type="text" name="store_info_socials[` + rowIdx + `][name]" style="width:100%;" required>
					</td>
					<td>
						<input type="text" name="store_info_socials[` + rowIdx + `][icon]" style="width:100%;" placeholder="e.g. dashicons-facebook" required>
					</td>
					<td>
						<input type="url" name="store_info_socials[` + rowIdx + `][link]" style="width:100%;" required>
					</td>
					<td>
						<input type="text" name="store_info_socials[` + rowIdx + `][hyper_text]" style="width:100%;">
					</td>
					<td style="text-align:center;">
						<input type="checkbox" name="store_info_socials[` + rowIdx + `][visible]" value="1" checked>
					</td>
					<td style="text-align:center;">
						<button type="button" class="button remove-social-row" title="Remove Social">&times;</button>
					</td>
				</tr>`;
					$table.append(newRow);
					rowIdx++;
				});
				$table.on('click', '.remove-social-row', function() {
					$(this).closest('tr').remove();
				});
			});
		})(jQuery);
	</script>
	<style>
		#store-info-socials-table th,
		#store-info-socials-table td {
			border: 1px solid #ddd;
			padding: 6px;
		}

		#store-info-socials-table th {
			background: #f9f9f9;
			text-align: left;
		}
	</style>
	</p>
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
	// Save Socials as JSON (include all input data for each social)
	if (isset($_POST['store_info_socials']) && is_array($_POST['store_info_socials'])) {
		$socials = [];
		foreach ($_POST['store_info_socials'] as $row) {
			$clean_row = [];
			foreach ($row as $key => $value) {
				// Sanitize each field appropriately
				if (is_array($value)) {
					// If somehow a value is an array, skip it
					continue;
				}
				if (stripos($key, 'url') !== false) {
					$clean_row[$key] = esc_url_raw($value);
				} elseif (stripos($key, 'email') !== false) {
					$clean_row[$key] = sanitize_email($value);
				} else {
					$clean_row[$key] = sanitize_text_field($value);
				}
			}
			// Only add if at least one field is not empty
			$has_data = false;
			foreach ($clean_row as $v) {
				if (!empty($v)) {
					$has_data = true;
					break;
				}
			}
			if ($has_data) {
				$socials[] = $clean_row;
			}
		}
		update_post_meta($post_id, 'socials', wp_json_encode($socials));
	} else {
		// If no socials, delete the meta
		delete_post_meta($post_id, 'socials');
	}
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

// Remove "Add New" button from the list table
add_action('admin_head', function () {
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'store-info' && $screen->base === 'edit') {
		$count = wp_count_posts('store-info')->publish + wp_count_posts('store-info')->draft + wp_count_posts('store-info')->pending + wp_count_posts('store-info')->future + wp_count_posts('store-info')->private;
		if ($count >= 1) {
			echo '<style>.page-title-action { display: none !important; }</style>';
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
