# Online Examination System (PHP + MySQL)

## Description
An **Online Examination System** built using **PHP + MySQL**. It provides secure login for Admins and Students, supports subject management, MCQ-based exams, timed tests, and instant result generation. Includes **role-based access control, pagination, and input sanitization** for industry-standard security and scalability.

## Features
- Admin: Manage subjects, questions, students, and view results
- Student: Attempt randomized exams with timer, submit answers, and view results instantly
- Secure Authentication & Session Handling
- Responsive UI (Bootstrap/Tailwind)

## Database Schema
Tables: `admins`, `students`, `subjects`, `questions`, `results`

## Setup
1. Clone repository  
   ```bash
   git clone https://github.com/your-username/online-exam-system.git
   cd online-exam-system

Import SQL_schema.sql into MySQL

Configure the database in app_config.php

Run with XAMPP/WAMP server

Access http://localhost/online-exam-system
