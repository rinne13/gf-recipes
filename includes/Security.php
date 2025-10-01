<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Security {
    
    public static function sanitize_ingredients($ingredients) {
        if (!is_array($ingredients)) {
            return [];
        }
        
        return array_map(function($ingredient) {
            return sanitize_text_field($ingredient);
        }, array_filter($ingredients));
    }
    
    public static function sanitize_steps($steps) {
        if (!is_array($steps)) {
            return [];
        }
        
        return array_map(function($step) {
            return wp_kses_post($step);
        }, array_filter($steps));
    }
    
    public static function sanitize_allergens($allergens) {
        if (!is_array($allergens)) {
            return [];
        }
        
        return array_map(function($allergen) {
            return sanitize_text_field($allergen);
        }, array_filter($allergens));
    }
    
    public static function verify_nonce($nonce, $action = 'wp_rest') {
        return wp_verify_nonce($nonce, $action);
    }
    
    public static function can_edit_recipes() {
        return current_user_can('edit_posts');
    }
    
    public static function normalize_ingredient($ingredient) {
        $ingredient = strtolower($ingredient);
        $ingredient = remove_accents($ingredient);
        $ingredient = preg_replace('/[^a-z0-9\s]/', '', $ingredient);
        $ingredient = trim($ingredient);
        
        $stemming = [
            'onions' => 'onion',
            'tomatoes' => 'tomato',
            'potatoes' => 'potato',
            'carrots' => 'carrot',
            'peppers' => 'pepper',
            'eggs' => 'egg',
            'chickens' => 'chicken',
            'cheeses' => 'cheese',
            'beans' => 'bean',
            'olives' => 'olive',
            'mushrooms' => 'mushroom',
        ];
        
        if (isset($stemming[$ingredient])) {
            $ingredient = $stemming[$ingredient];
        }
        
        return $ingredient;
    }
}
