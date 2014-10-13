<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
<?php the_post(); ?>
<!--the following sets up two colums formated by css. -->


			
			<?php echo '<div class="section group">'; ?>
	<?php echo '<div class="col span_1_of_2S">'; ?>


 <!-- Get the single post information for the book. -->
 
				<?php if ( has_post_thumbnail() ) : ?>
    <div class="thumbnail"><?php the_post_thumbnail('large'); ?></div>
<?php  elseif( get_post_meta($post->ID, "thumbnail", true) ): ?>
    <div class="thumbnail">
       <img src="<?php echo get_post_meta($post->ID, "thumbnail", true); ?>" width="100" height="100" alt="<?php the_title(); ?>" /></div>
<?php  else: ?>
<?php endif; ?>
<?php echo '</div>'; ?>
<?php echo '<div class="col span_2_of_2S">'; ?>
<?php echo '<div class ="book-title">',get_the_title().'</div>'; ?>
<?php echo '<span class="book-author"> Author: '.get_post_meta($post->ID, "author", true).' | </span>'; ?>
  <?php  echo '<span class="book-author"> Publisher: '.get_post_meta($post->ID, "publisher", true).' </span>'; ?>
<?php echo the_content(); ?>
<?php echo '</div>'; ?>
<?php	echo '</div>';	?>



<!-- get the previous and next book postings and display -->
 
				<nav class="nav-single">
					<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' ); ?></span>
					<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>' ); ?></span>
				</nav><!-- .nav-single -->

				<?php comments_template( '', true ); ?>

		

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>