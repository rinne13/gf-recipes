<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Meta {
    
    public static function register() {
        add_action('init', [__CLASS__, 'register_meta_fields']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_recipe', [__CLASS__, 'save_meta'], 10, 2);
        add_action('admin_notices', [__CLASS__, 'display_admin_notices']);
    }
    
    public static function register_meta_fields() {
        register_post_meta('recipe', 'ingredients', [
            'type'              => 'array',
            'description'       => 'List of ingredients',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'sanitize_callback' => [Security::class, 'sanitize_ingredients'],
        ]);
        
        register_post_meta('recipe', 'steps', [
            'type'              => 'array',
            'description'       => 'Cooking steps',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'sanitize_callback' => [Security::class, 'sanitize_steps'],
        ]);
        
        register_post_meta('recipe', 'cook_time_minutes', [
            'type'         => 'integer',
            'description'  => 'Cooking time in minutes',
            'single'       => true,
            'show_in_rest' => true,
        ]);
        
        register_post_meta('recipe', 'difficulty', [
            'type'         => 'string',
            'description'  => 'Difficulty level',
            'single'       => true,
            'show_in_rest' => true,
        ]);
        
        register_post_meta('recipe', 'servings', [
            'type'         => 'integer',
            'description'  => 'Number of servings',
            'single'       => true,
            'show_in_rest' => true,
        ]);
        
        register_post_meta('recipe', 'allergens', [
            'type'              => 'array',
            'description'       => 'List of allergens',
            'single'            => true,
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'sanitize_callback' => [Security::class, 'sanitize_allergens'],
        ]);
    }
    
    public static function add_meta_boxes() {
        add_meta_box(
            'gf_recipe_details',
            __('Recipe Details', 'gf-recipes'),
            [__CLASS__, 'render_meta_box'],
            'recipe',
            'normal',
            'high'
        );
    }
    
    public static function render_meta_box($post) {
        wp_nonce_field('gf_recipe_meta', 'gf_recipe_meta_nonce');
        
        $ingredients = get_post_meta($post->ID, 'ingredients', true) ?: [];
        $steps = get_post_meta($post->ID, 'steps', true) ?: [];
        $cook_time = get_post_meta($post->ID, 'cook_time_minutes', true);
        $difficulty = get_post_meta($post->ID, 'difficulty', true);
        $servings = get_post_meta($post->ID, 'servings', true);
        $allergens = get_post_meta($post->ID, 'allergens', true) ?: [];
        
        ?>
        <div class="gf-recipe-meta">
            <p>
                <label for="cook_time_minutes"><?php esc_html_e('Cook Time (minutes):', 'gf-recipes'); ?></label>
                <input type="number" id="cook_time_minutes" name="cook_time_minutes" value="<?php echo esc_attr($cook_time); ?>" min="0" />
            </p>
            
            <p>
                <label for="difficulty"><?php esc_html_e('Difficulty:', 'gf-recipes'); ?></label>
                <select id="difficulty" name="difficulty">
                    <option value=""><?php esc_html_e('Select...', 'gf-recipes'); ?></option>
                    <option value="easy" <?php selected($difficulty, 'easy'); ?>><?php esc_html_e('Easy', 'gf-recipes'); ?></option>
                    <option value="medium" <?php selected($difficulty, 'medium'); ?>><?php esc_html_e('Medium', 'gf-recipes'); ?></option>
                    <option value="hard" <?php selected($difficulty, 'hard'); ?>><?php esc_html_e('Hard', 'gf-recipes'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="servings"><?php esc_html_e('Servings:', 'gf-recipes'); ?></label>
                <input type="number" id="servings" name="servings" value="<?php echo esc_attr($servings); ?>" min="1" />
            </p>
            
            <div class="gf-repeatable-field">
                <label><?php esc_html_e('Ingredients:', 'gf-recipes'); ?></label>
                <div id="ingredients-list">
                    <?php
                    if (!empty($ingredients)) {
                        foreach ($ingredients as $index => $ingredient) {
                            echo '<div class="ingredient-row">';
                            echo '<input type="text" name="ingredients[]" value="' . esc_attr($ingredient) . '" />';
                            echo '<button type="button" class="button remove-row">' . esc_html__('Remove', 'gf-recipes') . '</button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button add-ingredient"><?php esc_html_e('Add Ingredient', 'gf-recipes'); ?></button>
            </div>
            
            <div class="gf-repeatable-field">
                <label><?php esc_html_e('Steps:', 'gf-recipes'); ?></label>
                <div id="steps-list">
                    <?php
                    if (!empty($steps)) {
                        foreach ($steps as $index => $step) {
                            echo '<div class="step-row">';
                            echo '<textarea name="steps[]" rows="2">' . esc_textarea($step) . '</textarea>';
                            echo '<button type="button" class="button remove-row">' . esc_html__('Remove', 'gf-recipes') . '</button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button add-step"><?php esc_html_e('Add Step', 'gf-recipes'); ?></button>
            </div>
            
            <div class="gf-repeatable-field">
                <label><?php esc_html_e('Allergens (excluding gluten):', 'gf-recipes'); ?></label>
                <div id="allergens-list">
                    <?php
                    if (!empty($allergens)) {
                        foreach ($allergens as $index => $allergen) {
                            echo '<div class="allergen-row">';
                            echo '<input type="text" name="allergens[]" value="' . esc_attr($allergen) . '" />';
                            echo '<button type="button" class="button remove-row">' . esc_html__('Remove', 'gf-recipes') . '</button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="button add-allergen"><?php esc_html_e('Add Allergen', 'gf-recipes'); ?></button>
            </div>
        </div>
        
        <style>
            .gf-recipe-meta p { margin-bottom: 15px; }
            .gf-recipe-meta label { display: inline-block; width: 150px; font-weight: 600; }
            .gf-recipe-meta input[type="number"], .gf-recipe-meta select { width: 200px; }
            .gf-repeatable-field { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
            .gf-repeatable-field > label { display: block; margin-bottom: 10px; font-weight: 600; }
            .ingredient-row, .step-row, .allergen-row { margin-bottom: 10px; display: flex; gap: 10px; align-items: flex-start; }
            .ingredient-row input, .allergen-row input { flex: 1; }
            .step-row textarea { flex: 1; }
            .remove-row { color: #b32d2e; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.add-ingredient').on('click', function() {
                $('#ingredients-list').append('<div class="ingredient-row"><input type="text" name="ingredients[]" /><button type="button" class="button remove-row"><?php esc_html_e('Remove', 'gf-recipes'); ?></button></div>');
            });
            
            $('.add-step').on('click', function() {
                $('#steps-list').append('<div class="step-row"><textarea name="steps[]" rows="2"></textarea><button type="button" class="button remove-row"><?php esc_html_e('Remove', 'gf-recipes'); ?></button></div>');
            });
            
            $('.add-allergen').on('click', function() {
                $('#allergens-list').append('<div class="allergen-row"><input type="text" name="allergens[]" /><button type="button" class="button remove-row"><?php esc_html_e('Remove', 'gf-recipes'); ?></button></div>');
            });
            
            $(document).on('click', '.remove-row', function() {
                $(this).parent().remove();
            });
        });
        </script>
        <?php
    }
    
    public static function save_meta($post_id, $post) {
        if (!isset($_POST['gf_recipe_meta_nonce']) || !wp_verify_nonce($_POST['gf_recipe_meta_nonce'], 'gf_recipe_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $errors = [];
        
        if (!has_post_thumbnail($post_id)) {
            $errors[] = __('Featured image is required.', 'gf-recipes');
        }
        
        if (isset($_POST['ingredients'])) {
            $ingredients = Security::sanitize_ingredients($_POST['ingredients']);
            update_post_meta($post_id, 'ingredients', $ingredients);
        }
        
        if (isset($_POST['steps'])) {
            $steps = Security::sanitize_steps($_POST['steps']);
            update_post_meta($post_id, 'steps', $steps);
        }
        
        if (isset($_POST['cook_time_minutes'])) {
            update_post_meta($post_id, 'cook_time_minutes', absint($_POST['cook_time_minutes']));
        }
        
        if (isset($_POST['difficulty']) && in_array($_POST['difficulty'], ['easy', 'medium', 'hard'])) {
            update_post_meta($post_id, 'difficulty', sanitize_text_field($_POST['difficulty']));
        }
        
        if (isset($_POST['servings'])) {
            update_post_meta($post_id, 'servings', absint($_POST['servings']));
        }
        
        if (isset($_POST['allergens'])) {
            $allergens = Security::sanitize_allergens($_POST['allergens']);
            update_post_meta($post_id, 'allergens', $allergens);
        }
        
        if (!empty($errors)) {
            set_transient('gf_recipe_errors_' . $post_id, $errors, 30);
        }
    }
    
    public static function display_admin_notices() {
        global $post;
        
        if ($post && $post->post_type === 'recipe') {
            $errors = get_transient('gf_recipe_errors_' . $post->ID);
            
            if ($errors) {
                foreach ($errors as $error) {
                    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
                }
                delete_transient('gf_recipe_errors_' . $post->ID);
            }
        }
    }
}
