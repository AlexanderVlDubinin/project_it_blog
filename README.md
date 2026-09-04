# 📝 Laravel IT Blog (Pet Project)

A modern and functional blog with tree-like comments, advanced filtering, a flexible notification system, a full-fledged admin panel and a content parser. The project is fully deployed in an isolated Docker environment based on Alpine Linux.

---

## 🛠 Technology stack and versions

### Infrastructure (Docker)
*   **PHP:** `8.4.23` (FPM, Alpine) + Extensions: `pdo_pgsql`, `mbstring`, `gd` (FreeType/JPEG), `intl`, `sockets`, `redis`
*   **Web Server:** Nginx `1.31.3`
*   **Database:** PostgreSQL `16.14`
*   **Cache & Queue:** Redis `7-alpine`
*   **Mail Testing:** Mailpit (SMTP server and Web interface)
*   **Node.js:** `v24.18.0` (LTS) + Vite (with HMR support)

### Backend & Frontend
*   **Framework:** Laravel `v13.21.1`
*   **Authentication:** Laravel Breeze `v2.4.2`
*   **Admin Panel:** Filament PHP `v5.7.3`
*   **Testing:** `Pest v4.7.5` + `pest-plugin-browser v4.3.1` (Playwright & Chromium)

---

## 🌟 Key features of the application

### 📰 Public part (Website)
*   **Posts:** View the list and the detailed page. Creation and editing by the authors. Support for images, tags, and the Soft Delete system.
*   **Advanced filtering:** Search by ID, title (part of the title), part of the content, author, tags, and date range (from and to) with the ability to instantly reset filters. Pagination of the list.
*   **Tree-like comments:** Endless nesting of responses (cascading structure), comment pagination. Authors can edit their comments. Support for Soft/Hard delete.
*   **Rating system (Likes):** 
    *   For posts: likes only (`+1`).
    *   For comments: likes and dislikes with the calculation of the total live rating (total rating = `Likes` minus `Dislikes').
*   **Smart Notifications (Database & Mail):** 
    *   They are triggered when responding to a comment or are sent manually from the admin panel.
    *   Filtering by type.
    *   Notifications of responses contain a "smart" button link that redirects the user to the post page and automatically scrolls to the desired comment.
    *   **Auto-deletion:** Read notifications are deleted automatically after a user-configured time interval (30 days by default) or manually.

### 🛡 Admin Panel (Filament)
*   **Dashboard:** 
    *   Interactive widget for the dynamics of likes/dislikes in time.
    *   Top 5 most popular posts of the application (by the number of likes).
*   **User Management (Users):** CRUD with pagination and end-to-end search.
*   **Post management (Posts):** Full-fledged CRUD, bucket management (Soft Delete / Restore / Hard Delete). The ability to create new tags on the fly directly in the post editing form.
*   **Comment Management:** Available directly on the edit page of the linked post.
*   **Tag Management (Tags):** CRUD with search and pagination.
*   **Sending notifications (Notification Center):**
    *   4 types: `info`, `warning`, `danger`, `success`.
    *   Channels: Email, Database (or both).
    *   Targeting: A specific user, a group by role, or mass sending to everyone.
    *   Dynamic icon selection from the `Heroicons` pack.

---

## ⚙️ Console commands (Automation and CRON)

*   `php artisan notifications:clear-old-read` — It runs daily (CRON). Checks users' personal settings and permanently deletes read notifications that have expired.
*   `php artisan simulate:adding-post` — Imitation of the activity of the authors. The console command parses the latest articles from [TechCrunch](https://techcrunch.com ), downloads content, titles, and covers (saving images locally to disk), and then generates new posts in the database.

---

## 🚀 Quick launch in Docker

### 1. Environment preparation
```bash
git clone https://github.com/AlexanderVlDubinin/project_it_blog.git
cd <project-folder-name>
cp .env.example .env
```
*Make sure that the connections in the `.env` file lead to container hosts: `DB_HOST=postgres`, `REDIS_HOST=redis`, `MAIL_HOST=mailpit'.*

### 2. Assembling and launching containers
The Node container will automatically pull up the 'npm install` dependencies and start the Vite server.
```bash
docker compose up -d --build
```

### 3. Rolling out demo data (Filling in the database)
The project has set up complex factories and siders (Users, Posts, Comments, Tags, Likes, Notifications):
```bash
# Installing backend dependencies
docker compose exec php composer install

# Key generation and migration with fake data
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --seed
```

---

## 🌐 Ports and access to services

| Service | Local URL / Address | Port in Docker |
| :--- | :--- | :--- |
| **Web App (Nginx)** | [http://localhost:8080](http://localhost:8080) | `80` |
| **The Filament Panel** | [http://localhost:8080/admin](http://localhost:8080/admin) | `80` |
| **Vite Dev Server** | [http://localhost:5173](http://localhost:5173) | `5173` |
| **Mailpit UI** | [http://localhost:8025](http://localhost:8025) | `8025` |
| **PostgreSQL** | `localhost:5432` | `5432` |
| **Redis** | `localhost:6379` | `6379` |

---

## 🧪 Testing (Pest + Playwright)

The Pest environment is used to run tests (including complex integration and browser scenarios via Playwright/Chromium). The Chromium system environment for Alpine is already configured inside the PHP container.

Running the entire test package (except browser tests):
```bash
docker compose exec php php vendor/bin/pest
```
Running browser tests (in the background, without launching the browser):
```bash
php artisan test tests/Browser/
```
Running browser tests (with browser launch):
```bash
$env:PWDEBUG=1; php artisan test tests/Browser/
```
after completing the browser tests and launching the browser, do:
```bash
$env:PWDEBUG=0
```

---

## 📂 Architectural features of the assembly
*   **Differentiation of rights:** The PHP container runs under the local user `laravel` with `UID:GID 1000:1000`, solving any `permission denied` problems on Linux hosts.
*   **Data persistence:** The folders `pgdata` (PostgreSQL) and `redisdata' (Redis) are placed in named Docker Volumes — posts, likes and cache will not disappear when `docker compose down' is called.
