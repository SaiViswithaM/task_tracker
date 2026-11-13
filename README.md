# Task Tracker (Core PHP + MySQL)

A responsive task management application built with core PHP, PDO, MySQL/MariaDB, HTML, CSS and JavaScript.

## Features

- User registration and login
- Password hashing with `password_hash()` and verification with `password_verify()`
- Create, view, edit and delete tasks
- Task statuses: Pending, In Progress and Completed
- Search tasks by title and filter by status
- Dashboard counts for all four task states
- Server-side validation
- PDO prepared statements
- Output escaping with `htmlspecialchars()`
- CSRF tokens on state-changing forms (including logout)
- Per-user task ownership checks in the backend
- Responsive layout

## Requirements

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.1+ recommended (PHP 8.2+ ideal)
- VS Code or another editor
- A modern browser

## Setup on Windows / XAMPP

1. Extract the `task_tracker` folder into your XAMPP `htdocs` directory. Example:
   `C:\xampp1\htdocs\task_tracker`
   (If your XAMPP folder is `C:\xampp`, use `C:\xampp\htdocs\task_tracker` instead.)
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open phpMyAdmin, usually `http://localhost/phpmyadmin`.
4. Select **Import**, choose this project's `database.sql`, and click **Import**. The script creates the `task_tracker` database and its tables.
5. Open `config/database.php`. The defaults are:
   - Host: `localhost`
   - Database: `task_tracker`
   - Username: `root`
   - Password: empty
   Change these values if your local MySQL setup differs.
6. Visit `http://localhost/task_tracker/` in your browser.
7. Choose **Create account**, register with your own email and a password of at least 8 characters, then start adding tasks.

## Test login details

There is no hard-coded test account. Register a local account through the registration page. This avoids shipping a known password or a pre-created account.

## Roles and permissions

This project uses a single authenticated role: **User**.
- A guest can access only the login and registration pages.
- A logged-in user can create, list, edit and delete only their own tasks.
- The backend includes the current session's user ID in task SELECT, UPDATE and DELETE queries. Changing a task ID in the URL or POST data cannot grant access to another user's task.
- There is no admin role because it is not required by the assignment.

## Database design

- `users`: stores account name, unique email, password hash and creation timestamp.
- `tasks`: stores each task and its `user_id` foreign key. The foreign key references `users.id` and cascades task deletion if an account is deleted.
- `tasks.status` is an ENUM restricted to the three assignment statuses.
- Indexes support filtering tasks by user/status and ordering by creation time.

## Manual test cases

| Test | Steps | Expected result |
|---|---|---|
| Registration | Register with valid name, email and password | Account created and dashboard opens |
| Duplicate email | Register again using the same email | Friendly duplicate-email error |
| Login | Log in with correct credentials | Dashboard opens |
| Invalid login | Enter incorrect password | Generic login error; no account details disclosed |
| Add task | Add a title, description and status | Task appears in the list |
| Empty title | Submit task form with blank title | Server rejects it with a validation message |
| Edit task | Edit a task and save | Updated values appear |
| Status filter | Select a status | Only matching tasks appear |
| Search | Search for part of a title | Matching tasks appear |
| Delete task | Click Delete and confirm | Task is removed |
| Cancel delete | Click Delete and cancel confirmation | Task remains |
| Guest access | Log out and visit `dashboard.php` directly | Redirected to login |
| Ownership | Create two accounts; try account A's task ID while logged in as B | Task cannot be viewed, edited or deleted by B |
| Password storage | Inspect `users.password` in phpMyAdmin | Hash is stored, not the original password |
| CSRF | Submit a state-changing form without a valid token | Request is rejected |

Run these tests manually in two separate browser sessions (or one normal and one private window) to verify ownership isolation.

## Security notes

- All database statements that use user input use PDO prepared statements.
- User-visible task content is escaped before rendering.
- Passwords are never stored in plaintext.
- CSRF tokens are checked for POST requests that change state.
- Task ownership is checked by the backend, not merely hidden in the UI.
- In production, use HTTPS, secure cookie settings, environment-based database credentials, rate limiting and a production web server configuration. Do not publish local credentials.

## Backup and restore

**Backup:** In phpMyAdmin, select the `task_tracker` database, choose **Export**, select SQL format and download the export. Alternatively, use `mysqldump` from a terminal:
```bash
mysqldump -u root -p task_tracker > task_tracker_backup.sql
```

**Restore:** Create/select the database and import the SQL file through phpMyAdmin, or run:
```bash
mysql -u root -p task_tracker < task_tracker_backup.sql
```
Keep backups in a secure location and test restoration periodically.

## Completion note

- Level 1: Completed — task create, list, edit and delete.
- Level 2: Completed — status, search, filter and backend/frontend validation.
- Level 3: Completed — registration, login, password hashing, session protection, ownership authorization, prepared statements, output escaping and CSRF protection.
- Bonus: Dashboard summary counts included.
- Known limitations: No password reset, email verification, pagination or admin role. This is an educational local assignment project, not a production-hardened service.

## AI coding assistant disclosure

AI coding assistance: ChatGPT was used to help scaffold and document the project. Review the code before submission and make a meaningful change yourself. Example review change: inspect and test the ownership condition in every task query (`WHERE id = ? AND user_id = ?`) and adjust the UI or validation based on your own testing. Replace this example with the exact change you personally made after reviewing the generated code.


## Postman API testing

This project includes PHP JSON endpoints in the `api/` directory and an importable Postman collection.

### Import into Postman

1. Start Apache and MySQL in XAMPP.
2. Import `database.sql` into MySQL (phpMyAdmin is optional; you can also use the MySQL command line).
3. Open Postman.
4. Select **Import** and import `postman/Task_Tracker_API.postman_collection.json`.
5. Import `postman/Task_Tracker_Local.postman_environment.json` as an environment.
6. Select the `Task Tracker Local` environment in Postman.
7. The default `base_url` is `http://localhost/task_tracker`. Change it if your folder or Apache port differs.

### Important: session cookies and CSRF

The API uses PHP session authentication. Postman normally stores and resends the `PHPSESSID` cookie automatically for the same host.

1. Run **1 - Get CSRF Token** first. It creates a PHP session and returns `csrf_token`.
2. Run **2 - Register** or **3 - Login**. Both require the CSRF token header. The collection updates the saved token after a successful response.
3. After successful registration/login, Postman stores the session cookie. Keep using the same host (`localhost`, not a mix of `127.0.0.1` and `localhost`).
4. Run task requests. The collection sends `X-CSRF-Token` automatically for state-changing requests.
5. Run **9 - Logout** to end the session.

If Postman does not retain the cookie, open **Cookies** in Postman, select `localhost`, and confirm that the `PHPSESSID` cookie exists. Do not manually share session cookies with anyone.

### API endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/csrf.php` | Start session and get CSRF token |
| POST | `/api/register.php` | Register and log in the new user |
| POST | `/api/login.php` | Log in |
| GET | `/api/tasks.php` | List own tasks; optional `search` and `status` query parameters |
| POST | `/api/task_create.php` | Create a task |
| POST | `/api/task_update.php` | Update task title, description and status |
| POST | `/api/task_status.php` | Update only task status |
| POST | `/api/task_delete.php` | Delete a task |
| POST | `/api/logout.php` | Log out |

### Example JSON bodies

Register:
```json
{"name":"Test User","email":"test@example.com","password":"TestPass123"}
```

Login:
```json
{"email":"test@example.com","password":"TestPass123"}
```

Create task:
```json
{"title":"Complete PHP assignment","description":"Test the Task Tracker API","status":"Pending"}
```

Update task:
```json
{"id":1,"title":"Finish PHP assignment","description":"Updated description","status":"In Progress"}
```

Update status:
```json
{"id":1,"status":"Completed"}
```

Delete task:
```json
{"id":1}
```

For all POST requests, set `Content-Type: application/json`. For registration, login, create, update, status, delete and logout, send `X-CSRF-Token` with the token returned by `/api/csrf.php` or by a successful login/registration response.

### Import database without phpMyAdmin

If the MySQL command-line client is available, open Command Prompt and run:
```bash
cd C:\xampp1\mysql\bin
mysql -u root -p < C:\xampp1\htdocs\task_tracker\database.sql
```
If your root account has no password, press Enter when prompted. Adjust paths to match your XAMPP installation. Alternatively, use the SQL tab in phpMyAdmin; phpMyAdmin is only a database administration interface, not a requirement for Postman.


### XAMPP MySQL port setting

The supplied `config/database.php` is configured for port `3305`, matching the common custom XAMPP setup used in this project. If your XAMPP Control Panel shows MySQL on port `3306`, change `$port = 3305` to `$port = 3306` in `config/database.php`. Keep the port consistent when importing the SQL database from the command line.
