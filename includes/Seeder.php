<?php

namespace GFRecipes;

defined('ABSPATH') || exit;

class Seeder {
    
    public static function seed_demo_data() {
        if (get_option('gf_recipes_seeded')) {
            return;
        }
        
        $demo_recipes = self::get_demo_recipes();
        
        foreach ($demo_recipes as $recipe_data) {
            $existing = get_page_by_title($recipe_data['title'], OBJECT, 'recipe');
            
            if ($existing) {
                continue;
            }
            
            $post_id = wp_insert_post([
                'post_title'   => $recipe_data['title'],
                'post_content' => $recipe_data['content'],
                'post_type'    => 'recipe',
                'post_status'  => 'publish',
                'post_excerpt' => $recipe_data['excerpt'],
            ]);
            
            if ($post_id) {
                update_post_meta($post_id, 'ingredients', $recipe_data['ingredients']);
                update_post_meta($post_id, 'steps', $recipe_data['steps']);
                update_post_meta($post_id, 'cook_time_minutes', $recipe_data['cook_time_minutes']);
                update_post_meta($post_id, 'difficulty', $recipe_data['difficulty']);
                update_post_meta($post_id, 'servings', $recipe_data['servings']);
                update_post_meta($post_id, 'allergens', $recipe_data['allergens']);
                
                wp_set_object_terms($post_id, $recipe_data['recipe_tags'], 'recipe_tag');
                wp_set_object_terms($post_id, $recipe_data['diet_types'], 'diet_type');
            }
        }
        
        update_option('gf_recipes_seeded', true);
    }
    
    private static function get_demo_recipes() {
        return [
            [
                'title'             => 'Classic Gluten-Free Pizza',
                'content'           => 'A delicious gluten-free pizza with crispy crust and your favorite toppings.',
                'excerpt'           => 'Crispy gluten-free pizza crust topped with tomato sauce, mozzarella, and fresh basil.',
                'ingredients'       => ['2 cups gluten-free flour', '1 tsp salt', '1 tbsp olive oil', '1 cup warm water', '1 tsp yeast', 'tomato sauce', 'mozzarella cheese', 'fresh basil'],
                'steps'             => ['Mix flour, salt, yeast in a bowl', 'Add water and olive oil, knead into dough', 'Let rise for 30 minutes', 'Roll out dough into pizza shape', 'Add sauce and toppings', 'Bake at 425°F for 15-20 minutes'],
                'cook_time_minutes' => 60,
                'difficulty'        => 'medium',
                'servings'          => 4,
                'allergens'         => ['dairy'],
                'recipe_tags'       => ['gluten-free', 'italian', 'dinner'],
                'diet_types'        => ['gluten-free'],
            ],
            [
                'title'             => 'Quinoa Buddha Bowl',
                'content'           => 'A nutritious and colorful bowl packed with quinoa, roasted vegetables, and tahini dressing.',
                'excerpt'           => 'Healthy quinoa bowl with roasted sweet potato, chickpeas, and creamy tahini.',
                'ingredients'       => ['1 cup quinoa', '1 sweet potato', '1 can chickpeas', '2 cups kale', 'tahini', 'lemon juice', 'olive oil', 'salt', 'pepper'],
                'steps'             => ['Cook quinoa according to package directions', 'Dice sweet potato and roast at 400°F for 25 minutes', 'Drain and roast chickpeas until crispy', 'Massage kale with olive oil and lemon', 'Assemble bowl with quinoa, vegetables, and chickpeas', 'Drizzle with tahini dressing'],
                'cook_time_minutes' => 45,
                'difficulty'        => 'easy',
                'servings'          => 2,
                'allergens'         => ['sesame'],
                'recipe_tags'       => ['gluten-free', 'healthy', 'vegetarian', 'lunch'],
                'diet_types'        => ['gluten-free', 'vegan', 'vegetarian'],
            ],
            [
                'title'             => 'Chocolate Chip Cookies',
                'content'           => 'Soft and chewy gluten-free chocolate chip cookies that taste just like the original.',
                'excerpt'           => 'Classic chocolate chip cookies made with gluten-free flour blend.',
                'ingredients'       => ['2 cups gluten-free flour blend', '1 tsp baking soda', '1/2 cup butter', '3/4 cup brown sugar', '1/4 cup white sugar', '2 eggs', '2 tsp vanilla', '2 cups chocolate chips'],
                'steps'             => ['Preheat oven to 350°F', 'Cream butter and sugars together', 'Beat in eggs and vanilla', 'Mix in flour and baking soda', 'Fold in chocolate chips', 'Drop spoonfuls onto baking sheet', 'Bake for 10-12 minutes'],
                'cook_time_minutes' => 25,
                'difficulty'        => 'easy',
                'servings'          => 24,
                'allergens'         => ['dairy', 'eggs'],
                'recipe_tags'       => ['gluten-free', 'dessert', 'cookies'],
                'diet_types'        => ['gluten-free', 'vegetarian'],
            ],
            [
                'title'             => 'Thai Green Curry',
                'content'           => 'Aromatic Thai green curry with vegetables and coconut milk, naturally gluten-free.',
                'excerpt'           => 'Spicy and creamy Thai curry with fresh vegetables and aromatic herbs.',
                'ingredients'       => ['2 tbsp green curry paste', '1 can coconut milk', '1 cup chicken or tofu', '1 bell pepper', '1 zucchini', '1 cup green beans', 'fish sauce', 'lime juice', 'basil', 'jasmine rice'],
                'steps'             => ['Cook rice according to package', 'Heat curry paste in pan', 'Add coconut milk and bring to simmer', 'Add protein and cook through', 'Add vegetables and simmer until tender', 'Season with fish sauce and lime', 'Serve over rice with fresh basil'],
                'cook_time_minutes' => 30,
                'difficulty'        => 'medium',
                'servings'          => 4,
                'allergens'         => [],
                'recipe_tags'       => ['gluten-free', 'thai', 'curry', 'dinner'],
                'diet_types'        => ['gluten-free'],
            ],
            [
                'title'             => 'Breakfast Egg Muffins',
                'content'           => 'Protein-packed egg muffins perfect for meal prep and busy mornings.',
                'excerpt'           => 'Make-ahead egg muffins with vegetables and cheese.',
                'ingredients'       => ['8 eggs', '1/4 cup milk', '1 cup spinach', '1/2 cup bell peppers', '1/2 cup cheese', '1/4 cup onion', 'salt', 'pepper'],
                'steps'             => ['Preheat oven to 350°F', 'Whisk eggs and milk together', 'Chop vegetables finely', 'Mix vegetables and cheese into eggs', 'Pour into greased muffin tin', 'Bake for 20-25 minutes until set'],
                'cook_time_minutes' => 30,
                'difficulty'        => 'easy',
                'servings'          => 12,
                'allergens'         => ['eggs', 'dairy'],
                'recipe_tags'       => ['gluten-free', 'breakfast', 'meal-prep'],
                'diet_types'        => ['gluten-free', 'vegetarian'],
            ],
            [
                'title'             => 'Lemon Herb Grilled Chicken',
                'content'           => 'Juicy grilled chicken marinated in lemon, garlic, and fresh herbs.',
                'excerpt'           => 'Simple and flavorful grilled chicken with Mediterranean flavors.',
                'ingredients'       => ['4 chicken breasts', '1/4 cup olive oil', '2 lemons', '4 garlic cloves', 'fresh rosemary', 'fresh thyme', 'salt', 'pepper'],
                'steps'             => ['Mix olive oil, lemon juice, minced garlic, and herbs', 'Marinate chicken for at least 2 hours', 'Preheat grill to medium-high', 'Grill chicken 6-7 minutes per side', 'Check internal temperature reaches 165°F', 'Let rest 5 minutes before serving'],
                'cook_time_minutes' => 25,
                'difficulty'        => 'easy',
                'servings'          => 4,
                'allergens'         => [],
                'recipe_tags'       => ['gluten-free', 'chicken', 'grilled', 'dinner'],
                'diet_types'        => ['gluten-free'],
            ],
            [
                'title'             => 'Sweet Potato Fries',
                'content'           => 'Crispy baked sweet potato fries seasoned to perfection.',
                'excerpt'           => 'Healthy alternative to regular fries with a hint of sweetness.',
                'ingredients'       => ['3 large sweet potatoes', '2 tbsp olive oil', '1 tsp paprika', '1/2 tsp garlic powder', 'salt', 'pepper'],
                'steps'             => ['Preheat oven to 425°F', 'Cut sweet potatoes into thin strips', 'Toss with oil and seasonings', 'Spread on baking sheet in single layer', 'Bake for 30-35 minutes, flipping halfway', 'Serve immediately'],
                'cook_time_minutes' => 40,
                'difficulty'        => 'easy',
                'servings'          => 4,
                'allergens'         => [],
                'recipe_tags'       => ['gluten-free', 'side-dish', 'healthy'],
                'diet_types'        => ['gluten-free', 'vegan', 'vegetarian'],
            ],
            [
                'title'             => 'Beef Tacos with Corn Tortillas',
                'content'           => 'Classic beef tacos using naturally gluten-free corn tortillas.',
                'excerpt'           => 'Flavorful ground beef tacos with all your favorite toppings.',
                'ingredients'       => ['1 lb ground beef', 'corn tortillas', '1 onion', '2 garlic cloves', 'chili powder', 'cumin', 'lettuce', 'tomatoes', 'cheese', 'sour cream'],
                'steps'             => ['Brown ground beef with diced onion and garlic', 'Add chili powder and cumin, cook 5 minutes', 'Warm corn tortillas', 'Chop lettuce and tomatoes', 'Assemble tacos with meat and toppings', 'Serve with lime wedges'],
                'cook_time_minutes' => 20,
                'difficulty'        => 'easy',
                'servings'          => 4,
                'allergens'         => ['dairy'],
                'recipe_tags'       => ['gluten-free', 'mexican', 'tacos', 'dinner'],
                'diet_types'        => ['gluten-free'],
            ],
            [
                'title'             => 'Almond Flour Pancakes',
                'content'           => 'Fluffy pancakes made with almond flour for a protein-rich breakfast.',
                'excerpt'           => 'Grain-free pancakes that are light, fluffy, and delicious.',
                'ingredients'       => ['2 cups almond flour', '3 eggs', '1/4 cup milk', '2 tbsp honey', '1 tsp baking powder', '1/2 tsp vanilla', 'butter for cooking'],
                'steps'             => ['Mix all ingredients in a bowl until smooth', 'Heat griddle over medium heat', 'Pour 1/4 cup batter per pancake', 'Cook until bubbles form, then flip', 'Cook another 2 minutes until golden', 'Serve with maple syrup and berries'],
                'cook_time_minutes' => 15,
                'difficulty'        => 'easy',
                'servings'          => 4,
                'allergens'         => ['eggs', 'dairy', 'nuts'],
                'recipe_tags'       => ['gluten-free', 'breakfast', 'pancakes'],
                'diet_types'        => ['gluten-free', 'vegetarian'],
            ],
            [
                'title'             => 'Mediterranean Chickpea Salad',
                'content'           => 'Fresh and vibrant salad with chickpeas, vegetables, and lemon dressing.',
                'excerpt'           => 'Light and refreshing salad perfect for lunch or as a side.',
                'ingredients'       => ['2 cans chickpeas', '1 cucumber', '2 tomatoes', '1 red onion', 'feta cheese', 'olives', 'olive oil', 'lemon juice', 'oregano'],
                'steps'             => ['Drain and rinse chickpeas', 'Dice cucumber, tomatoes, and onion', 'Combine all vegetables in a bowl', 'Whisk olive oil, lemon juice, and oregano', 'Toss salad with dressing', 'Top with feta and olives'],
                'cook_time_minutes' => 15,
                'difficulty'        => 'easy',
                'servings'          => 6,
                'allergens'         => ['dairy'],
                'recipe_tags'       => ['gluten-free', 'salad', 'mediterranean', 'vegetarian'],
                'diet_types'        => ['gluten-free', 'vegetarian'],
            ],
            [
                'title'             => 'Coconut Rice Pudding',
                'content'           => 'Creamy rice pudding made with coconut milk and topped with fresh fruit.',
                'excerpt'           => 'Dairy-free dessert with tropical coconut flavor.',
                'ingredients'       => ['1 cup jasmine rice', '2 cans coconut milk', '1/2 cup sugar', '1 tsp vanilla', 'cinnamon', 'fresh mango', 'shredded coconut'],
                'steps'             => ['Cook rice in coconut milk over low heat', 'Stir frequently until creamy, about 30 minutes', 'Add sugar and vanilla', 'Let cool slightly', 'Top with fresh mango and coconut', 'Sprinkle with cinnamon'],
                'cook_time_minutes' => 40,
                'difficulty'        => 'medium',
                'servings'          => 6,
                'allergens'         => [],
                'recipe_tags'       => ['gluten-free', 'dessert', 'dairy-free'],
                'diet_types'        => ['gluten-free', 'vegan', 'vegetarian', 'dairy-free'],
            ],
            [
                'title'             => 'Zucchini Noodles with Pesto',
                'content'           => 'Light and healthy zucchini noodles tossed with homemade basil pesto.',
                'excerpt'           => 'Low-carb alternative to pasta with fresh, vibrant flavors.',
                'ingredients'       => ['4 zucchini', '2 cups basil', '1/2 cup pine nuts', '2 garlic cloves', '1/2 cup parmesan', '1/2 cup olive oil', 'cherry tomatoes'],
                'steps'             => ['Spiralize zucchini into noodles', 'Blend basil, pine nuts, garlic, parmesan, and olive oil for pesto', 'Heat zucchini noodles in pan for 2-3 minutes', 'Toss with pesto', 'Add halved cherry tomatoes', 'Serve immediately with extra parmesan'],
                'cook_time_minutes' => 20,
                'difficulty'        => 'easy',
                'servings'          => 4,
                'allergens'         => ['dairy', 'nuts'],
                'recipe_tags'       => ['gluten-free', 'low-carb', 'italian', 'dinner'],
                'diet_types'        => ['gluten-free', 'vegetarian'],
            ],
        ];
    }
}
