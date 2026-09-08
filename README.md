# COẠCHTECH BookShelf 書籍レビューアプリ

## 環境構築

**Dockerビルド**

1. `git clone git@github.com:HO826/coachtech-.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**

1. `docker-compose exec php bash`
2. `composer install`

```text

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:GxVCTMZ4oRqZPBtKxiKmayD73365llZT6SNhPMZw09A=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password


DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

STRIPE_SECRET=your_stripe_secret_key_here
STRIPE_KEY=your_stripe_public_key_here

XDEBUG_MODE=coverage
```

5. アプリケーションキーの作成

```bash
php artisan key:generate
```

6. マイグレーションの実行

```bash
php artisan migrate
```

7. シーディングの実行

```bash
php artisan db:seed
```

## 使用技術(実行環境)

- Windows (WSL2 / Ubuntu)
- PHP 8.x
- Laravel 8.83.29 (Laravel Fortify搭載)
- Stripe (決済機能)
- MySQL8.0.26

## ER図

![ER図](frm.png)

## URL

- 開発環境：http://localhost:8081/
- phpMyAdmin:：http://localhost:8080/
