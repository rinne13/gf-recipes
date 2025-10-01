<?php

namespace GFRecipes\Tests;

use WP_UnitTestCase;
use GFRecipes\Suggest;

class Test_Suggest extends WP_UnitTestCase {
    
    private $recipe_id;
    
    public function setUp(): void {
        parent::setUp();
        
        $this->recipe_id = wp_insert_post([
            'post_title'  => 'Test Pasta',
            'post_type'   => 'recipe',
            'post_status' => 'publish',
        ]);
        
        update_post_meta($this->recipe_id, 'ingredients', [
            'pasta',
            'tomato sauce',
            'cheese',
            'basil',
        ]);
    }
    
    public function test_suggest_with_all_ingredients() {
        $results = Suggest::suggest_recipes(['pasta', 'tomato sauce', 'cheese', 'basil']);
        
        $this->assertArrayHasKey('can_cook', $results);
        $this->assertArrayHasKey('almost', $results);
        $this->assertNotEmpty($results['can_cook']);
    }
    
    public function test_suggest_with_partial_ingredients() {
        $results = Suggest::suggest_recipes(['pasta', 'tomato sauce']);
        
        $this->assertArrayHasKey('can_cook', $results);
        $this->assertArrayHasKey('almost', $results);
        $this->assertNotEmpty($results['almost']);
    }
    
    public function test_strict_mode_excludes_partial_matches() {
        $results = Suggest::suggest_recipes(['pasta', 'tomato sauce'], true);
        
        $this->assertEmpty($results['can_cook']);
    }
    
    public function test_empty_ingredients_returns_empty_results() {
        $results = Suggest::suggest_recipes([]);
        
        $this->assertEmpty($results['can_cook']);
        $this->assertEmpty($results['almost']);
    }
    
    public function tearDown(): void {
        wp_delete_post($this->recipe_id, true);
        parent::tearDown();
    }
}
