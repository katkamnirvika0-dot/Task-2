# Professional Blog System

A clean, modern, and professional blog system built with PHP and MySQL.

## Features

### Core Features
- 🔐 User Authentication (Login/Register)
- 📝 Create, Read, Update, Delete Posts
- 👤 User Dashboard
- 🎨 Professional Light Theme

### Advanced Features (Task 3)
- 🔍 Search Functionality
- 📄 Pagination System
- 📱 Responsive Design
- 🎯 Professional UI/UX

## Technology Stack
- **Frontend**: HTML5, CSS3 (Custom Professional Theme), JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Icons**: Bootstrap Icons
- **Font**: Inter Font Family

## Design Features
- Clean, minimal design
- Professional color scheme
- Consistent spacing and typography
- Smooth animations and transitions
- Mobile-first responsive design
- Accessible UI components

## Installation

1. **Setup Database**
```sql
CREATE DATABASE blog;
USE blog;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);