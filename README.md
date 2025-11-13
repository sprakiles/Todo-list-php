# Modern To-Do List

## Setup
1. Ensure PHP has `pdo_sqlite` enabled.
2. Run the app:
   ```bash
   php -S localhost:8000
   ```
3. Open `http://localhost:8000` in your browser.

## Files
- `index.php` – UI & JavaScript
- `styles.css` – styling
- `config.php` – database config
- `auth.php` / `tasks.php` – backend endpoints
- `view_database.php` – inspect DB

## Notes
- Data is stored in `todo_app.db` (SQLite).
- Theme preference is saved with `localStorage`.
- To view users/tasks quickly, open `view_database.php`.
