app/
├── Admin/
│   ├── Controller/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── User/
│   │   │   └── UserController.php
│   │   ├── Role/
│   │   │   └── RoleController.php
│   │   ├── Menu/
│   │   │   └── MenuController.php
│   │   └── Dashboard/
│   │       └── DashboardController.php
│   │
│   ├── Middleware/
│   │   ├── AdminAuthMiddleware.php
│   │   └── PermissionMiddleware.php
│   │
│   └── Routes/
│       ├── auth.php
│       ├── user.php
│       ├── role.php
│       ├── menu.php
│       └── dashboard.php
│
├── Common/
│   ├── Model/
│   ├── Service/
│   ├── Helper/
│   └── Exception/
│
routes/
├── admin.php
├── api.php
