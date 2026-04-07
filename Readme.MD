# 🧺 LarryMayers Portfolio Platform (Hybrid MVC + SPA Architecture)

## 📌 Overview

This project is a custom-built portfolio and application platform designed using a **hybrid architecture**:

* 🧠 **PHP (MVC-style)** for routing, controllers, and server-side rendering
* ⚡ **Vanilla JavaScript (Async/AJAX)** for dynamic UI updates and SPA-like behavior

The goal is to create a **lightweight, framework-free web application** that combines:

* The simplicity of PHP
* The responsiveness of modern front-end apps

---

## 🏗️ Architecture

### 🔹 Core Concept

This application follows a **Hybrid MVC + SPA pattern**:

```
Client (Browser)
   ↓
JavaScript (AJAX / Fetch)
   ↓
PHP Router (index.php)
   ↓
Controller Logic (logic.php)
   ↓
View Rendering (templates)
```

---

## 📁 Project Structure

```
/core
  /controllers
    routes.php        # Route definitions
    logic.php         # Controller functions

  /databases
    Database.php
    messages.sqlite

  /security
    Secrets.php

/templates
  /regions
    header.php
    header-nav.php
    footer.php

  home.php
  about.php
  contact.php
  projects.php
  resume.php

index.php              # Main application entry point
```

---

## 🚦 Routing System

Routes are defined using a **method + path key format**:

```php
'GET /projects' => ['handler' => 'projects'],
'POST /contact' => ['handler' => 'contactPost'],
```

### 🔹 Flow

1. Request enters `index.php`
2. URI + method are parsed
3. Route is matched from `$routes`
4. Corresponding controller function is executed

---

## 🎮 Controller Layer (`logic.php`)

Each route maps to a controller function:

```php
function projects(): void {
    view('projects');
}
```

Controllers are responsible for:

* Handling request logic
* Passing data to views
* Returning JSON (for API-like responses)

---

## 🖼️ View System

Views are rendered using a shared layout:

```php
function view(string $view_name, array $data = []): void {
    extract($data);
    require header;
    require nav;
    require view;
    require footer;
}
```

This ensures:

* Consistent layout
* Reusable components
* Clean separation of concerns

---

## ⚡ Async / SPA Behavior

Although the app uses PHP rendering, it supports **SPA-like navigation** via JavaScript:

* Fetch content asynchronously
* Inject HTML into the DOM
* Avoid full page reloads

Example:

```js
fetch('/projects')
  .then(res => res.text())
  .then(html => {
    document.getElementById('app').innerHTML = html;
  });
```

---

## 🔁 Method Override Support

Supports REST-like behavior via POST:

```php
$_POST['_method'] = 'PUT';
```

or

```
X-HTTP-Method-Override: PUT
```

---

## 📬 Contact Form (Example API Behavior)

```php
function contactPost(): void {
    echo json_encode(["message" => "Success"]);
}
```

Returns JSON responses for async handling.

---

## 🚀 Future Enhancements

* [ ] Dedicated `/api/*` route namespace
* [ ] Authentication system
* [ ] Admin dashboard for content management
* [ ] Cartana price comparison engine integration
* [ ] Full client-side routing (history.pushState)

---

## 🧠 Design Philosophy

This project intentionally avoids heavy frameworks to:

* Demonstrate core engineering fundamentals
* Maintain full control over architecture
* Showcase custom-built solutions

---

## 👨🏽‍💻 Author

**Larry Mayers**
Data Engineer | Web Developer | System Designer

---

## 💡 Final Note

This is not just a portfolio — it is a **living system** designed to evolve into:

> A data-driven platform combining web engineering, analytics, and real-world applications like Cartana.
