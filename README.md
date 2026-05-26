# TaskFlow — Task Tracking System

A dynamic, database-driven web application for tracking tasks. Built with PHP, MySQL, HTML, and CSS.

## Features

- **Authentication** — Register, Login, Logout with secure password hashing
- **Dashboard** — Overview stats, recent tasks, upcoming due dates
- **Full CRUD** — Add, Edit, Delete, and Mark tasks as Complete
- **Search & Filter** — Filter by status, priority, or keyword search
- **Priority Levels** — Low, Medium, High
- **Status Tracking** — Pending, In Progress, Completed
- **Overdue Detection** — Visual alerts for past-due tasks
- **Responsive Design** — Works on desktop and mobile

## Technologies Used

- **PHP** (server-side logic, sessions, prepared statements)
- **MySQL** (database via XAMPP)
- **HTML5 / CSS3** (structure and styling)
- **JavaScript** (modal interactions, client-side UX)
- **RailWay** (Website Host)

## Run Website
- **enki.up.railway.app** — Website Link

## Setup Instructions (XAMPP)

### 1. Copy files
Place the `task-tracker` folder inside:
```
C:/xampp/htdocs/task-tracker/
```

### 2. Import the database
1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open `http://localhost/phpmyadmin`
3. Click **New** → create database named `task_tracker_db`
4. Click **Import** → upload `database.sql` → click **Go**

### 3. Run the app
Open your browser and go to:
```
http://localhost/task-tracker/
```
- **Read** — View all tasks with search & filter
- **Update** — Edit Task modal (pre-filled fields)
- **Delete** — Delete with confirmation prompt
- **Mark Complete** — One-click status update
