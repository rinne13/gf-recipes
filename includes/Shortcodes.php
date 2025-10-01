<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Shortcodes {
    
    public static function register() {
        add_shortcode('gf_search', [__CLASS__, 'search_shortcode']);
        add_shortcode('gf_recipe_form', [__CLASS__, 'recipe_form_shortcode']);
    }
    
    public static function search_shortcode($atts) {
        $atts = shortcode_atts([
            'per_page' => 12,
        ], $atts);
        
        ob_start();
        
        $keyword = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $difficulty = isset($_GET['difficulty']) ? sanitize_text_field($_GET['difficulty']) : '';
        $cook_time_max = isset($_GET['cook_time_max']) ? absint($_GET['cook_time_max']) : 0;
        $diet_type = isset($_GET['diet_type']) ? sanitize_text_field($_GET['diet_type']) : '';
        
        ?>
        <div class="gf-search-form">
            <form method="get" action="">
                <div class="search-row">
                    <input type="text" name="q" placeholder="<?php esc_attr_e('Search recipes...', 'gf-recipes'); ?>" value="<?php echo esc_attr($keyword); ?>" />
                    
                    <select name="difficulty">
                        <option value=""><?php esc_html_e('Any Difficulty', 'gf-recipes'); ?></option>
                        <option value="easy" <?php selected($difficulty, 'easy'); ?>><?php esc_html_e('Easy', 'gf-recipes'); ?></option>
                        <option value="medium" <?php selected($difficulty, 'medium'); ?>><?php esc_html_e('Medium', 'gf-recipes'); ?></option>
                        <option value="hard" <?php selected($difficulty, 'hard'); ?>><?php esc_html_e('Hard', 'gf-recipes'); ?></option>
                    </select>
                    
                    <select name="diet_type">
                        <option value=""><?php esc_html_e('Any Diet', 'gf-recipes'); ?></option>
                        <?php
                        $diet_types = get_terms(['taxonomy' => 'diet_type', 'hide_empty' => false]);
                        foreach ($diet_types as $term) {
                            echo '<option value="' . esc_attr($term->slug) . '" ' . selected($diet_type, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <input type="number" name="cook_time_max" placeholder="<?php esc_attr_e('Max time (min)', 'gf-recipes'); ?>" value="<?php echo esc_attr($cook_time_max); ?>" min="0" />
                    
                    <button type="submit"><?php esc_html_e('Search', 'gf-recipes'); ?></button>
                </div>
            </form>
        </div>
        
        <?php
        
        if (!empty($keyword) || !empty($difficulty) || !empty($cook_time_max) || !empty($diet_type)) {
            $recipes = Search::search_recipes([
                'keyword'        => $keyword,
                'difficulty'     => $difficulty,
                'cook_time_max'  => $cook_time_max,
                'diet_type'      => $diet_type,
                'posts_per_page' => absint($atts['per_page']),
            ]);
            
            if (!empty($recipes)) {
                echo '<div class="gf-search-results">';
                echo '<p>' . sprintf(esc_html__('Found %d recipes', 'gf-recipes'), count($recipes)) . '</p>';
                echo '<div class="recipes-grid">';
                
                foreach ($recipes as $recipe) {
                    self::render_recipe_card($recipe);
                }
                
                echo '</div></div>';
            } else {
                echo '<p class="no-results">' . esc_html__('No recipes found. Try different search criteria.', 'gf-recipes') . '</p>';
            }
        }
        
        return ob_get_clean();
    }
    
    public static function recipe_form_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to add a recipe.', 'gf-recipes') . '</p>';
        }
        
        if (!Security::can_edit_recipes()) {
            return '<p>' . esc_html__('You do not have permission to add recipes.', 'gf-recipes') . '</p>';
        }
        
        ob_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gf_recipe_nonce']) && wp_verify_nonce($_POST['gf_recipe_nonce'], 'gf_add_recipe')) {
            $title = sanitize_text_field($_POST['recipe_title']);
            $content = wp_kses_post($_POST['recipe_content']);
            
            if (empty($title)) {
                echo '<div class="notice error"><p>' . esc_html__('Title is required.', 'gf-recipes') . '</p></div>';
            } else {
                $post_id = wp_insert_post([
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_type'    => 'recipe',
                    'post_status'  => 'publish',
                ]);
                
                if ($post_id) {
                    if (!empty($_POST['ingredients'])) {
                        $ingredients = Security::sanitize_ingredients($_POST['ingredients']);
                        update_post_meta($post_id, 'ingredients', $ingredients);
                    }
                    
                    if (!empty($_POST['steps'])) {
                        $steps = Security::sanitize_steps($_POST['steps']);
                        update_post_meta($post_id, 'steps', $steps);
                    }
                    
                    if (!empty($_POST['cook_time_minutes'])) {
                        update_post_meta($post_id, 'cook_time_minutes', absint($_POST['cook_time_minutes']));
                    }
                    
                    if (!empty($_POST['difficulty'])) {
                        update_post_meta($post_id, 'difficulty', sanitize_text_field($_POST['difficulty']));
                    }
                    
                    if (!empty($_POST['servings'])) {
                        update_post_meta($post_id, 'servings', absint($_POST['servings']));
                    }
                    
                    echo '<div class="notice success"><p>' . esc_html__('Recipe added successfully!', 'gf-recipes') . ' <a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html__('View recipe', 'gf-recipes') . '</a></p></div>';
                }
            }
        }
        
        ?>
        <div class="gf-recipe-form">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('gf_add_recipe', 'gf_recipe_nonce'); ?>
                
                <p>
                    <label for="recipe_title"><?php esc_html_e('Recipe Title:', 'gf-recipes'); ?> *</label>
                    <input type="text" id="recipe_title" name="recipe_title" required />
                </p>
                
                <p>
                    <label for="recipe_content"><?php esc_html_e('Description:', 'gf-recipes'); ?></label>
                    <textarea id="recipe_content" name="recipe_content" rows="4"></textarea>
                </p>
                
                <p>
                    <label for="cook_time_minutes"><?php esc_html_e('Cook Time (minutes):', 'gf-recipes'); ?></label>
                    <input type="number" id="cook_time_minutes" name="cook_time_minutes" min="0" />
                </p>
                
                <p>
                    <label for="difficulty"><?php esc_html_e('Difficulty:', 'gf-recipes'); ?></label>
                    <select id="difficulty" name="difficulty">
                        <option value=""><?php esc_html_e('Select...', 'gf-recipes'); ?></option>
                        <option value="easy"><?php esc_html_e('Easy', 'gf-recipes'); ?></option>
                        <option value="medium"><?php esc_html_e('Medium', 'gf-recipes'); ?></option>
                        <option value="hard"><?php esc_html_e('Hard', 'gf-recipes'); ?></option>
                    </select>
                </p>
                
                <p>
                    <label for="servings"><?php esc_html_e('Servings:', 'gf-recipes'); ?></label>
                    <input type="number" id="servings" name="servings" min="1" />
                </p>
                
                <div class="ingredients-section">
                    <label><?php esc_html_e('Ingredients:', 'gf-recipes'); ?></label>
                    <div id="ingredients-list">
                        <input type="text" name="ingredients[]" placeholder="<?php esc_attr_e('e.g., 2 cups rice flour', 'gf-recipes'); ?>" />
                    </div>
                    <button type="button" class="add-ingredient"><?php esc_html_e('+ Add Ingredient', 'gf-recipes'); ?></button>
                </div>
                
                <div class="steps-section">
                    <label><?php esc_html_e('Steps:', 'gf-recipes'); ?></label>
                    <div id="steps-list">
                        <textarea name="steps[]" rows="2" placeholder="<?php esc_attr_e('Step 1...', 'gf-recipes'); ?>"></textarea>
                    </div>
                    <button type="button" class="add-step"><?php esc_html_e('+ Add Step', 'gf-recipes'); ?></button>
                </div>
                
                <p>
                    <button type="submit" class="submit-button"><?php esc_html_e('Add Recipe', 'gf-recipes'); ?></button>
                </p>
            </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.add-ingredient').addEventListener('click', function() {
                const list = document.getElementById('ingredients-list');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'ingredients[]';
                list.appendChild(input);
            });
            
            document.querySelector('.add-step').addEventListener('click', function() {
                const list = document.getElementById('steps-list');
                const textarea = document.createElement('textarea');
                textarea.name = 'steps[]';
                textarea.rows = 2;
                list.appendChild(textarea);
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    private static function render_recipe_card($recipe) {
        ?>
        <div class="recipe-card">
            <?php if ($recipe['thumbnail']): ?>
                <img src="<?php echo esc_url($recipe['thumbnail']); ?>" alt="<?php echo esc_attr($recipe['title']); ?>" />
            <?php endif; ?>
            
            <div class="recipe-content">
                <h3><a href="<?php echo esc_url($recipe['permalink']); ?>"><?php echo esc_html($recipe['title']); ?></a></h3>
                
                <div class="recipe-badges">
                    <?php if ($recipe['cook_time_minutes']): ?>
                        <span class="badge time">⏱ <?php echo esc_html($recipe['cook_time_minutes']); ?> min</span>
                    <?php endif; ?>
                    
                    <?php if ($recipe['difficulty']): ?>
                        <span class="badge difficulty <?php echo esc_attr($recipe['difficulty']); ?>">
                            <?php
                            $dots = ['easy' => '•', 'medium' => '• •', 'hard' => '• • •'];
                            echo esc_html($dots[$recipe['difficulty']] ?? '');
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php foreach ($recipe['diet_types'] as $diet): ?>
                        <span class="badge diet"><?php echo esc_html($diet); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($recipe['excerpt']): ?>
                    <p class="excerpt"><?php echo esc_html($recipe['excerpt']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
