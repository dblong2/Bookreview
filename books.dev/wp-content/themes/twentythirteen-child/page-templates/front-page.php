<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Thirteen consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>


<div class="fp_title" ><i class="fa fa-star fa-3x"></i>Our FEATURED book reviews</div>
 <?php

 /**
  * set array to pull featured books for front page
  */
 
 $args = array(
		'post_type' => 'books', // enter your custom post type
		'orderby' => 'Title',
		'order' => 'ASC',
		'posts_per_page'=> '-1',  // overrides posts per page in theme settings
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'featured',
				'value' => 'yes',
				'compare' => '='
				)
			),
				
	);

/**
 * query based on args supplied
 */

$my_query = new WP_Query($args); 
while ($my_query->have_posts()) : $my_query->the_post(); 

	echo'<div class="section group">';
	echo '<div class="col span_1_of_2">';
?>
<?php if ( has_post_thumbnail() ) : ?>
    <div class="thumbnail"><?php the_post_thumbnail('thumbnail'); ?></div>
<?php  elseif( get_post_meta($post->ID, "thumbnail", true) ): ?>
    <div class="thumbnail">
       <img src="<?php echo get_post_meta($post->ID, "thumbnail", true); ?>" width="100" height="100" alt="<?php the_title(); ?>" /></div>
 <?php  else: ?>
<?php endif; ?>

<?php
	echo '</div>';
	echo '<div class="col span_2_of_2">';
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
<?php get_sidebar( 'home_right_1' ); ?>
 
<?php get_footer(); ?>