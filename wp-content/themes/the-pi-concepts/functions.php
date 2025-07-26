<?php

add_action('wp_enqueue_scripts', 'theme_the_pi_concepts_enqueue_styles');
add_action('wp_enqueue_scripts', 'theme_the_pi_concepts_enqueue_scripts');
add_action('after_setup_theme', 'theme_the_pi_concepts_add_editor_style');


function theme_the_pi_concepts_enqueue_styles()
{
	wp_enqueue_style(
		'theme-the-pi-concepts-style',
		get_stylesheet_uri()
	);
	wp_enqueue_style(
		'theme-the-pi-concepts-header-style',
		get_parent_theme_file_uri('assets/css/header.css')
	);
	wp_enqueue_style(
		'theme-the-pi-concepts-footer-style',
		get_parent_theme_file_uri('assets/css/footer.css')
	);
	wp_enqueue_style(
		'theme-the-pi-concepts-base-style',
		get_parent_theme_file_uri('assets/css/base.css')
	);
	if (is_front_page()) {
		wp_enqueue_style(
			'theme-the-pi-concepts-home-style',
			get_parent_theme_file_uri('assets/css/frontend/home.css')
		);
	}
}

function theme_the_pi_concepts_enqueue_scripts()
{
	wp_enqueue_script(
		'theme-the-pi-concepts-marquee-script',
		get_parent_theme_file_uri('assets/js/marquee.js')
	);
}

function theme_the_pi_concepts_add_editor_style()
{
	add_editor_style('assets/css/base.css');
	add_editor_style('assets/css/editor/home.css');
}
