<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Search {
    
    public static function search_recipes($args = []) {
        $defaults = [
            'keyword'              => '',
            'difficulty'           => '',
            'cook_time_max'        => 0,
            'diet_type'            => '',
            'ingredients_include'  => [],
            'ingredients_exclude'  => [],
            'posts_per_page'       => 20,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = [
            'post_type'      => 'recipe',
            'post_status'    => 'publish',
            'posts_per_page' => absint($args['posts_per_page']),
        ];
        
        if (!empty($args['keyword'])) {
            $query_args['s'] = sanitize_text_field($args['keyword']);
        }
        
        $meta_query = ['relation' => 'AND'];
        
        if (!empty($args['difficulty']) && in_array($args['difficulty'], ['easy', 'medium', 'hard'])) {
            $meta_query[] = [
                'key'     => 'difficulty',
                'value'   => $args['difficulty'],
                'compare' => '=',
            ];
        }
        
        if (!empty($args['cook_time_max'])) {
            $meta_query[] = [
                'key'     => 'cook_time_minutes',
                'value'   => absint($args['cook_time_max']),
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
        }
        
        if (count($meta_query) > 1) {
            $query_args['meta_query'] = $meta_query;
        }
        
        $tax_query = ['relation' => 'AND'];
        
        if (!empty($args['diet_type'])) {
            $tax_query[] = [
                'taxonomy' => 'diet_type',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($args['diet_type']),
            ];
        }
        
        if (count($tax_query) > 1) {
            $query_args['tax_query'] = $tax_query;
        }
        
        $recipes_query = new \WP_Query($query_args);
        $recipes = [];
        
        if ($recipes_query->have_posts()) {
            while ($recipes_query->have_posts()) {
                $recipes_query->the_post();
                $post_id = get_the_ID();
                
                $recipe = self::format_recipe($post_id);
                
                $score = self::calculate_relevance_score(
                    $recipe,
                    $args['keyword'],
                    $args['ingredients_include'],
                    $args['cook_time_max']
                );
                
                $recipe['score'] = $score;
                
                $skip = false;
                
                if (!empty($args['ingredients_include'])) {
                    $has_match = false;
                    foreach ($args['ingredients_include'] as $ing) {
                        if (self::ingredient_matches($recipe['ingredients'], $ing)) {
                            $has_match = true;
                            break;
                        }
                    }
                    if (!$has_match) {
                        $skip = true;
                    }
                }
                
                if (!empty($args['ingredients_exclude'])) {
                    foreach ($args['ingredients_exclude'] as $ing) {
                        if (self::ingredient_matches($recipe['ingredients'], $ing)) {
                            $skip = true;
                            break;
                        }
                    }
                }
                
                if (!$skip) {
                    $recipes[] = $recipe;
                }
            }
            wp_reset_postdata();
        }
        
        usort($recipes, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $recipes;
    }
    
    private static function calculate_relevance_score($recipe, $keyword, $ingredients, $cook_time_max) {
        $score = 0;
        
        if (!empty($keyword)) {
            $keyword_lower = strtolower($keyword);
            
            if (stripos($recipe['title'], $keyword) !== false) {
                $score += 3;
            }
            
            foreach ($recipe['ingredients'] as $ingredient) {
                if (stripos($ingredient, $keyword) !== false) {
                    $score += 2;
                    break;
                }
            }
            
            foreach ($recipe['tags'] as $tag) {
                if (stripos($tag, $keyword) !== false) {
                    $score += 1;
                    break;
                }
            }
        }
        
        if (!empty($ingredients)) {
            foreach ($ingredients as $ing) {
                if (self::ingredient_matches($recipe['ingredients'], $ing)) {
                    $score += 2;
                }
            }
        }
        
        if ($cook_time_max > 0 && $recipe['cook_time_minutes'] > $cook_time_max) {
            $score -= 1;
        }
        
        return $score;
    }
    
    private static function ingredient_matches($recipe_ingredients, $search_ingredient) {
        $search_normalized = Security::normalize_ingredient($search_ingredient);
        
        foreach ($recipe_ingredients as $ingredient) {
            $ingredient_normalized = Security::normalize_ingredient($ingredient);
            
            if (strpos($ingredient_normalized, $search_normalized) !== false || 
                strpos($search_normalized, $ingredient_normalized) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function format_recipe($post_id) {
        $recipe = [
            'id'                => $post_id,
            'title'             => get_the_title($post_id),
            'excerpt'           => get_the_excerpt($post_id),
            'permalink'         => get_permalink($post_id),
            'thumbnail'         => get_the_post_thumbnail_url($post_id, 'medium'),
            'ingredients'       => get_post_meta($post_id, 'ingredients', true) ?: [],
            'steps'             => get_post_meta($post_id, 'steps', true) ?: [],
            'cook_time_minutes' => get_post_meta($post_id, 'cook_time_minutes', true) ?: 0,
            'difficulty'        => get_post_meta($post_id, 'difficulty', true) ?: '',
            'servings'          => get_post_meta($post_id, 'servings', true) ?: 0,
            'allergens'         => get_post_meta($post_id, 'allergens', true) ?: [],
            'tags'              => wp_get_post_terms($post_id, 'recipe_tag', ['fields' => 'names']),
            'diet_types'        => wp_get_post_terms($post_id, 'diet_type', ['fields' => 'names']),
        ];
        
        return $recipe;
    }
}
