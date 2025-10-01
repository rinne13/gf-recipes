<?php
namespace GFRecipes;

defined('ABSPATH') || exit;

/**
 * Front-end recipe submission: shortcode, assets, and form handler.
 * Shortcode: [gf_submit_recipe]
 */
class Submit {
    public static function register() : void {
        add_shortcode('gf_submit_recipe', [__CLASS__, 'render_form']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('init', [__CLASS__, 'maybe_handle_post']);
    }

    public static function enqueue_assets() : void {
        // Enqueue CSS ONLY on pages that contain the shortcode
        if (!is_singular()) return;
        global $post;
        if (!$post || !has_shortcode($post->post_content, 'gf_submit_recipe')) return;

        wp_enqueue_style(
            'gf-recipes-submit',
            GF_RECIPES_PLUGIN_URL . 'assets/css/submit.css',
            [],
            GF_RECIPES_VERSION
        );
    }

    /**
     * Handle form submission (creates a pending "recipe" post)
     */
    public static function maybe_handle_post() : void {
        // Bail if form is not posted
        if (empty($_POST['gf_submit_recipe_nonce'])) return;

        // Verify nonce
        if (!wp_verify_nonce($_POST['gf_submit_recipe_nonce'], 'gf_submit_recipe')) {
            wp_die(__('Security check failed.', 'gf-recipes'));
        }

        // Honeypot (bots usually fill this field)
        if (!empty($_POST['website'])) {
            wp_die(__('Spam detected.', 'gf-recipes'));
        }

        // Basic validation
        $title   = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
        if ($title === '' || $content === '') {
            self::redirect_with_message('error', __('Please fill in the required fields: title and description.', 'gf-recipes'));
        }

        // Determine author (logged-in user or guest)
        $author_id   = is_user_logged_in() ? get_current_user_id() : 0;
        $guest_name  = '';
        $guest_email = '';

        if (!$author_id) {
            $guest_name  = isset($_POST['guest_name']) ? sanitize_text_field($_POST['guest_name']) : '';
            $guest_email = isset($_POST['guest_email']) ? sanitize_email($_POST['guest_email']) : '';
            if ($guest_name === '' || !is_email($guest_email)) {
                self::redirect_with_message('error', __('For guest submission please provide a valid name and email.', 'gf-recipes'));
            }
        }

        // Raw meta fields
        $ingredients_raw = isset($_POST['ingredients']) ? wp_unslash($_POST['ingredients']) : '';
        $steps_raw       = isset($_POST['steps']) ? wp_unslash($_POST['steps']) : '';

        $cook_time  = isset($_POST['cook_time']) ? absint($_POST['cook_time']) : 0;
        $difficulty = isset($_POST['difficulty']) ? sanitize_key($_POST['difficulty']) : '';

        // Split ingredients/steps by line
        $ingredients = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $ingredients_raw))));
        $steps       = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $steps_raw))));

        // Create post in "pending" status for moderation
        $post_id = wp_insert_post([
            'post_type'    => 'recipe',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'pending',
            'post_author'  => $author_id,
        ], true);

        if (is_wp_error($post_id)) {
            self::redirect_with_message('error', __('Failed to create the recipe. Please try again later.', 'gf-recipes'));
        }

        // Assign tags (CSV)
        if (!empty($_POST['tags'])) {
            $tags_csv = sanitize_text_field(wp_unslash($_POST['tags']));
            $tags     = array_values(array_filter(array_map('trim', explode(',', $tags_csv))));
            if ($tags) {
                // Assumes taxonomy slug is 'recipe_tag' (adjust if different)
                wp_set_post_terms($post_id, $tags, 'recipe_tag', false);
            }
        }

        // Save meta (adjust keys if your Meta.php expects other names)
        if ($ingredients) update_post_meta($post_id, 'ingredients', $ingredients);
        if ($steps)       update_post_meta($post_id, 'steps', $steps);
        if ($cook_time)   update_post_meta($post_id, 'cook_time_minutes', $cook_time);
        if (in_array($difficulty, ['easy','medium','hard'], true)) {
            update_post_meta($post_id, 'difficulty', $difficulty);
        }

        // Save guest info if applicable
        if (!$author_id) {
            update_post_meta($post_id, '_guest_name', $guest_name);
            update_post_meta($post_id, '_guest_email', $guest_email);
        }

        // Featured image upload
        if (!empty($_FILES['featured_image']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            // (Optional) add basic upload constraints here via filters if needed
            $attachment_id = media_handle_upload('featured_image', $post_id);
            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        // Notify admin (optional, can be disabled via filter)
        if (apply_filters('gf_recipes_notify_on_submission', true)) {
            $admin_email = get_option('admin_email');
            wp_mail(
                $admin_email,
                sprintf(__('New recipe submitted: %s', 'gf-recipes'), $title),
                sprintf(__('Review it: %s', 'gf-recipes'), admin_url('post.php?post=' . $post_id . '&action=edit'))
            );
        }

        // Redirect with success message
        self::redirect_with_message('success', __('Thanks! Your recipe was submitted and awaits moderation.', 'gf-recipes'));
    }

    /**
     * Redirect back with a status message
     */
    private static function redirect_with_message(string $type, string $msg) : void {
        $url = add_query_arg([
            'gf_recipe_submit' => $type,
            'gf_msg'           => rawurlencode($msg),
        ], wp_get_referer() ?: home_url('/'));
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Shortcode renderer: outputs the submission form
     */
    public static function render_form() : string {
        // Feedback notice after redirect
        $notice = '';
        if (!empty($_GET['gf_recipe_submit']) && !empty($_GET['gf_msg'])) {
            $class  = sanitize_key($_GET['gf_recipe_submit']) === 'success' ? 'notice success' : 'notice error';
            $notice = '<div class="'.esc_attr($class).'">'.esc_html(wp_unslash($_GET['gf_msg'])).'</div>';
        }

        $is_guest = !is_user_logged_in();

        ob_start(); ?>
        <div class="gf-submit-scope">
          <div class="gf-submit-wrapper">
         

            <form class="gf-submit-form" method="post" enctype="multipart/form-data">
              <?php wp_nonce_field('gf_submit_recipe', 'gf_submit_recipe_nonce'); ?>

              <!-- Honeypot (invisible field to trap bots) -->
              <input type="text" name="website" value="" class="gf-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

              <?php if ($is_guest): ?>
                <div class="gf-field two-col">
                  <p>
                    <label for="guest_name"><?php esc_html_e('Your Name*', 'gf-recipes'); ?></label>
                    <input type="text" id="guest_name" name="guest_name" required>
                  </p>
                  <p>
                    <label for="guest_email"><?php esc_html_e('Email*', 'gf-recipes'); ?></label>
                    <input type="email" id="guest_email" name="guest_email" required>
                  </p>
                </div>
              <?php endif; ?>

              <p class="gf-field">
                <label for="title"><?php esc_html_e('Recipe Title*', 'gf-recipes'); ?></label>
                <input type="text" id="title" name="title" required>
              </p>

              <p class="gf-field">
                <label for="content"><?php esc_html_e('Short Description*', 'gf-recipes'); ?></label>
                <textarea id="content" name="content" rows="4" required></textarea>
              </p>

              <div class="gf-field two-col">
                <p>
                  <label for="cook_time"><?php esc_html_e('Cook Time (minutes)', 'gf-recipes'); ?></label>
                  <input type="number" id="cook_time" name="cook_time" min="0" step="1" placeholder="30">
                </p>
                <p>
                  <label for="difficulty"><?php esc_html_e('Difficulty', 'gf-recipes'); ?></label>
                  <select id="difficulty" name="difficulty">
                    <option value=""><?php esc_html_e('Select…', 'gf-recipes'); ?></option>
                    <option value="easy"><?php esc_html_e('Easy', 'gf-recipes'); ?></option>
                    <option value="medium"><?php esc_html_e('Medium', 'gf-recipes'); ?></option>
                    <option value="hard"><?php esc_html_e('Hard', 'gf-recipes'); ?></option>
                  </select>
                </p>
              </div>

              <p class="gf-field">
                <label for="ingredients"><?php esc_html_e('Ingredients* (one per line)', 'gf-recipes'); ?></label>
                <textarea id="ingredients" name="ingredients" rows="6" placeholder="2 eggs
100 g rice flour
1 tsp baking powder" required></textarea>
              </p>

              <p class="gf-field">
                <label for="steps"><?php esc_html_e('Steps* (one per line)', 'gf-recipes'); ?></label>
                <textarea id="steps" name="steps" rows="6" placeholder="Preheat oven to 180°C
Mix wet ingredients
Add dry ingredients and stir" required></textarea>
              </p>

              <p class="gf-field">
                <label for="tags"><?php esc_html_e('Tags (comma-separated)', 'gf-recipes'); ?></label>
                <input type="text" id="tags" name="tags" placeholder="breakfast, quick, dairy-free">
              </p>

              <p class="gf-field">
                <label for="featured_image"><?php esc_html_e('Featured Image', 'gf-recipes'); ?></label>
                <input type="file" id="featured_image" name="featured_image" accept="image/*">
              </p>

              <p class="gf-actions">
                <button type="submit" class="gf-submit-btn"><?php esc_html_e('Submit Recipe', 'gf-recipes'); ?></button>
              </p>
            </form>

            <p class="gf-note"><?php esc_html_e('By submitting, you agree that your recipe will be reviewed before publication.', 'gf-recipes'); ?></p>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

Submit::register();
