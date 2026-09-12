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
*   **Fullstack Engine:** Livewire `v4.4.4` (used for dynamic tables and "Show more" pagination)
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
    *   For comments: likes and dislikes with the calculation of the total live rating (total rating = `Likes` minus `Dislikes`).
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

## 📊 Public Dashboard (`/dashboard`)

The application implements a single dynamic Dashboard based on **Livewire v4.4.4** components. The page content is fully adapted to one of the **4 user roles**, providing each with a unique operational hub.

All interactive tables are equipped with reactive AJAX pagination **"Show more"** (Show more), displays a counter for the total number of records and hides the button when the data is fully loaded.

### 👤 Role: User (Regular reader)
* **Personal activity statistics:** Card with automatic calculation:
* Number of liked posts.
    * The total number of comments left.
    * The number of liked and disliked comments.
    * **The overall rating of user comments** (live balance: `Likes - Dislikes`).
* **Recommendation system:** A table of the **5 most popular posts** in the system (sorted strictly by the highest number of likes).

### ✍️ Role: Author (Content Author)
* Includes **all the features of the User role**.
* **"My posts" section:** A personal table displaying exclusively the articles of this author. Allows to see both published and hidden (drafts) posts with the ability to quickly switch to editing them.

### 🛡 Role: Moderator (Operational Moderation Center)
* **Queue of hidden posts:** A table with all posts in the `soft deleted` status. For each element, a quick access button is displayed to the Filament admin panel for final moderation.
* **Queue of hidden comments:** The table of comments located in the `soft delete'. It is equipped with buttons to go to the public page of the post to a specific place (anchor) of the comment. If the comment post has the status of `soft deleted`, then the button leads to the post's Filament admin panel for final moderation.
*   **Potentially toxic content:** A list of the lowest-rated comments on the site (which have gone into deep dislike) for rapid response and cleaning blogs from spam /toxicity.

### 👑 Role: Admin (Strategic Control Center)
* **Registration control:** A table of the last registered users in the system with the possibility of instant access to their cards inside the Filament.
* **Content Import monitoring:** An interactive list of posts that were automatically parsed and simulated by the console team from the *[TechCrunch](https://techcrunch.com )* website. Allows the administrator to visually assess the stability of the parser.

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
*   **Data persistence:** The folders `pgdata` (PostgreSQL) and `redisdata` (Redis) are placed in named Docker Volumes — posts, likes and cache will not disappear when `docker compose down` is called.
*   **Reactive UI (Livewire):** All interactive tables on the public Dashboard ("Show more" pagination, dynamic counting of the total number of entries) are implemented on Livewire components. This allows you to update data on the fly using AJAX requests without completely reloading the page and without writing cumbersome JS code.
