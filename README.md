# hyperf-admin-php

基于 Hyperf 3.1 的管理后台后端基础框架（API），内置 JWT 登录鉴权、RBAC 权限（角色/菜单/按钮）、用户/角色/菜单管理、文件上传与多存储引擎（本地/阿里云 OSS/七牛/腾讯 COS）、配置中心（DB 存储）、统一参数校验与错误码规范等能力。

## Features

- 认证鉴权：JWT（`Authorization: Bearer <token>`）
- 权限控制：RBAC（角色/菜单/按钮）+ `#[Permission(code: ...)]` 注解
- 系统管理：用户/角色/菜单（与前端动态路由/按钮权限对接）
- 上传与存储：本地 + OSS/Qiniu/COS，多引擎可切换（配置存 DB）
- 统一响应：`{ code, msg, data }`
- 参数校验：`hyperf/validation` FormRequest（DTO/Request）模式，校验失败统一返回 `code=422`
- 运行端口：默认 `9503`

## Requirements

- PHP >= 8.1
- Swoole >= 5.0（`swoole.use_shortname=Off`）或 Swow >= 1.3
- MySQL / Redis

## Quick Start (Local)

1) 准备环境变量（不要提交 `.env`，仓库提供 `.env.example`）

```bash
cp .env.example .env
```

2) 安装依赖

```bash
composer install
```

3) 执行迁移（确保已创建数据库）

```bash
php bin/hyperf.php migrate
```

4) 启动服务

```bash
php bin/hyperf.php start
```

访问：`http://127.0.0.1:9503`

## Quick Start (Docker)

```bash
docker-compose up --build
```

端口：`9503`

## API & Conventions

### Auth

- 登录：`POST /api/auth/login`
- 用户信息：`GET /api/user/info`（需登录）

### RBAC

- 控制器通常挂载：
  - `AdminAuthMiddleware`：JWT 校验并写入 Context（`admin_user_id` / `admin_username`）
  - `PermissionMiddleware`：读取 `#[Permission(code: ...)]` 注解并校验权限码

### Validation (Request DTO)

已启用 `Hyperf\Validation\Middleware\ValidationMiddleware`，推荐控制器方法直接注入 Request DTO：

```php
public function login(\App\Admin\Request\Auth\LoginRequest $form)
{
    $data = $form->validatedData();
    // ...
}
```

校验失败返回示例：

```json
{
  "code": 422,
  "msg": "用户名 必须填写",
  "data": {
    "errors": {
      "username": ["用户名 必须填写"]
    }
  }
}
```

错误码常量：`app/Common/Enum/ErrorCode.php`（与 HTTP 状态语义对齐，但 HTTP 层统一返回 200）。

## Config

### CORS

`.env.example` 提供：

- `CORS_ALLOW_ORIGINS`：逗号分隔白名单；默认 `*`
- `CORS_ALLOW_CREDENTIALS`：默认 `false`；如需 cookies/credentials，请设置为 `true` 并配置明确域名白名单（不能使用 `*`）

### Upload

- `UPLOAD_MAX_SIZE_MB`：应用层文件大小兜底限制（默认 128）
- `UPLOAD_STRICT_MIME`：是否开启严格 MIME 校验（默认 false）

## Docs

- 详细说明：`doc.md`

## License

Apache-2.0

