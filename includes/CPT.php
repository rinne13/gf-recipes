<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class CPT {
    
    public static function register() {
        self::register_post_type();
        self::register_taxonomies();
        self::add_admin_hooks();
    }
    
    private static function register_post_type() {
        $labels = [
            'name'                  => _x('Recipes', 'Post type general name', 'gf-recipes'),
            'singular_name'         => _x('Recipe', 'Post type singular name', 'gf-recipes'),
            'menu_name'             => _x('Recipes', 'Admin Menu text', 'gf-recipes'),
            'name_admin_bar'        => _x('Recipe', 'Add New on Toolbar', 'gf-recipes'),
            'add_new'               => __('Add New', 'gf-recipes'),
            'add_new_item'          => __('Add New Recipe', 'gf-recipes'),
            'new_item'              => __('New Recipe', 'gf-recipes'),
            'edit_item'             => __('Edit Recipe', 'gf-recipes'),
            'view_item'             => __('View Recipe', 'gf-recipes'),
            'all_items'             => __('All Recipes', 'gf-recipes'),
            'search_items'          => __('Search Recipes', 'gf-recipes'),
            'not_found'             => __('No recipes found.', 'gf-recipes'),
            'not_found_in_trash'    => __('No recipes found in Trash.', 'gf-recipes'),
            'archives'              => _x('Recipe Archives', 'The post type archive label used in nav menus.', 'gf-recipes'),
        ];
        
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'recipes'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-carrot',
            'show_in_rest'       => true,
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
        ];
        
        register_post_type('recipe', $args);
    }
    
    private static function register_taxonomies() {
        register_taxonomy('recipe_tag', 'recipe', [
            'label'        => __('Recipe Tags', 'gf-recipes'),
            'rewrite'      => ['slug' => 'recipe-tag'],
            'hierarchical' => false,
            'show_in_rest' => true,
        ]);
        
        register_taxonomy('diet_type', 'recipe', [
            'label'        => __('Diet Types', 'gf-recipes'),
            'rewrite'      => ['slug' => 'diet-type'],
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);
        
        if (!term_exists('gluten-free', 'recipe_tag')) {
            wp_insert_term('gluten-free', 'recipe_tag');
        }
        
        $diet_types = ['gluten-free', 'dairy-free', 'vegan', 'vegetarian'];
        foreach ($diet_types as $diet) {
            if (!term_exists($diet, 'diet_type')) {
                wp_insert_term($diet, 'diet_type', ['parent' => 0]);
            }
        }
    }
    
    private static function add_admin_hooks() {
        add_filter('manage_recipe_posts_columns', [__CLASS__, 'add_admin_columns']);
        add_action('manage_recipe_posts_custom_column', [__CLASS__, 'render_admin_columns'], 10, 2);
    }
    
    public static function add_admin_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['cook_time'] = __('Cook Time', 'gf-recipes');
                $new_columns['difficulty'] = __('Difficulty', 'gf-recipes');
                $new_columns['servings'] = __('Servings', 'gf-recipes');
            }
        }
        
        return $new_columns;
    }
    
    public static function render_admin_columns($column, $post_id) {
        switch ($column) {
            case 'cook_time':
                $time = get_post_meta($post_id, 'cook_time_minutes', true);
                echo $time ? esc_html($time . ' min') : '—';
                break;
                
            case 'difficulty':
                $difficulty = get_post_meta($post_id, 'difficulty', true);
                echo $difficulty ? esc_html(ucfirst($difficulty)) : '—';
                break;
                
            case 'servings':
                $servings = get_post_meta($post_id, 'servings', true);
                echo $servings ? esc_html($servings) : '—';
                break;
        }
    }
}
