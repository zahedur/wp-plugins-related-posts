<?php
/*
 * Plugin Name:       Related Posts
 * Plugin URI:        https://zahedur.com
 * Description:       A WordPress plugin that displays a maximum of 5 related posts at the end of each post.
 * Version:           1.0
 * Author:            Zahedur Rahman
 * Author URI:        https://zahedur.com
 * Text Domain:       related-posts
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Zr_Related_Post
 * Main class for the Related Posts plugin.
 */
class Zr_Related_Post
{

    /**
     * Constructor for the Zr_Related_Post class.
     * Hooks into the 'init' action to initialize the class.
     */
    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialization function.
     * Hooks for related posts display and script enqueue.
     */
    public function init()
    {
        add_action( 'the_content', [$this, 'related_posts' ]);
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
    }

    /**
     * Display related posts at the end of the main content.
     *
     * @param string $content The post content.
     * @return string The modified post content with related posts.
     */
    public function related_posts(string $content): string
    {
        //Get current post id
        $post_id = get_the_ID();

        // Get current post category or categories
        $post_categories = get_the_category($post_id);

        //No related posts found
        if (!$post_categories) {
            $post_content = '<div class="' . esc_attr('zr-related-post-box') . '">';
            $post_content .= '<div class="' . esc_attr('zr-related-post-head') . '"> <h2>' . esc_html__('Related Posts', 'related-posts') . '</h2> </div>';
            $post_content .= '<div class="' . esc_attr('zr-related-posts-not-found') . '">';
            $post_content .= '<p>' . esc_html__('No related posts found', 'related-posts') . '</p>';
            $post_content .= '</div>';
            $post_content .= '</div>';
            $content .= $post_content;
            return $content;
        }

        // Pluck categories ids
        $category_ids = [];
        foreach ( $post_categories as $post_category ) {
            $category_ids[] = $post_category->term_id;
        }

        //Query arguments
        $args = [
            'post_not_in'       => [ get_the_ID() ],
            'posts_per_page'    => 5,
            'orderby'           => 'rand',
            'post_type'         => 'post',
            'category__in'      => $category_ids,
        ];

        // Query to get "Related Posts".
        $query = new WP_Query( $args );

        // Start related posts content.
        $post_content = '<div class="' . esc_attr('zr-related-post-box') . '">';
        $post_content .= '<div class="' . esc_attr('zr-related-post-head') . '"> <h2>' . esc_html__('Related Posts', 'related-posts') . '</h2> </div>';
        if ( $query->have_posts() ) :
            $post_content .= '<div class="' . esc_attr('zr-related-posts') . '">';
                while ( $query->have_posts() ) : $query->the_post();
                    $post_content .= '<a href="'. esc_url(get_permalink()) .'" class="' . esc_attr('zr-related-post') . '">';
                    if (has_post_thumbnail()) {
                        $post_content .= '<div class="' . esc_attr('zr-related-post-thumbnail') . '"><img src="' . esc_url(get_the_post_thumbnail_url()) . '" width="80" alt="' . esc_attr(get_the_title()) . '" /></div>';
                    }
                    $post_content .= '<div class="'. esc_attr('zr-related-post-title-and-content') .'">';
                    $post_content .= '<div class="'. esc_attr('zr-related-post-title') .'">' . esc_html(get_the_title()) . '</div>';
                    $post_content .= '</div>';
                    $post_content .= '</a>';
                endwhile;
            $post_content .= '</div>';
        else :
            $post_content .= '<div class="' . esc_attr('zr-related-posts-not-found') . '">';
            $post_content .= '<p>' . esc_html__('No related posts found', 'related-posts') . '</p>';
            $post_content .= '</div>';
        endif;
        $post_content .= '</div>';
        //End related posts content.

        //Restore the global $post variable to the current post in the main query.
        wp_reset_postdata();

        //Merging the content of the related post at the end of the original post
        $content .= $post_content;

        // return the content
        return $content;
    }

    /**
     * Enqueue styles for the related posts.
     */
    public function enqueue_scripts()
    {
        //Add custom style
        wp_enqueue_style( 'zr-related-posts', plugins_url( 'assets/css/style.css', __FILE__ ) );
    }


}

// Instantiate the Zr_Related_Post class to initialize the plugin.
new Zr_Related_Post();