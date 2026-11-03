# Advanced Web Technologies Laboratory

**Student:** Manish Singh  
**Email:** [manisskng@gmail.com](mailto:manisskng@gmail.com)  
**Roll Number:** 23/CS/244  
**Academic Year:** 2025–2026

This repository contains the live demonstrations and source code for the experiments performed during the Advanced Web Technologies laboratory course.

## Live Demo

The static experiment showcase is available at:

**https://manikng.github.io/Adv-Web-Tech-LAB-DTU/**

The PHP-based experiments run best on a local PHP server because static hosting platforms do not execute server-side PHP.

```powershell
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

## Experiments

| Experiment | Topic | Live Entry | Key Learnings and Learning Intention |
|---|---|---|---|
| 1 | Departmental Details | [Open](experiments/exp1/index.html) | Learned how HTML structures a university departmental webpage and how CSS improves readability and presentation. I intended to understand semantic elements, page organization, links, images, and the basics of creating a polished static website. |
| 2 | CSS Flexbox and Grid Layouts | [Open](experiments/exp2/index.html) | Learned to build responsive layouts using Flexbox, CSS Grid, external stylesheets, icons, and modern visual design. I intended to understand how layout systems adapt content across screen sizes without unnecessarily complicated positioning. |
| 3 | JavaScript Validation and DOM Manipulation | [Open](experiments/exp3/index.html) | Learned event handling, regular expressions, real-time form validation, DOM updates, and user feedback. I intended to move from static forms to interactive client-side experiences that prevent invalid submissions and give immediate guidance. |
| 4 | XML and XSL Course Catalogue | [Open](experiments/exp4/courses.xml) | Learned how structured XML data can be separated from its presentation and rendered through an XSL stylesheet. I intended to understand document structure, XML namespaces, transformations, and browser-based XML display. |
| 5 | Dynamic PHP Student Result Management | [Open](experiments/exp5/index.php) | Learned server-side rendering, database abstraction, PHP APIs, CRUD operations, aggregate calculations, and CSV export. I intended to understand how a dynamic application separates configuration, persistence, business logic, and presentation while applying useful interaction and validation principles. |
| 6 | PHP and MySQL Database CRUD | [Open](experiments/exp6/index.php) | Learned database connectivity, prepared SQL statements, CRUD operations, filtering, sorting, analytics, and fallback database handling. I intended to build a safer and more reliable full-stack application while understanding the relationship between PHP and relational databases. |
| 7 | Sessions, Cookies, and Authentication | [Open](experiments/exp7/index.php) | Learned registration, password hashing, login sessions, persistent cookies, CSRF protection, logout handling, session fixation prevention, and inactivity expiry. I intended to understand how HTTP state is managed securely and how multiple security controls work together in an authentication system. |

## Technology Overview

- HTML5 and semantic page structure
- CSS3, Flexbox, Grid, and responsive design
- JavaScript and DOM manipulation
- XML and XSL
- PHP server-side application development
- PDO and SQLite/MySQL database connectivity
- REST-style PHP APIs and JSON communication
- Secure authentication and session management

## Repository Structure

```text
.
├── index.html          # Main experiment showcase
├── css/                # Styles for the main showcase
├── js/app.js           # Experiment cards and iframe navigation
├── experiments/
│   ├── exp1/           # HTML and CSS
│   ├── exp2/           # Flexbox and Grid
│   ├── exp3/           # JavaScript validation
│   ├── exp4/           # XML and XSL
│   ├── exp5/           # Dynamic PHP application
│   ├── exp6/           # PHP and MySQL CRUD application
│   └── exp7/           # Authentication with sessions and cookies
└── README.md
```

## Running the Static Experiments

HTML, CSS, JavaScript, and XML experiments can be opened directly in a browser or served using any static web server.

For all experiments, including PHP, the recommended setup is a local PHP server from the repository root:

```powershell
cd d:\7-sem\advWebTechRepo
php -S localhost:8000
```

## Author

**Manish Singh**  
Roll Number: **23/CS/244**  
Email: **manisskng@gmail.com**