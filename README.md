# Approval Workflow System

![Docker](https://img.shields.io/badge/Docker-✓-blue)
![MySQL](https://img.shields.io/badge/MySQL-✓-blue)
![Laravel](https://img.shields.io/badge/Laravel-✓-red)

A hierarchical approval workflow system with department-based routing.

## 🚀 Features

- Multi-level approval chains
- Department-specific workflows
- Request tracking with status history
- JWT Authentication
- Dockerized environment

## ⚙️ System Requirements

- Docker 20.10+

## 🛠 Installation

1. Clone and configure:
   ```bash
   git clone https://github.com/yourrepo/approval-workflow.git
   cd approval-workflow
   cp .env.example .env

2. Start services
```docker-compose up -d --build```

## ERD Diagram

The Entity-Relationship Diagram (ERD) for the database schema is shown below:

This ERD represents the relationships between users, requests, approval_flows, and approval_steps.

![ERD Diagram](./Initial%20ERD%20(No%20Department).png)

This ERD represents the relationships between users, departments, requests, approval_flows, and approval_steps.

![ERD Diagram](./Enhanced%20ERD%20(With%20Departments).png)

## API Documentation

The API documentation is available through the Postman collection provided in this repository. It covers all the endpoints for managing users, departments, approver hierarchy setup, request submission and tracking, and approval/rejection actions.

## API Endpoints

### 🔐 Authentication
| Method | Endpoint            | Description                     |
|--------|---------------------|---------------------------------|
| POST   | `/auth/login`       | Login user (get JWT token)      |
| POST   | `/auth/register`    | Register new user               |
| POST   | `/auth/logout`      | Invalidate current token        |

### 👥 User Management
| Method | Endpoint            | Description                     |
|--------|---------------------|---------------------------------|
| GET    | `/users`            | List all users                  |
| GET    | `/users/{id}`       | Get user details                |
| PATCH  | `/users/{id}`       | Update user                     |
| DELETE | `/users/{id}`       | Deactivate user                 |

### 🏢 Department Management
| Method | Endpoint              | Description                     |
|--------|-----------------------|---------------------------------|
| GET    | `/departments`        | List all departments            |
| POST   | `/departments`        | Create new department           |
| GET    | `/departments/{id}`   | Get department details          |
| PATCH  | `/departments/{id}`   | Update department               |
| DELETE | `/departments/{id}`   | Deactivate department           |

### ↔️ Approval Workflows
| Method | Endpoint                  | Description                     |
|--------|---------------------------|---------------------------------|
| GET    | `/approval-flows`         | List approval hierarchies       |
| POST   | `/approval-flows`         | Add approver to hierarchy       |
| PATCH  | `/approval-flows/{id}`    | Update approver level           |
| DELETE | `/approval-flows/{id}`    | Remove from hierarchy           |

### 📝 Request Management
| Method | Endpoint                  | Description                     |
|--------|---------------------------|---------------------------------|
| GET    | `/requests`               | List requests (filterable)      |
| POST   | `/requests`               | Submit new request              |
| GET    | `/requests/{id}`          | Get request details             |
| GET    | `/requests/{id}/steps`    | Get approval history            |

### ✅ Approval Actions
| Method | Endpoint                       | Description                     |
|--------|--------------------------------|---------------------------------|
| POST   | `/requests/{id}/approve`       | Approve request                 |
| POST   | `/requests/{id}/reject`        | Reject request                  |

## Postman Collection

The Postman collection file (Approval Workflow System.postman_collection.json) is included in the repository. Import this collection into Postman to test the API.

## Testing

In the command line, run
```
php artisan test
```