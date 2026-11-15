# COMP016 - Web Development Lab Activity 5

To-do list web app using PHP, PDO and MySQL (refactored from the Lab 4 session-based version).

## Setup
1. Start Apache and MySQL in Laragon.
2. Open HeidiSQL and run `database.sql` to create `task_db` and the `tasks` table.
3. Put this folder in `C:\laragon\www\` and open `http://localhost/WebDev-LabActivity5/`.
4. If your MySQL root account has a password, set it in `db.php`.

## Features
- Create, read, update (edit and toggle status) and delete tasks
- PDO prepared statements for every query
- State-changing actions use POST
- Output escaped with `htmlspecialchars()`
- External CSS (`style.css`) and JavaScript (`js/script.js`)
