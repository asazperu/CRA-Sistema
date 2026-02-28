# Árbol de carpetas MVC propuesto

```text
CRA-Sistema/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ChatController.php
│   │   ├── DashboardController.php
│   │   └── InstallController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Conversation.php
│   │   └── Message.php
│   ├── Views/
│   │   ├── auth/
│   │   ├── chat/
│   │   ├── dashboard/
│   │   ├── install/
│   │   └── layouts/
│   ├── Services/
│   │   └── ChatAssistantService.php
│   ├── Middlewares/
│   │   └── AuthMiddleware.php
│   ├── Config/
│   │   └── routes.php
│   └── Core/
│       ├── App.php
│       ├── Router.php
│       ├── Database.php
│       ├── Auth.php
│       └── ...
├── public/
│   ├── index.php
│   ├── install.php
│   └── assets/
├── config/
│   └── app.php
├── documentos/
│   ├── 01-arbol-mvc.md
│   ├── 02-mapa-rutas.md
│   └── 03-esquema-mysql.md
├── cli/
│   └── install_check.php
└── database.sql
```
