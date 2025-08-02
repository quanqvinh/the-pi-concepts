<?php

// Enable SVG upload support
add_filter('upload_mimes', 'image_optimizer_enable_svg_upload');
function image_optimizer_enable_svg_upload($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}

// Allow SVG display in media library
add_filter('wp_check_filetype_and_ext', 'image_optimizer_fix_svg_mime_type', 10, 4);
function image_optimizer_fix_svg_mime_type($data, $file, $filename, $mimes)
{
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ('svg' === strtolower($ext)) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}

// Optional: Sanitize SVG uploads (basic, for extra security use a library)
add_filter('wp_handle_upload_prefilter', 'image_optimizer_svg_sanitize_check');
function image_optimizer_svg_sanitize_check($file)
{
	$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
	if ('svg' === strtolower($ext)) {
		// Optionally, you can add more robust SVG sanitization here
		// For now, just allow
	}
	return $file;
}

add_action('wp_handle_upload', 'image_optimizer_init');

function image_optimizer_init($upload)
{
	$file_path = $upload['file'];
	$ext = pathinfo($file_path, PATHINFO_EXTENSION);

	// If SVG, skip optimization but allow upload
	if (strtolower($ext) === 'svg') {
		$upload['type'] = 'image/svg+xml';
		return $upload;
	}

	$image_info = getimagesize($file_path);

	if (!$image_info) {
		error_log('[Image Optimizer] Not a valid image: ' . $file_path);
		return $upload;
	}

	// Only process JPEG and PNG
	$mime = $image_info['mime'];
	if (!in_array($mime, ['image/jpeg', 'image/png'])) {
		return $upload;
	}

	// Convert to WebP directly from the original file
	$webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_path);

	// Load image using GD based on type
	if ($mime === 'image/jpeg') {
		$image = @imagecreatefromjpeg($file_path);
	} elseif ($mime === 'image/png') {
		$image = @imagecreatefrompng($file_path);
	} else {
		$image = false;
	}

	if ($image !== false) {
		$webp_result = @imagewebp($image, $webp_path, 80);
		if (!$webp_result) {
			error_log('[Image Optimizer] imagewebp failed for: ' . $webp_path);
		}
		imagedestroy($image);

		// Remove original file
		if (file_exists($file_path) && !@unlink($file_path)) {
			error_log('[Image Optimizer] Failed to remove original file: ' . $file_path);
		}

		// Update $upload['file'] and $upload['url'] to .webp
		$upload['file'] = $webp_path;
		$upload['url'] = preg_replace('/\.(jpe?g|png)$/i', '.webp', $upload['url']);
		$upload['type'] = 'image/webp';

		// No need to rename .webp to original file name, just use .webp as the new file name
	} else {
		error_log('[Image Optimizer] Failed to create image resource from: ' . $file_path);
	}

	return $upload;
}
