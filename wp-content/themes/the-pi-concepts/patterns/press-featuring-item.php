<?php
/**
 * Title: Press Featuring Item
 * Slug: the-pi-concepts/press-featuring-item
 * Categories: featured
 * Description: Displays a grid of the latest 3 "Press Featuring" custom posts, each with a featured image and title, linking to the post. Useful for showcasing recent press mentions or features.
 * Keywords: press, featuring, grid, showcase, news
 */
?>

<?php
$press_featuring = new WP_Query([
	'post_type' => 'press_featuring',
	'posts_per_page' => 3,
	'orderby' => 'date',
	'order' => 'DESC',
]);

if ($press_featuring->have_posts()) : ?>
	<div class="press-featuring-grid">
		<?php while ($press_featuring->have_posts()) : $press_featuring->the_post();
			$subtitle = get_post_meta(get_the_ID(), 'press_featuring_subtitle', true);
			$link     = get_post_meta(get_the_ID(), 'press_featuring_link', true);
		?>
			<div class="press-featuring-item">
				<a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener">
					<?php the_post_thumbnail('medium'); ?>
					<h3><?php the_title(); ?></h3>
					<?php if ($subtitle): ?>
						<p class="subtitle"><?php echo esc_html($subtitle); ?></p>
					<?php endif; ?>
				</a>
			</div>
		<?php endwhile;
		wp_reset_postdata(); ?>
	</div>
<?php endif; ?>
