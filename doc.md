# Hyperf Admin 基础框架说明（后端 API）

本项目基于 Hyperf 3.1 构建，提供一套「管理后台」常用能力的基础后端：JWT 登录鉴权、RBAC 权限（角色/菜单/按钮）、用户/角色/菜单管理、文件上传与多存储引擎（本地/阿里云OSS/七牛/腾讯COS）、配置表存储等。

## 1. 技术栈与依赖

- 运行时：PHP >= 8.1 + Swoole(>=5) 或 Swow(>=1.3)
- 框架：Hyperf（HTTP Server / DI / Database / Redis / Cache / Async Queue 等）
- 数据库：MySQL（`config/autoload/databases.php`）
- 缓存/队列：Redis（`config/autoload/redis.php`、`config/autoload/async_queue.php`）
- 鉴权：`phper666/jwt-auth` + `firebase/php-jwt`（`config/autoload/jwt.php`）
- 查询过滤：`2515104337/hyperf-eloquent-filter`（`config/autoload/eloquent_filter.php`）
- 对象存储：阿里云 OSS / 七牛 / 腾讯 COS SDK（`composer.json`）

## 2. 启动方式（本地）

1) 配置环境变量：复制并修改 `.env`（参考 `.env.example`）
2) 安装依赖：

```bash
composer install
```

3) 执行迁移（需要数据库已创建）：

```bash
php bin/hyperf.php migrate
```

4) 启动服务：

```bash
php bin/hyperf.php start
```

- HTTP 端口：默认 `9503`（见 `config/autoload/server.php`）

## 3. 目录结构（核心）

- `app/Admin/Controller/*`：管理后台 API（使用 PHP8 Attribute 路由注解）
- `app/Admin/Middleware/*`：后台中间件（CORS / JWT 鉴权 / 权限校验）
- `app/Common/Model/*`：业务模型（System/File 等）
- `app/Common/Service/*`：业务服务层（Auth/System/Menu/Role/User/Upload/Storage 等）
- `app/Common/Annotation/Permission.php`：权限注解（接口权限码）
- `app/Common/Helper/ResponseHelper.php`：统一 JSON 响应结构
- `app/Exception/Handler/*`：异常处理（业务异常/兜底异常）
- `config/*`：框架与组件配置（server/db/redis/jwt/middlewares 等）
- `migrations/*`：数据库迁移（用户/角色/菜单/权限关联/配置/文件等）

## 4. 请求链路与约定

### 4.1 路由加载

- `config/autoload/annotations.php` 配置扫描路径为 `BASE_PATH . '/app'`
- 管理后台接口主要通过 Attribute 声明路由，例如：
  - `app/Admin/Controller/Auth/AuthController.php`：`#[Controller(prefix: '/api')]` + `#[PostMapping(...)]`

### 4.2 全局中间件

- `config/autoload/middlewares.php` 目前仅注册：
  - `App\Admin\Middleware\CorsMiddleware`

### 4.3 鉴权与上下文

- `App\Admin\Middleware\AdminAuthMiddleware`：
  - 从 `Authorization` Header 读取 token（支持 `Bearer xxx`）
  - 使用 `Phper666\JWTAuth\JWT` 校验 token
  - 将用户信息写入上下文：
    - `Context::set('admin_user_id', ...)`
    - `Context::set('admin_username', ...)`
- `App\Admin\Controller\BaseController` 提供：
  - `getUserId()` / `getUsername()`
  - `getParams()` / `getParam()`

### 4.4 统一响应

- `App\Common\Helper\ResponseHelper` 返回 JSON：
  - 成功：`{ code: 200, msg: "success", data: ... }`
  - 失败：`{ code: 4xx/5xx, msg: "...", data: ... }`

### 4.5 异常处理

- `App\Common\Exception\BusinessException`：用于可预期业务错误（默认 code=400）
- `config/autoload/exceptions.php` HTTP 异常处理顺序：
  1. `App\Exception\Handler\BusinessExceptionHandler`（业务异常，返回 code/message）
  2. `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler`
  3. `App\Exception\Handler\AppExceptionHandler`（兜底，记录日志并返回 500）

注意：两个 handler 都以 HTTP 200 返回，但 JSON 内 `code` 表示业务状态码（见 `app/Exception/Handler/*`）。

## 5. RBAC 权限模型（角色 / 菜单 / 按钮）

### 5.1 数据表与关系（见 `migrations/*`）

- `admin_users`：后台用户（软删）
- `roles`：角色（软删）
- `menus`：菜单与按钮（软删）
  - `type`: 1=目录，2=菜单，3=按钮
  - `permission`: 按钮/接口权限标识（如 `user:add`）
- `admin_user_roles`：用户-角色关联
- `role_menus`：角色-菜单关联

### 5.2 权限注解与校验

- `App\Common\Annotation\Permission`：用于标记接口所需权限码
- `App\Admin\Middleware\PermissionMiddleware`：
  - 读取当前路由对应 Controller/Method
  - 从方法注解提取 `Permission(code: ...)`
  - 从缓存获取用户权限数据并校验
- `App\Common\Service\System\PermissionCacheService`：
  - 缓存键：`user_permissions:{userId}`，默认 TTL=3600s
  - 超级管理员角色：硬编码为 `R_SUPER`

### 5.3 典型用法

- 控制器上统一挂载中间件：
  - `#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]`
- 需要权限的接口增加注解：
  - `#[Permission(code: 'user:add')]`

## 6. 管理后台核心接口（按模块）

以下均为 Attribute 路由（以代码为准）：

- 登录/用户信息：
  - `POST /api/auth/login`（`app/Admin/Controller/Auth/AuthController.php`）
  - `GET /api/user/info`（需登录）
- 用户管理：
  - `GET /api/user/list`、`GET /api/user/{id}`、`POST /api/user`、`PUT /api/user/{id}`、`DELETE /api/user/{id}`
  - `GET/PUT /api/user/profile`、`PUT /api/user/password`
  - 入口：`app/Admin/Controller/System/UserController.php`
- 角色/菜单管理：
  - 入口：`app/Admin/Controller/System/RoleController.php`、`app/Admin/Controller/System/MenuController.php`
- 文件上传：
  - `POST /api/upload/image|video|file`（`app/Admin/Controller/File/UploadController.php`）
- 存储设置：
  - `GET /api/setting/storage/list|detail`
  - `POST /api/setting/storage/setup|change`
  - 入口：`app/Admin/Controller/Setting/StorageController.php`

## 7. 文件上传与存储引擎

- 上传入口：`App\Common\Service\UploadService`
  - 按扩展名校验类型（`App\Common\Enum\FileEnum`）
  - 通过 `App\Common\Service\Storage\Driver` 选择当前存储引擎并上传
  - 上传后写入 `file` 表（`App\Common\Model\File\File`）
- 存储引擎：
  - `local` / `aliyun` / `qiniu` / `qcloud`（`app/Common/Service/Storage/Driver.php`）
- 存储配置：
  - 存在 `config` 表（`migrations/2024_01_15_000001_create_config_table.php`）
  - `Driver::getDefaultEngine()` / `getEngineConfig()` 基于 `Config::getValue()` 读取

## 8. 异步队列与进程

- Async Queue 配置：`config/autoload/async_queue.php`（Redis Driver）
- 消费进程：`app/Process/AsyncQueueConsumer.php`（`#[Process]`）

## 9. 开发工具与质量保障

- 代码风格：`php-cs-fixer`（`.php-cs-fixer.php`），命令：`composer cs-fix`
- 静态分析：`phpstan`（`phpstan.neon.dist`），命令：`composer analyse`
- 测试：`phpunit`（`phpunit.xml.dist`），命令：`composer test`

## 10. 参数校验（hyperf/validation，FormRequest 模式）

项目已引入并启用 `hyperf/validation` 的 `ValidationMiddleware`（见 `config/autoload/middlewares.php`），推荐统一使用 `FormRequest`/DTO 来做接口参数校验，减少控制器手写校验逻辑。

- 基类：`app/Common/Request/ApiFormRequest.php`（默认 `authorize()=true`，提供 `validatedData()`）
- 示例：
  - 登录：`app/Admin/Request/Auth/LoginRequest.php`
  - 创建用户：`app/Admin/Request/System/UserCreateRequest.php`
  - 更新用户：`app/Admin/Request/System/UserUpdateRequest.php`
  - 修改密码：`app/Admin/Request/System/UpdatePasswordRequest.php`
  - 角色：`app/Admin/Request/System/RoleCreateRequest.php`、`app/Admin/Request/System/RoleUpdateRequest.php`、`app/Admin/Request/System/RoleUpdateMenusRequest.php`
  - 菜单：`app/Admin/Request/System/MenuSaveRequest.php`

控制器用法（示例）：

```php
public function login(LoginRequest $form): ResponseInterface
{
    $data = $form->validatedData();
    // ...
}
```

校验失败会抛出 `Hyperf\Validation\ValidationException`，由 `app/Exception/Handler/ValidationExceptionHandler.php` 统一转成业务 JSON 返回（`code=422`，`data.errors` 为详细字段错误）。

## 10.1 错误码规范（业务 code）

错误码常量集中在 `app/Common/Enum/ErrorCode.php`，并与 HTTP 状态码语义对齐（但 HTTP 层仍统一返回 200）：

- `200`：成功
- `400`：业务错误/参数错误（`BusinessException` 默认）
- `401`：未登录/未授权
- `403`：无权限
- `422`：参数校验失败（validation）
- `500`：服务器内部错误

## 11. 框架层面的改进建议（优先级从高到低）

1) CORS 策略建议  
已调整为「Origin 白名单动态回显」：通过环境变量 `CORS_ALLOW_ORIGINS`（逗号分隔）控制允许来源；如需携带 cookie/credentials，设置 `CORS_ALLOW_CREDENTIALS=true` 并配置明确域名（此时不会再返回 `*`）。

2) Docker/端口一致性  
当前已统一为 `9503`（`config/autoload/server.php`、`Dockerfile`、`docker-compose.yml`、`.devcontainer/*`、`deploy.test.yml`），后续改端口时建议全局一起改，避免容器启动但访问不到。

3) JWT 放行路由策略  
已将 `config/autoload/jwt.php` 的 `no_check_route` 收敛为仅放行登录与 `OPTIONS` 预检，避免未来接入 jwt-auth 默认中间件时“默认全放行”。

4) 状态字段语义统一  
`admin_users.status` 语义为 `1启用/2禁用`（整型），已修正 `PermissionCacheService` 中的 boolean 写法为 `status=1`，建议后续在业务代码中保持一致。

5) 上传安全与可控性增强  
`UploadService` 已补充应用层文件大小限制（`UPLOAD_MAX_SIZE_MB`）与文件名清理；如需更严格可开启 `UPLOAD_STRICT_MIME=true`（可能会因客户端差异误判）。

6) 接口参数校验与错误码规范化  
已引入 `hyperf/validation`（FormRequest 模式）并新增 `ErrorCode` 常量（`app/Common/Enum/ErrorCode.php`），建议后续把剩余接口逐步迁移为「Request DTO + service」的形式，减少手写校验与魔法数字。

7) 权限缓存失效策略进一步完善  
当前已在用户变更角色、角色变更菜单时清理缓存（见 `UserService`、`RoleService`）。建议在“菜单变更（影响角色菜单）/按钮权限变更”场景补充对相关角色用户的缓存清理，或采用版本号/事件驱动的方式做统一失效。

8) 可观测性与追踪  
建议增加：
  - request-id（链路日志关联）
  - 结构化日志字段（userId/path/latency）
  - 慢 SQL/慢请求日志（仅在 dev/staging 开启）

9) 文档与接口描述自动化  
建议补充 OpenAPI/Swagger（或 apifox 导出规范），并把权限码（`Permission` 注解）与菜单按钮 permission 对齐规则写入文档，减少“按钮权限码/接口权限码不一致”问题。
