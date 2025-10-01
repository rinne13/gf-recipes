<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class REST {
    
    public static function register_routes() {
        register_rest_route('gf/v1', '/recipes', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_recipes'],
            'permission_callback' => '__return_true',
        ]);
        
        register_rest_route('gf/v1', '/recipes', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'create_recipe'],
            'permission_callback' => [__CLASS__, 'create_permission_check'],
        ]);
        
        register_rest_route('gf/v1', '/suggest', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'suggest_recipes'],
            'permission_callback' => '__return_true',
        ]);
    }
    
    public static function get_recipes($request) {
        $params = [
            'keyword'              => $request->get_param('q') ?: '',
            'difficulty'           => $request->get_param('difficulty') ?: '',
            'cook_time_max'        => $request->get_param('cook_time_max') ?: 0,
            'diet_type'            => $request->get_param('diet_type') ?: '',
            'ingredients_include'  => $request->get_param('ingredients_includes') ?: [],
            'posts_per_page'       => $request->get_param('per_page') ?: 20,
        ];
        
        $recipes = Search::search_recipes($params);
        
        return new \WP_REST_Response([
            'success' => true,
            'count'   => count($recipes),
            'recipes' => $recipes,
        ], 200);
    }
    
    public static function suggest_recipes($request) {
        $ingredients = $request->get_param('ingredients');
        $strict = $request->get_param('strict') === '1' || $request->get_param('strict') === 1;
        
        if (empty($ingredients)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Please provide ingredients.', 'gf-recipes'),
            ], 400);
        }
        
        if (is_string($ingredients)) {
            $ingredients = array_map('trim', explode(',', $ingredients));
        }
        
        $suggestions = Suggest::suggest_recipes($ingredients, $strict);
        
        return new \WP_REST_Response([
            'success'  => true,
            'input'    => $ingredients,
            'strict'   => $strict,
            'can_cook' => $suggestions['can_cook'],
            'almost'   => $suggestions['almost'],
        ], 200);
    }
    
    public static function create_recipe($request) {
        $title = sanitize_text_field($request->get_param('title'));
        $content = wp_kses_post($request->get_param('content'));
        
        if (empty($title)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Title is required.', 'gf-recipes'),
            ], 400);
        }
        
        $post_data = [
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'recipe',
            'post_status'  => 'publish',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $post_id->get_error_message(),
            ], 500);
        }
        
        if ($request->get_param('ingredients')) {
            $ingredients = Security::sanitize_ingredients($request->get_param('ingredients'));
            update_post_meta($post_id, 'ingredients', $ingredients);
        }
        
        if ($request->get_param('steps')) {
            $steps = Security::sanitize_steps($request->get_param('steps'));
            update_post_meta($post_id, 'steps', $steps);
        }
        
        if ($request->get_param('cook_time_minutes')) {
            update_post_meta($post_id, 'cook_time_minutes', absint($request->get_param('cook_time_minutes')));
        }
        
        if ($request->get_param('difficulty')) {
            $difficulty = sanitize_text_field($request->get_param('difficulty'));
            if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
                update_post_meta($post_id, 'difficulty', $difficulty);
            }
        }
        
        if ($request->get_param('servings')) {
            update_post_meta($post_id, 'servings', absint($request->get_param('servings')));
        }
        
        if ($request->get_param('allergens')) {
            $allergens = Security::sanitize_allergens($request->get_param('allergens'));
            update_post_meta($post_id, 'allergens', $allergens);
        }
        
        if ($request->get_param('recipe_tags')) {
            wp_set_object_terms($post_id, $request->get_param('recipe_tags'), 'recipe_tag');
        }
        
        if ($request->get_param('diet_types')) {
            wp_set_object_terms($post_id, $request->get_param('diet_types'), 'diet_type');
        }
        
        $recipe = Search::format_recipe($post_id);
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Recipe created successfully.', 'gf-recipes'),
            'recipe'  => $recipe,
        ], 201);
    }
    
    public static function create_permission_check() {
        return Security::can_edit_recipes();
    }
}
