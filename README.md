# 🚀 The Pi Concepts WordPress Project

This repository contains the source code and configuration for **The Pi Concepts** WordPress website, including custom plugins, themes, and Docker-based local development setup.

## 🗂️ Project Structure

- 📦 `wp-content/plugins/thepi-core/`  
  Custom plugin for registering custom post types (CPTs), APIs, and blocks specific to The Pi Concepts.

- 🎨 `wp-content/themes/the-pi-concepts/`  
  Custom theme for the website (not included in this repo by default, see `.gitignore`).

- ⚙️ `wp-config.php`  
  WordPress configuration file, pre-configured for local development.

- 🚫 `.gitignore`  
  Configured to version only custom code (plugins/themes) and ignore WordPress core, uploads, and sensitive files.

- 🔄 `.htaccess`  
  Standard WordPress rewrite rules.

## 💻 Local Development

This project is intended to be run in a local development environment, typically using Docker.  
**Database credentials and other settings in `wp-config.php` are set for local use.**

### 🏁 Steps to Get Started

1. 🧬 **Clone the repository:**

   ```bash
   git clone <this-repo-url>
   cd <project-directory>
   ```

2. 🐳 **Set up Docker (if using):**

   - Ensure you have Docker and Docker Compose installed.
   - Use your own `docker-compose.yml` file to spin up WordPress, MySQL, and other services as needed.

3. 🛠️ **Install WordPress:**

   - On first run, visit `http://localhost` in your browser and follow the WordPress installation steps.
   - The database credentials are pre-set in `wp-config.php`:
     - 🗄️ DB Name: `wordpress`
     - 👤 DB User: `wp_user`
     - 🔑 DB Password: `wp_pass`
     - 🖧 DB Host: `db:3306`

4. 🧩 **Activate the Custom Plugin:**

   - Go to the WordPress admin dashboard.
   - Navigate to **Plugins** and activate **The Pi Core** plugin.

5. 🛠️ **Develop Your Theme/Plugin:**
   - Place your custom theme in `wp-content/themes/the-pi-concepts/`.
   - Place additional custom plugins in `wp-content/plugins/`.

## 🧩 Custom Plugin: The Pi Core

- Registers custom post types and blocks for The Pi Concepts.
- Organizes code into `post-types/`, `apis/`, and `components/` for maintainability.

## 🗃️ Version Control

- Only custom code (plugins/themes) is versioned.
- WordPress core, uploads, and sensitive files are ignored via `.gitignore`.
- If you wish to version core files, adjust `.gitignore` accordingly.

## 🔒 Security & Best Practices

- 🚫 Do **not** commit sensitive information (passwords, API keys) to the repository.
- 🏭 For production, update `wp-config.php` with secure credentials and set `WP_DEBUG` to `false`.

## 🆘 Support

For questions or issues, please contact the project maintainer or open an issue in this repository.

---

✨ **Happy publishing with The Pi Concepts!** ✨
