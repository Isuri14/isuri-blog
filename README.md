# My Blog System

**Author:** K.H.I. Hansani  
**Student ID:** 235043E

A simple PHP + MySQL blog system with user registration/login and CRUD operations for blog posts. Built for learning purposes and assignment submission.

---

## Features
- User registration with password hashing (`password_hash`)
- Secure login with `password_verify`
- Create, Read, Update, Delete (CRUD) for blog posts
- Access control: users can only edit/delete their own posts
- Error handling and centralized logging (`errors/error_log.txt`)
- Prepared statements (prevents SQL injection)
- Basic UI with forms for register, login, add/edit/delete, post view
- Session management with secure logout

---

## Requirements
- XAMPP (Apache + MySQL) or equivalent local LAMP/WAMP stack
- PHP 7.4+ (mysqli recommended)
- A browser (Chrome, Firefox, Edge)
- (Optional) VS Code or any code editor

---

## Project Structure
