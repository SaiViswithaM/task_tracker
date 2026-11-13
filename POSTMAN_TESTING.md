# Postman quick start

1. Start Apache and MySQL in XAMPP.
2. Make sure `task_tracker` is inside your XAMPP `htdocs` folder.
3. Import `database.sql` into MySQL once.
4. In Postman, import both JSON files from `postman/`.
5. Select environment **Task Tracker Local**.
6. Run requests in this order:
   - 1 - Get CSRF Token
   - 2 - Register (first run only) OR 3 - Login (later runs)
   - 4 - List My Tasks
   - 7 - Create Task
   - 8 - Update Task
   - 9 - Update Task Status
   - 10 - Delete Task
   - 11 - Logout

Postman should retain the `PHPSESSID` cookie automatically. Use `localhost` consistently in every request. The collection stores the CSRF token in the environment after the CSRF, registration or login request.

For repeat testing, change the environment `email` and `password` values to an account you registered. Passwords in this local Postman environment are examples only; do not use a real password.
