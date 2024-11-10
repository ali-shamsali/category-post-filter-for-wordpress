<?php
/*
Plugin Name: Category Post Filter
Description: نمایش دسته‌بندی‌ها و زیر دسته‌ها همراه با فیلتر کردن پست‌ها براساس انتخاب چندین زیر دسته.
Version: 1.0
Author: ali shamsali
*/

function cpf_enqueue_scripts() {
    wp_enqueue_style('cpf-style', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('cpf-script', plugins_url('/js/script.js', __FILE__), array('jquery'), null, true);

    wp_localize_script('cpf-script', 'cpf_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'cpf_enqueue_scripts');

// شورت‌کد دسته‌بندی‌ها و زیر دسته‌ها
function cpf_display_categories() {
    $categories = get_categories(array(
        'parent'       => 0,
        'hide_empty'   => false,
        'orderby'      => 'name',
        'order'        => 'ASC',
    ));

    echo '<div class="custom-container">';
    echo '<div class="custom-sidebar">';
    echo '<ul class="custom-category-list">';
    
    foreach ($categories as $category) {
        echo '<li class="custom-category-item">';
        echo '<strong>' . esc_html($category->name) . '</strong>';

        $subcategories = get_categories(array(
            'parent'       => $category->term_id,
            'hide_empty'   => false,
        ));
        
        if (!empty($subcategories)) {
            echo '<ul class="custom-subcategory-list">';
            foreach ($subcategories as $subcategory) {
                echo '<li>';
                echo '<label><input type="checkbox" class="custom-subcategory" value="' . esc_attr($subcategory->term_id) . '"> ' . esc_html($subcategory->name) . '</label>';
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '</li>';
    }
    echo '</ul>';
    echo '</div>'; 
    echo '<div class="custom-posts" id="custom-posts"></div>'; 
    echo '</div>';
}
add_shortcode('category_post_filter', 'cpf_display_categories');

// تابع AJAX برای نمایش پست‌ها
function cpf_fetch_posts() {
    $subcategory_ids = isset($_POST['subcategory_ids']) ? array_map('intval', $_POST['subcategory_ids']) : array();

    $args = array(
        'posts_per_page' => -1
    );
    if (!empty($subcategory_ids)) {
        $args['category__in'] = $subcategory_ids;
    }

    $posts = get_posts($args);

    if (!empty($posts)) {
        echo '<div class="custom-posts-grid">';
        foreach ($posts as $post) {
            $post_link = get_permalink($post->ID);
            
            echo '<div class="custom-post-card">';
            echo '<a href="' . esc_url($post_link) . '" style="text-decoration:none; color:inherit;">';

            if (has_post_thumbnail($post->ID)) {
                $thumbnail = get_the_post_thumbnail($post->ID, 'medium', array('class' => 'custom-post-thumbnail'));
                echo $thumbnail;
            }

            echo '<div class="custom-post-content">';
            echo '<h4>' . esc_html($post->post_title) . '</h4>';
            $post_content = apply_filters('the_content', $post->post_content);
            $post_content = wp_trim_words(wp_strip_all_tags($post_content), 20);
            echo '<p>' . esc_html($post_content) . '</p>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>پستی یافت نشد.</p>';
    }
    wp_die();
}
add_action('wp_ajax_cpf_fetch_posts', 'cpf_fetch_posts');
add_action('wp_ajax_nopriv_cpf_fetch_posts', 'cpf_fetch_posts');
