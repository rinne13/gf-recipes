# gf-recipes
# GF Recipes

A complete WordPress recipe management system with custom post types, metadata, search, and user submissions.  

✨ Features:
- Custom Post Type: **Recipes**
- Rich metadata: ingredients, steps, cooking time, difficulty level
- **Tags support** (breakfast, dessert, dairy-free, etc.)
- User-friendly **submission form** via shortcode `[gf_submit_recipe]`
- Guest and registered user submissions (with moderation)
- Featured image upload
- REST API routes for recipe integration
- Responsive recipe cards grid with modern design

🛠 Tech stack:
- PHP 8.1+
- WordPress 6+
- Custom REST API endpoints
- Vanilla JS + CSS for front-end form handling
- Built with accessibility and mobile-first design in mind

🚀 Perfect for:
- Food blogs and recipe communities
- Gluten-free and health-focused sites
- Any WordPress site that wants structured recipes with search/filtering

---

### Installation
1. Download or clone this repo into your WordPress `/wp-content/plugins/` folder.  
2. Activate **GF Recipes** from the WordPress admin panel.  
3. Use shortcodes:
   - `[gf_recipes]` – display recipes archive/grid
   - `[gf_submit_recipe]` – display user submission form

---

### Usage
- Add recipes via the WordPress admin panel or let users submit them from the front-end.
- Recipes include structured fields: **ingredients (one per line)**, **steps**, **cook time**, and **difficulty**.
- Submitted recipes are stored as **pending** until approved by admin.

---

### Roadmap
- ✅ Basic recipe CPT + metadata  
- ✅ Front-end submission with moderation  
- ✅ Recipe tagging and categorization  
- ⏳ Advanced search (by ingredient, difficulty, time)  
- ⏳ User dashboards with saved recipes  
- ⏳ Rating system and comments  

---

### License
GPL v2 or later. Free to use and modify.
