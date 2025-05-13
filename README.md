# Medical & Event Management Web Application

## Overview
This is a robust medical and event management web application developed as part of the academic program at **Esprit School of Engineering**.  
It provides advanced features for managing users (patients/doctors), events, medical records, products, orders, posts, feedback, and analytics, all within a secure and scalable Symfony 6.4 framework.

## Features
- **User Management:** Registration, login, profile management, roles (patients, doctors, admin), secure authentication, password reset, Google/OAuth2 login
- **Event Management:** Event creation, editing, registration, event categories, statistics, calendar integration, event search
- **Medical Records:** Creation, consultation, and editing of medical records for patients and doctors
- **Appointment Scheduling:** Booking and managing appointments for patients and doctors
- **Product Management:** Product catalog, add/edit/delete products, advanced search, product categories, favorites, ratings
- **Order Management:** Shopping cart, order placement, order history and tracking
- **Posts & Comments:** Create and manage posts, comment system, post categories, favorites, ratings
- **Reclamations System:** Submit, track, and manage reclamations, manage types of reclamations, admin and user responses
- **Dashboard & Analytics:** Global statistics, charts, and indicators for users, events, products, and system activity
- **Calendar:** Interactive calendar for event and appointment visualization
- **Real-Time Notifications:** User notifications via Pusher integration
- **Chatbot:** Automated user assistance and support
- **PDF Generation:** Export data and documents as PDF files
- **Advanced Pagination & Search:** Efficient navigation and search for products, events, posts, etc.
- **File & Image Management:** Upload, display, and delete files/images
- **Category Management:** Manage categories for products, posts, and events
- **Profile Management:** Edit, view, and secure user profiles
- **Security:** Multi-role access control, secure workflows, email verification

## Tech Stack
- **Backend:** PHP 8+, Symfony 6.4, Doctrine ORM
- **Frontend:** Twig, Bootstrap (if used), JavaScript (Stimulus, UX Turbo)
- **Database:** MySQL/PostgreSQL
- **APIs & Integrations:** OAuth2, Pusher, Google Mailer, Twilio, QR Code, Calendar
- **Testing:** PHPUnit
- **Other:** Composer, KNP Paginator, Endroid QR Code, Guzzle

## Directory Structure
```
projectPI/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   ├── Service/
│   └── ...
├── templates/
│   ├── admin/
│   ├── dashboard/
│   ├── medecin/
│   ├── patient/
│   ├── produit/
│   ├── reclamation_controller_php/
│   └── ...
├── public/
├── config/
├── migrations/
└── ...
```

## Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL or PostgreSQL
- Node.js (for asset compilation, if required)

### Installation
```bash
git clone <repository-url>
cd projectPI
composer install
# Configure your .env with database credentials
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load # (if fixtures are provided)
php bin/console server:run
```

### Usage
- Access the application at `http://localhost:8000`
- Register as a patient or doctor, or log in as an admin
- Explore dashboard analytics, manage events, appointments, products, posts, and more

## Acknowledgments
This project was completed as part of the academic curriculum at **Esprit School of Engineering**.

## Topics
`esprit-school-of-engineering`, `symfony`, `medical-management`, `event-management`, `dashboard`, `php`, `doctrine`, `twig`, `web-app`, `calendar`, `notifications`, `pdf`, `oauth2`
