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
- **Railway Servers** (For Hosting Website)

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

### 4. (Optional) Change DB credentials
Edit `includes/db.php` if your MySQL username/password differ from the XAMPP default.

## Database Structure

### `users`
| Column     | Type         | Description              |
|------------|--------------|--------------------------|
| id         | INT PK AI    | User ID                  |
| fullname   | VARCHAR(100) | Full name                |
| email      | VARCHAR(100) | Unique email             |
| password   | VARCHAR(255) | Bcrypt hashed password   |
| created_at | TIMESTAMP    | Registration date        |

### `tasks`
| Column      | Type                              | Description          |
|-------------|-----------------------------------|----------------------|
| id          | INT PK AI                         | Task ID              |
| user_id     | INT FK → users.id                 | Owner                |
| title       | VARCHAR(200)                      | Task title           |
| description | TEXT                              | Optional details     |
| priority    | ENUM(low, medium, high)           | Priority level       |
| status      | ENUM(pending, in_progress, completed) | Task status      |
| due_date    | DATE                              | Optional due date    |
| created_at  | TIMESTAMP                         | Creation date        |
| updated_at  | TIMESTAMP                         | Last update          |

## Project Structure
```
task-tracker/
├── index.php           # Redirect entry point
├── login.php           # Login page
├── register.php        # Registration page
├── logout.php          # Session destroy
├── dashboard.php       # Main dashboard
├── tasks.php           # All Tasks + CRUD
├── database.sql        # Database setup script
├── includes/
│   ├── db.php          # Database connection
│   ├── auth.php        # Auth helper functions
│   └── sidebar.php     # Sidebar navigation
└── assets/
    └── css/
        └── style.css   # All styles
```

## CRUD Operations
- **Create** — Add Task modal with validation
- **Read** — View all tasks with search & filter
- **Update** — Edit Task modal (pre-filled fields)
- **Delete** — Delete with confirmation prompt
- **Mark Complete** — One-click status update
