# pa-v3 backend

pa-v3 的 Laravel backend，架在 AWS EC2（新加坡）+ Cloudflare Tunnel + Access 後面，對外網址 <https://nelsonlys.com>（Access OTP 登入）。前端 SPA 在另一個 repo，部署於 Cloudflare Workers：<https://pa-v3.15064719d.workers.dev>。

## Stack

- Laravel 13 / PHP 8.5 (php-fpm)
- MySQL 8.4
- Inertia v3 + React 19 + Tailwind CSS v4 + Vite 8

## 前端：Inertia + React

採用 Inertia 整合：route 直接渲染 React 頁面、直接傳 props，不需要另外寫 API。

- 進入點：`resources/js/app.jsx`（createInertiaApp）
- 根模板：`resources/views/app.blade.php`（`@vite` + `@inertia`）
- 頁面：`resources/js/Pages/`（如 `Home.jsx`）
- 共用元件：`resources/js/Components/`（如 `Clock.jsx`）

### 加一個新 React 頁面

1. `resources/js/Pages/About.jsx` 寫 `export default function About() { ... }`
2. `routes/web.php` 加一行：

   ```php
   Route::inertia('/about', 'About');
   // 要傳資料：
   Route::inertia('/about', 'About', ['name' => 'Nelson']);
   // 或從 controller：
   return Inertia::render('About', ['name' => 'Nelson']);
   ```

3. 在 server 上 build（server 無 dev server，改前端都要 build）：

   ```bash
   cd /var/www/html/nelson
   sudo npm run build
   ```

## 部署（server）

```bash
ssh nelson                # EC2 t4g.small, Ubuntu 26.04（server 已有自己的 GitHub key）
cd /var/www/html/nelson
sudo git pull
sudo php artisan config:clear   # 有動 config/.env 時
sudo npm run build              # 有動前端（jsx/css/blade 根模板）時
```
