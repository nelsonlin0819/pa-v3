# pa-v3 backend

pa-v3 的 Laravel API backend，架在 AWS EC2（新加坡）+ Cloudflare Tunnel + Access 後面，對外網址 <https://nelsonlys.com>（Access OTP 登入）。前端 SPA 在另一個 repo，部署於 Cloudflare Workers：<https://pa-v3.15064719d.workers.dev>。

## Stack

- Laravel 13 / PHP 8.5 (php-fpm)
- MySQL 8.4
- Blade + Tailwind CSS v4 + Vite 8（含 React 管線）

## 前端：Blade 頁面

- 基底模板：`resources/views/layouts/app.blade.php`（載入 Vite 資源、footer）
- 新頁面：`resources/views/xxx.blade.php` 寫 `@extends('layouts.app')`，到 `routes/web.php` 加 route 回 `view('xxx', [...])`

## 前端：掛 React 頁面

React 管線已就緒（`vite.config.js` 已掛 `@vitejs/plugin-react`，範例 entry：`resources/js/react/app.jsx`）。步驟：

1. 在 `resources/js/react/` 新增 jsx entry，mount 到指定的 div id：

   ```jsx
   import { createRoot } from 'react-dom/client';

   const el = document.getElementById('your-mount-id');
   if (el) createRoot(el).render(<YourComponent />);
   ```

2. Blade 頁面放掛載點 `<div id="your-mount-id"></div>`，並把該 jsx 路徑加進 blade 的 `@vite([...])` 陣列（或 `vite.config.js` 的 `laravel({ input: [...] })`，兩者擇一即可）
3. 在 server 上 build：

   ```bash
   cd /var/www/html/nelson
   sudo npm run build
   ```

## 部署（server）

```bash
ssh -A nelson                # EC2 t4g.small, Ubuntu 26.04
cd /var/www/html/nelson
sudo git pull
sudo php artisan config:clear   # 有動 config/.env 時
sudo npm run build              # 有動前端（blade/jsx/css）時
```
