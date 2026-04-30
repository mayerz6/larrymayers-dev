# LarryMayers Career Portfolio SaaS Application – Project Context

## 🎯 Goal

Build a production-grade, database-driven portfolio SaaS application that:

- Demonstrates full-stack engineering capability (PHP MVC + async JS)
- Acts as a lightweight CMS for managing structured career data
- Prioritizes clean architecture, testability, and extensibility
- Serves as a foundation for future SaaS products
- Showcase professional portfolio
- Deploy SQLite for persistence
- Provides an ADMIN dashboard for managing:
  - Messages (CRM)
  - Resume (CRUD)
  - Projects (CRUD)
  - Skills/Expertise (CRUD)
  - Certifications (CRUD)


Primary objective:
👉 Showcase senior-level engineering thinking, not just UI output

---

## 🧱 Architecture Rules

### State Management Rules

- Server (PHP + DB) is the source of truth
- Frontend state is temporary and must sync with backend
- sessionStorage is only for UX persistence (not authoritative state)

### Backend (PHP MVC)

- index.php = request orchestrator
- routes.php = route definitions
- logic.php = controllers
- Services = business logic (e.g., ResumeService)

### Frontend
- Progressive enhancement (forms work without JS)
- AJAX only enhances UX (not required for functionality)
- Avoid inline editing for now (use dedicated forms)

### Rules:
- Controllers MUST be thin
- No SQL inside views
- No business logic inside controllers
- All DB writes go through service layer

### Routing
- index.php is the orchestrator
- Routes defined in routes.php

---

## 🗄️ Database Design

### Core Tables

- resume
- duties
- projects
- technologies
- skills
- skill_categories
- certifications
- messages
- users

### Relationships

- resume (1) → (many) duties
- projects (many) ↔ (many) technologies
- skills (many) → (1) skill_categories

### Design Rules

- All tables use INTEGER PRIMARY KEY AUTOINCREMENT
- Use order_index for UI ordering
- Use foreign keys with ON DELETE CASCADE where appropriate
- No denormalized data duplication

---

## 🧪 Testing Strategy

### Testing Scope

- Services → Unit tested (core business logic)
- Database interactions → Tested with SQLite in-memory DB
- Controllers → Tested via integration tests
- Views → Not tested (presentation layer)

### Testing Goals

- Validate data integrity
- Validate transactional behavior
- Prevent regression in CRUD workflows


---

## 🚫 Anti-Patterns (STRICT)

- ❌ Inline editing (temporary restriction)
- ❌ Mixing JSON + HTML responses
- ❌ Business logic inside controllers
- ❌ Direct DOM manipulation without server sync

---

## 🔧 Dev Rules for Codex

- Follow existing routing structure
- Prefer service-based architecture
- Use prepared statements for DB
- Do NOT introduce frameworks

---


## ✅ Current Focus

- Stabilize Resume CRUD (create → delete → update via forms)
- Finalize authentication flow
- Ensure consistent server → UI data flow

👉 No new features until CRUD is stable

---

## 🚀 Future Goals (Priority Order)

1. AJAX partial rendering
2. Toast notifications
3. Role-based access control
4. API layer (REST endpoints)
5. Searchable resume system
6. Blog platform