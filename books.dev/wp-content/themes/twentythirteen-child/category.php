<?php
/**
 * The template for displaying Category pages
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title"><?php printf( __( 'Category Archives: %s', 'twentythirteen' ), single_cat_title( '', false ) ); ?></h1>

				<?php if ( category_description() ) : // Show an optional category description ?>
				<div class="archive-meta"><?php echo category_description(); ?></div>
				<?php endif; ?>
			</header><!-- .archive-header -->

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
			<?php echo'<div class="section group">'; ?>
	<?php echo '<div class="col span_1_of_2">'; ?>
	<?php if ( has_post_thumbnail() ) : ?>
    <div class="thumbnail"><?php the_post_thumbnail('thumbnail'); ?></div>
<?php  elseif( get_post_meta($post->ID, "thumbnail", true) ): ?>
    <div class="thumbnail">
       <img src="<?php echo get_post_meta($post->ID, "thumbnail", true); ?>" width="100" height="100" alt="<?php the_title(); ?>" /></div>
 <?php  else: ?>
<?php endif; ?>

<?php
	echo '</div>';
	echo '<div class="col span_2_of_2 span_archive">';
	//echo ' '.get_the_excerpt(). ' ';
	echo '<div class ="book-title">','<a href="'.get_permalink().'">'.get_the_title().'</a> ','</div>';
	echo '<span class="book-author"> Author: '.get_post_meta($post->ID, "author", true).' | </span>';
    echo '<span class="book-author"> Publisher: '.get_post_meta($post->ID, "publisher", true).' </span>';

	echo the_excerpt('Read more...');
	echo the_author_post_rating( $post->ID );
	echo '</div>';
	echo '</div>';	
endwhile; 
	

wp_reset_query();
?>
				

			<?php twentythirteen_paging_nav(); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>