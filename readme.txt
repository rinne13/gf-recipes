=== Gluten Free Recipes ===
Contributors: yourname
Tags: recipes, gluten-free, cooking, food, custom-post-type
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete recipe management system with custom post types, advanced search, and ingredient-based suggestions.

== Description ==

Gluten Free Recipes is a comprehensive WordPress plugin for managing and showcasing gluten-free recipes. Perfect for food bloggers, recipe sites, and cooking enthusiasts.

= Features =

* Custom Post Type for Recipes with full metadata support
* Advanced Search with relevance scoring
* "What's in my Fridge?" ingredient-based recipe suggestions
* REST API endpoints for programmatic access
* Taxonomies: Recipe Tags and Diet Types
* Frontend shortcodes for search and recipe submission
* Responsive and accessible design
* SEO-friendly structure

= Custom Fields =

* Ingredients (array)
* Cooking Steps (ordered array)
* Cook Time (minutes)
* Difficulty Level (easy/medium/hard)
* Servings
* Allergen Information

= Shortcodes =

* `[gf_search]` - Display recipe search form with filters
* `[gf_recipe_form]` - Frontend recipe submission form (requires login)

= REST API =

* `GET /wp-json/gf/v1/recipes` - Search recipes with filters
* `POST /wp-json/gf/v1/recipes` - Create new recipe (authenticated)
* `GET /wp-json/gf/v1/suggest` - Get recipe suggestions based on ingredients

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/gf-recipes/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Recipes in the admin menu to start adding recipes
4. Demo data with 12 sample recipes will be automatically seeded on activation

== Frequently Asked Questions ==

= How do I add a recipe? =

Go to Recipes > Add New in your WordPress admin panel. Fill in the title, description, and use the Recipe Details metabox to add ingredients, steps, and other metadata.

= How does the ingredient suggestion feature work? =

Create a page and use the "What's in my Fridge" template. Users enter their available ingredients, and the system suggests recipes they can make based on ingredient matching with a scoring algorithm.

= Can users submit recipes from the frontend? =

Yes! Use the `[gf_recipe_form]` shortcode on any page. Users must be logged in and have the capability to edit posts.

== Screenshots ==

1. Recipe archive page showing all recipes
2. Single recipe view with ingredients and steps
3. Admin interface for adding/editing recipes
4. Search interface with filters
5. "What's in my Fridge?" suggestion tool

== Changelog ==

= 1.0.0 =
* Initial release
* Custom Post Type for recipes
* Advanced search functionality
* Ingredient-based suggestions
* REST API endpoints
* Demo data seeder

== Upgrade Notice ==

= 1.0.0 =
Initial release of Gluten Free Recipes plugin.
