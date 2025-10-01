<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Suggest {
    
    public static function suggest_recipes($ingredients, $strict = false) {
        if (empty($ingredients) || !is_array($ingredients)) {
            return [
                'can_cook' => [],
                'almost'   => [],
            ];
        }
        
        $normalized_inputs = array_map([Security::class, 'normalize_ingredient'], $ingredients);
        $normalized_inputs = array_filter($normalized_inputs);
        
        if (empty($normalized_inputs)) {
            return [
                'can_cook' => [],
                'almost'   => [],
            ];
        }
        
        $query = new \WP_Query([
            'post_type'      => 'recipe',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);
        
        $can_cook = [];
        $almost = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $recipe_ingredients = get_post_meta($post_id, 'ingredients', true) ?: [];
                
                if (empty($recipe_ingredients)) {
                    continue;
                }
                
                $result = self::calculate_match_score($normalized_inputs, $recipe_ingredients);
                
                if ($result['missing_count'] === 0) {
                    $recipe = Search::format_recipe($post_id);
                    $recipe['match_score'] = $result['score'];
                    $recipe['matched_count'] = $result['matched_count'];
                    $recipe['missing_ingredients'] = [];
                    $can_cook[] = $recipe;
                } elseif (!$strict && $result['matched_count'] > 0) {
                    $recipe = Search::format_recipe($post_id);
                    $recipe['match_score'] = $result['score'];
                    $recipe['matched_count'] = $result['matched_count'];
                    $recipe['missing_ingredients'] = $result['missing_ingredients'];
                    $almost[] = $recipe;
                }
            }
            wp_reset_postdata();
        }
        
        usort($can_cook, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
        
        usort($almost, function($a, $b) {
            if ($b['match_score'] !== $a['match_score']) {
                return $b['match_score'] <=> $a['match_score'];
            }
            return count($a['missing_ingredients']) <=> count($b['missing_ingredients']);
        });
        
        return [
            'can_cook' => $can_cook,
            'almost'   => $almost,
        ];
    }
    
    private static function calculate_match_score($user_ingredients, $recipe_ingredients) {
        $normalized_recipe = array_map([Security::class, 'normalize_ingredient'], $recipe_ingredients);
        
        $matched_count = 0;
        $missing_ingredients = [];
        
        foreach ($normalized_recipe as $index => $recipe_ing) {
            $matched = false;
            
            foreach ($user_ingredients as $user_ing) {
                if (strpos($recipe_ing, $user_ing) !== false || strpos($user_ing, $recipe_ing) !== false) {
                    $matched = true;
                    break;
                }
            }
            
            if ($matched) {
                $matched_count++;
            } else {
                $missing_ingredients[] = $recipe_ingredients[$index];
            }
        }
        
        $missing_count = count($missing_ingredients);
        $score = (2 * $matched_count) - $missing_count;
        
        return [
            'score'               => $score,
            'matched_count'       => $matched_count,
            'missing_count'       => $missing_count,
            'missing_ingredients' => $missing_ingredients,
        ];
    }
}
