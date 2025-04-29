

Minisched

v0.1 

2025-4-28 着手


## 開発用アカウント
ローカルのみ有効なユーザー kenzy	nekoneko




# Laravel10で従来の開発環境を構築 | XAMPP, jQuery, ログインまわり（Breeze）

## 前提
- Windows 環境
- XAMPPがインストールされ、Apacheが使用できる状態
- Composerがインストール済み
- Node.jsは今回の手順では不要
- データベースはsqliteまたはMySQLが利用可能

## 手順詳細

### 1. XAMPPでApacheを起動しておく
XAMPPのコントロールパネルから「Apache」をStartします。これでローカルサーバーが使える状態になります。

### 2. 任意の場所にプロジェクトのディレクトリを作成
```bash
mkdir minisched
cd minisched
```
プロジェクトのベースディレクトリを作成し、ここにLaravelファイルを配置します。

### 3. Laravel10.3.3のプロジェクトを作成
```bash
composer create-project laravel/laravel:^10.3.3 dev
cd dev
```
"dev"というサブディレクトリにLaravelをインストールします。

### 4. Breezeパッケージをインストール（ログイン機能）
```bash
composer require laravel/breeze --dev
```
シンプルなログイン・登録機能を持つBreezeを導入します。

### 5. 最初のマイグレーションを実行
```bash
php artisan migrate
```
標準のusersテーブルなどを作成します。

### 6. BreezeをBlade版でインストール
```bash
php artisan breeze:install blade
```
Bladeテンプレートエンジンを利用したシンプルな認証UIをインストールします。

### 7. Breeze導入後、再度マイグレーション
```bash
php artisan migrate
```
認証関連のテーブルをDBに反映させます。

### 8. データベース設定について
- **sqliteを使う場合**：`.env`編集不要
- **MySQLを使う場合**：`.env`に以下を記述
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=データベース名
  DB_USERNAME=root
  DB_PASSWORD=
  ```

### 9. シンボリックリンクを作成してXAMPP配下にパスを通す（必要な場合）
管理者モードのPowerShellで実行：
```bash
cmd /c mklink /D C:\xampp\htdocs\minisched C:\Users\user\git\minisched
```
仮想的にリンクを作り、localhostからアクセスできるようにします。

### 10. ブラウザでトップページにアクセス
[http://localhost/minisched/dev/public/](http://localhost/minisched/dev/public/)  
Laravelの初期画面が表示されれば成功です。

### 11. 登録画面からユーザー登録・ログイン
[http://localhost/minisched/dev/public/register](http://localhost/minisched/dev/public/register)  
適当な情報でユーザー登録して、ログインできることを確認します。

---

## まとめ図
```
[XAMPP起動]
      ↓
[プロジェクト作成 → minisched/dev]
      ↓
[Breezeインストール → migrate2回]
      ↓
[シンボリックリンク設定（必要時）]
      ↓
[http://localhost/minisched/dev/public/ にアクセス]
      ↓
[registerからユーザー登録＆ログイン確認]
```

---

## メモ
- Breezeは簡単なサンプル認証には最適
- シンボリックリンクは複数案件対応に便利
- `php artisan migrate` は頻繁に使うので習得必須

