<?php

namespace GFRecipes\Tests;

use WP_UnitTestCase;
use GFRecipes\CPT;

class Test_CPT extends WP_UnitTestCase {
    
    public function test_recipe_post_type_registered() {
        CPT::register();
        $this->assertTrue(post_type_exists('recipe'));
    }
    
    public function test_recipe_taxonomies_registered() {
        CPT::register();
        $this->assertTrue(taxonomy_exists('recipe_tag'));
        $this->assertTrue(taxonomy_exists('diet_type'));
    }
    
    public function test_default_terms_created() {
        CPT::register();
        $tag = term_exists('gluten-free', 'recipe_tag');
        $this->assertNotNull($tag);
    }
    
    public function test_create_recipe() {
        $post_id = wp_insert_post([
            'post_title'  => 'Test Recipe',
            'post_type'   => 'recipe',
            'post_status' => 'publish',
        ]);
        
        $this->assertIsInt($post_id);
        $this->assertGreaterThan(0, $post_id);
        
        $post = get_post($post_id);
        $this->assertEquals('recipe', $post->post_type);
        $this->assertEquals('Test Recipe', $post->post_title);
    }
}
