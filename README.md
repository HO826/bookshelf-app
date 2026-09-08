# COẠCHTECH BookShelf 書籍レビューアプリ

## 環境構築

**DockerビルドとLaravel環境構築**

1. リポジトリのクローン

`git clone https://github.com/HO826/bookshelf-app`

2. 環境変数ファイルの設定

`cp .env.example .env`
(※必要に応じて .env 内のデータベース設定やAPIキーなどを書き換えてください)

3. DockerDesktopアプリを立ち上げる

4. Laravel Sail（Dockerコンテナ）のビルドとバックグラウンド起動

docker run --rm \
 -u "$(id -u):$(id -g)" \
 -v "$(pwd):/var/www/html" \
 -w /var/www/html \
 laravelsail/php82-composer:latest \
 composer install

または、すでにコンテナがビルド済みの場合は以下のSailコマンドを利用します：

`./vendor/bin/sail up -d --build`

5. アプリケーションキーの生成

`./vendor/bin/sail artisan key:generate`

6. マイグレーションとシーディングの実行

`./vendor/bin/sail artisan migrate --seed`

7. サンプル環境変数 (.env)

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

GOOGLE_BOOKS_API_KEY=AIzaSyC5SeoGAzj7vH0p3s7QVXT7Okn1f5OaThY

## テストユーザー情報（シーディング）

マイグレーションと同時に投入されるシーダーにより、以下のテストユーザーでログインして動作確認が可能です。

氏名: 山田 太郎
email: yamada@example.com
password: password

## テストカバレッジの実行

sail artisan test --coverage
(※提出前のカバレッジは82%)

## 使用技術(実行環境)

- Windows (WSL2 / Ubuntu)
- PHP 8.x
- Laravel 10.x (Laravel Fortify搭載)
- MySQL8.4
- Docker, Laravel Sail, phpMyAdmin
- フロントエンド: Vite, Tailwind CSS ^3.4.0, @tailwindcss/formsを使用しての実装ですが、主にバックエンド中心の開発を行っておりました。

## ER図

![ER図](er.drawio.png)

## URL

- 開発環境：http://localhost
- phpMyAdmin:：http://localhost:8080/

## トラブルシューティング / 注意事項

### PHP 8.5 以降でのデータベース接続の警告について

PHP 8.5 環境でテストを実行した際、PDO の SSL 接続オプションに関する非推奨警告（`Constant PDO::MYSQL_ATTR_SSL_CA is deprecated`）が発生する場合は、`config/database.php` 内の `mysql` 設定を以下のように修正して対応しています。

**修正前 (`config/database.php`)**:

````php
'options' => extension_loaded('pdo_mysql') ? array_filter([
    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
]) : [],

**修正後 (`config/database.php`)**:

```php
'options' => extension_loaded('pdo_mysql') ? array_filter([
    \Pdo\Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
]) : [],
````
