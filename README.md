# UniLab — Research Project Management Platform

[🇮🇹 Versione italiana](README.it.md)

**Research project management platform built with Laravel**

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?logo=laravel&logoColor=white)
![REST API](https://img.shields.io/badge/REST-API-blue)
![SQLite](https://img.shields.io/badge/SQLite-Database-003B57?logo=sqlite&logoColor=white)

## Project Overview

**UniLab** is a web platform designed to support the management of academic and research projects.

The application provides tools for organizing projects, research teams, tasks, milestones, publications and deadlines within a centralized environment.

Different user roles are supported to manage access to project operations, while REST APIs provide programmatic access to selected application resources.

The project was developed as part of the **Web Programming** coursework at the University of Bari.

---

## Main Features

- Research project creation and management
- Project member and team management
- Role-based access control
- Task assignment and progress tracking
- Milestone and deadline management
- Academic publication management
- Comments and file attachments
- Project tagging
- User notifications
- Personal dashboard with project-related information
- CSV data export
- REST API with token-based authentication
- Asynchronous export operations

---

## User Roles

UniLab supports different roles with distinct responsibilities and permissions:

- **Principal Investigator (PI)** — manages research projects and their main resources
- **Manager** — supports project management and organization
- **Researcher** — participates in research activities and assigned projects
- **Collaborator** — contributes to project activities with limited permissions

Access to application features is restricted according to the user's role and project membership.

---

## Tech Stack

- **PHP 8.1+**
- **Laravel 10**
- **Laravel Sanctum**
- **Laravel Breeze**
- **Eloquent ORM**
- **Blade**
- **Tailwind CSS**
- **JavaScript**
- **Vite**
- **SQLite**
- **PHPUnit**

---

## Application Architecture

The project follows the standard Laravel MVC architecture:

```text
UniLab/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Jobs/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
├── composer.json
├── package.json
└── artisan
```

The application separates domain models, controllers, services and background jobs while using Laravel's routing system for both the web interface and REST APIs.

---

## Main Domain Entities

The application manages several interconnected entities, including:

- Users
- Projects
- Tasks
- Milestones
- Publications
- Authors
- Groups
- Comments
- Attachments
- Tags
- Notifications

Relationships between these entities are handled through Laravel's Eloquent ORM.

---

## REST API

UniLab includes a versioned REST API for accessing application resources.

API functionality includes operations related to:

- Authentication
- Users
- Projects
- Publications
- Data export

Protected endpoints use **Laravel Sanctum** for token-based authentication.

---

## Notifications and Background Operations

The platform includes a notification system for project-related events and deadlines.

Some operations, such as data exports, can also be handled asynchronously through Laravel jobs.

---

## Installation

Clone the repository:

```bash
git clone https://github.com/zSpiDa/UniLab.git
cd UniLab
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment configuration:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database in `.env`, then run the migrations:

```bash
php artisan migrate
```

Build the frontend assets:

```bash
npm run build
```

Start the application:

```bash
php artisan serve
```

---

## Running Tests

The project includes automated tests for application features, authentication and API functionality.

Run the test suite with:

```bash
php artisan test
```

---

## Academic Context

UniLab was developed as an academic project for the **Web Programming** course within the Bachelor's Degree in Computer Science and Digital Communication at the **University of Bari Aldo Moro**.

The project focuses on the design and implementation of a structured web application using Laravel, relational data modelling, role-based authorization, REST APIs and software engineering principles.

---

## Disclaimer

This project was developed for educational purposes.

It is intended as an academic demonstration of web application development and is not designed for production deployment without additional security, configuration and infrastructure work.
