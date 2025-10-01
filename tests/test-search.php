<?php

namespace GFRecipes\Tests;

use WP_UnitTestCase;
use GFRecipes\Search;

class Test_Search extends WP_UnitTestCase {
    
    private $recipe_id;
    
    public function setUp(): void {
        parent::setUp();
        
        $this->recipe_id = wp_insert_post([
            'post_title'  => 'Chocolate Cake',
            'post_type'   => 'recipe',
            'post_status' => 'publish',
        ]);
        
        update_post_meta($this->recipe_id, 'ingredients', ['chocolate', 'flour', 'sugar']);
        update_post_meta($this->recipe_id, 'cook_time_minutes', 45);
        update_post_meta($this->recipe_id, 'difficulty', 'medium');
        update_post_meta($this->recipe_id, 'servings', 8);
        
        wp_set_object_terms($this->recipe_id, ['dessert'], 'recipe_tag');
    }
    
    public function test_search_by_keyword() {
        $results = Search::search_recipes(['keyword' => 'Chocolate']);
        
        $this->assertNotEmpty($results);
        $this->assertEquals('Chocolate Cake', $results[0]['title']);
    }
    
    public function test_search_by_difficulty() {
        $results = Search::search_recipes(['difficulty' => 'medium']);
        
        $this->assertNotEmpty($results);
    }
    
    public function test_search_by_cook_time() {
        $results = Search::search_recipes(['cook_time_max' => 60]);
        
        $this->assertNotEmpty($results);
    }
    
    public function test_search_by_ingredient() {
        $results = Search::search_recipes(['ingredients_include' => ['chocolate']]);
        
        $this->assertNotEmpty($results);
    }
    
    public function test_relevance_scoring() {
        $results = Search::search_recipes(['keyword' => 'Chocolate']);
        
        $this->assertArrayHasKey('score', $results[0]);
        $this->assertGreaterThan(0, $results[0]['score']);
    }
    
    public function tearDown(): void {
        wp_delete_post($this->recipe_id, true);
        parent::tearDown();
    }
}
