<?php
// This file is generated. Do not modify it manually.
return array(
	'thepi-events-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'thepi-core/events-grid',
		'version' => '0.1.0',
		'title' => 'Thepi Event Grid',
		'category' => 'widgets',
		'icon' => 'calendar-alt',
		'description' => 'Example block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'attributes' => array(
			'gapX' => array(
				'type' => 'number',
				'default' => 19
			),
			'gapY' => array(
				'type' => 'number',
				'default' => 50
			),
			'initialAmount' => array(
				'type' => 'number',
				'default' => 3
			),
			'showMore' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showMoreAmountEachTime' => array(
				'type' => 'number',
				'default' => 3
			)
		),
		'supports' => array(
			'html' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'thepi-components',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js'
	),
	'thepi-gallery-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'thepi-core/gallery-grid',
		'version' => '0.1.0',
		'title' => 'Thepi Gallery Grid',
		'category' => 'widgets',
		'icon' => 'format-gallery',
		'description' => 'A customizable gallery grid block.',
		'example' => array(
			
		),
		'attributes' => array(
			'columns' => array(
				'type' => 'number',
				'default' => 9
			),
			'columnGap' => array(
				'type' => 'string',
				'default' => '17px'
			),
			'rowGap' => array(
				'type' => 'string',
				'default' => '17px'
			),
			'rowHeight' => array(
				'type' => 'string',
				'default' => '400px'
			),
			'initialAmount' => array(
				'type' => 'number',
				'default' => 7
			),
			'showMore' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showMoreAmountEachTime' => array(
				'type' => 'number',
				'default' => 3
			)
		),
		'supports' => array(
			'html' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'thepi-gallery-grid',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js'
	),
	'thepi-menu-carousel' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'thepi-core/menu-carousel',
		'version' => '0.1.0',
		'title' => 'Thepi Menu Carousel',
		'category' => 'widgets',
		'icon' => 'images-alt2',
		'description' => 'Example block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'attributes' => array(
			'images' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'object',
					'properties' => array(
						'url' => array(
							'type' => 'string'
						),
						'name' => array(
							'type' => 'string'
						),
						'alt' => array(
							'type' => 'string'
						)
					)
				),
				'default' => array(
					
				)
			),
			'aspectRatio' => array(
				'type' => 'string',
				'default' => '12/13'
			)
		),
		'supports' => array(
			'html' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'thepi-components',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js'
	),
	'thepi-press-featuring-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'thepi-core/press-featuring-grid',
		'version' => '0.1.0',
		'title' => 'Thepi Press Featuring Grid',
		'category' => 'widgets',
		'icon' => 'grid-view',
		'description' => 'Example block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'thepi-components',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'gapX' => array(
				'type' => 'number',
				'default' => 19
			),
			'gapY' => array(
				'type' => 'number',
				'default' => 50
			),
			'initialAmount' => array(
				'type' => 'number',
				'default' => 3
			),
			'showMore' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showMoreAmountEachTime' => array(
				'type' => 'number',
				'default' => 3
			)
		)
	)
);
