TripMate Backend API
Laravel基盤の旅行計画管理RESTful APIサーバー
📋 プロジェクト概要
TripMateは、ユーザーが旅行日程を体系的に計画・管理できるよう支援するバックエンドAPIサービスです。旅行計画の作成、日程管理、場所検索およびスケジューリング機能を提供します。
👥 チームメンバー
<table>
  <tr>
    <td align="center">
      <img src="https://github.com/dgk99.png" width="100"/><br/>
      <b>キム・ミンギュ</b><br/>
      チームリーダー<br/>
      <a href="https://github.com/dgk99">@dgk99</a>
    </td>
    <td align="center">
      <img src="https://github.com/jammmin02.png" width="100"/><br/>
      <b>パク・ジョンミン</b><br/>
      メンバー<br/>
      <a href="https://github.com/jammmin02">@jammmin02</a>
    </td>
    <td align="center">
      <img src="https://github.com/dayeon2423004.png" width="100"/><br/>
      <b>キム・ダヨン</b><br/>
      メンバー<br/>
      <a href="https://github.com/dayeon2423004">@dayeon2423004</a>
    </td>
  </tr>
</table>
🛠 技術スタック

Framework: Laravel 11
Language: PHP 8.2+
Database: MySQL 8.0
Authentication: Laravel Sanctum
API Documentation: L5-Swagger (OpenAPI 3.0)
Testing: PHPUnit
Code Quality: PHPStan, Laravel Pint
Container: Docker, Docker Compose
External API: Google Maps Platform (Places API)

✨ 主要機能
1. ユーザー認証

会員登録・ログイン
トークンベース認証 (Sanctum)
ユーザー情報照会および削除

2. 旅行計画管理 (Trip)

旅行の作成・照会・修正・削除
旅行期間および地域設定
ユーザー別旅行リスト照会

3. 日程管理 (TripDay)

旅行日次別日程作成
日程メモ作成
日程順序の再配置

4. スケジュールアイテム (ScheduleItem)

日次別詳細日程追加
訪問時間設定
場所連携
順序再配置
地点間距離計算機能

5. 場所管理 (Place)

Google Places API連携
場所検索（オートコンプリート、近隣場所）
座標ベースの逆ジオコーディング
場所情報の保存および照会

6. 地域情報 (Region)

国・都市情報提供
旅行地域分類

🌍 外部API連携 - Google Maps Platform
Places API (Web Service)

外部場所データの収集・検索
外部場所データを内部Placeテーブルに保存して再利用
オートコンプリート、近隣検索、詳細情報取得機能

公式ドキュメント

https://developers.google.com/maps/documentation/places/web-service/overview?hl=ja

主要API機能

Autocomplete: リアルタイム場所検索候補提供
Place Search: テキスト・座標基準の場所検索
Place Details: 場所の詳細情報取得
Nearby Search: 特定位置周辺の場所検索
Geocoding/Reverse Geocoding: 住所⇔座標変換

📐 ScheduleItem 距離計算
実装方式

緯度(lat) / 経度(lng) 基準の距離計算
Haversine（ハーバーサイン）公式 使用
日程間の移動距離推定値算出

Haversine公式
地球を完全な球体と仮定し、2つの座標点間の最短距離（大圏距離）を計算します。
a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
c = 2 ⋅ atan2(√a, √(1−a))
d = R ⋅ c

φ: 緯度（ラジアン）
λ: 経度（ラジアン）
R: 地球の半径（約6,371km）

活用

スケジュールアイテム間の移動距離自動計算
効率的な旅行ルート計画支援
1日の移動距離推定

参考資料

https://link2me.tistory.com/1831

📂 プロジェクト構造
cs25-4-tripmate-laravel-backend-develop/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/      # APIコントローラー
│   │   │   ├── Requests/          # Requestバリデーション
│   │   │   └── Resources/         # APIリソース変換
│   │   ├── Models/                # Eloquentモデル
│   │   ├── Repositories/          # データアクセスレイヤー
│   │   ├── Services/              # ビジネスロジック
│   │   │   └── Trip/
│   │   │       └── DistanceHelper.php  # 距離計算ヘルパー
│   │   ├── Swagger/               # APIドキュメント
│   │   └── Traits/                # 共通Trait
│   ├── database/
│   │   ├── migrations/            # DBマイグレーション
│   │   ├── seeders/               # 初期データ
│   │   └── data/                  # Seedデータ (JSON)
│   ├── routes/
│   │   └── api.php                # APIルート定義
│   └── tests/                     # テストコード
├── docker/
│   ├── nginx/                     # Nginx設定
│   └── php/                       # PHP-FPM Dockerfile
├── db/
│   └── tripmate_schema.sql        # DBスキーマ定義
└── docker-compose.yml             # Docker環境設定
🚀 はじめに
必須要件

Docker & Docker Compose
Git

インストールおよび実行

リポジトリクローン

bashgit clone [repository-url]
cd cs25-4-tripmate-laravel-backend-develop

環境変数設定

bashcd backend
cp .env.example .env
.envファイルでデータベースおよびその他の設定を行います：
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=tripmate
DB_USERNAME=root
DB_PASSWORD=your_password

# Google Maps API Key
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

Dockerコンテナ実行

bashcd ..
docker-compose up -d

依存関係のインストールおよび初期化

bashdocker exec -it backend-laravel bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed

サーバー接続


APIサーバー: http://localhost:9000
APIドキュメント: http://localhost:9000/api/documentation

📡 APIエンドポイント
認証 (Authentication)

POST /api/v2/users - 会員登録
POST /api/v2/auth/login - ログイン
POST /api/v2/auth/logout - ログアウト（認証必要）

ユーザー (Users)

GET /api/v2/users/me - 現在のユーザー情報
DELETE /api/v2/users/me - ユーザー削除

旅行 (Trips)

GET /api/v2/trips - 旅行リスト
POST /api/v2/trips - 旅行作成
GET /api/v2/trips/{id} - 旅行詳細
PUT /api/v2/trips/{id} - 旅行修正
DELETE /api/v2/trips/{id} - 旅行削除

日程 (Trip Days)

GET /api/v2/trips/{trip_id}/days - 日程リスト
POST /api/v2/trips/{trip_id}/days - 日程作成
PATCH /api/v2/trips/{trip_id}/days/{day_no} - メモ修正
DELETE /api/v2/trips/{trip_id}/days/{day_no} - 日程削除
POST /api/v2/trips/{trip_id}/days/reorder - 順序変更

スケジュールアイテム (Schedule Items)

GET /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items
POST /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items
PATCH /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{id}
PUT /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{id}
DELETE /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{id}
POST /api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/reorder

場所 (Places)

GET /api/v2/places/autocomplete - オートコンプリート検索
GET /api/v2/places/external-search - 外部API検索
GET /api/v2/places/reverse-geocode - 逆ジオコーディング
GET /api/v2/places/nearby - 近隣場所検索
POST /api/v2/places/from-external - 外部場所保存
GET /api/v2/places/{id} - 場所詳細

地域 (Regions)

GET /api/v2/regions - 地域リスト

🧪 テスト
bash# 全体テスト実行
docker exec -it backend-laravel php artisan test

# Featureテストのみ実行
docker exec -it backend-laravel php artisan test --testsuite=Feature

# 特定テストファイル実行
docker exec -it backend-laravel php artisan test tests/Feature/Auth/LoginTest.php
🔍 コード品質
コードスタイル検査および自動修正
bash# Pintでコードスタイル検査
composer lint

# 自動修正
./vendor/bin/pint
静的解析
bash# PHPStan実行
composer stan

# Baseline生成
composer stan:baseline
🗄 データベーススキーマ
主要テーブル

Users: ユーザー情報
Region: 旅行地域（国・都市）
PlaceCategory: 場所カテゴリー
Place: 場所情報
Trip: 旅行計画
TripDay: 旅行日次
ScheduleItem: 日程アイテム

ERDおよび詳細スキーマは /db/tripmate_schema.sql を参照
主要リレーション
Users (1) --< (N) Trips
Trips (1) --< (N) TripDays
TripDays (1) --< (N) ScheduleItems
ScheduleItems (N) >-- (1) Places
Trips (N) >-- (1) Regions
Places (N) >-- (1) PlaceCategories
📝 開発規則
Repository-Serviceパターン

Repository: データアクセスロジック担当
Service: ビジネスロジック担当
Controller: リクエスト・レスポンス処理

APIレスポンス形式
json{
  "status": "success",
  "data": { /* レスポンスデータ */ },
  "message": "成功メッセージ"
}
エラーレスポンス：
json{
  "status": "error",
  "message": "エラーメッセージ",
  "errors": { /* バリデーションエラー詳細 */ }
}
🔧 環境変数
主要環境変数リスト：
envAPP_NAME=TripMate
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=tripmate
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:9000

# Google Maps API
GOOGLE_MAPS_API_KEY=your_api_key_here
📚 APIドキュメント
Swagger UIによる対話型APIドキュメント：

URL: http://localhost:9000/api/documentation
OpenAPI 3.0スペック基準
リアルタイムAPIテスト可能

🐳 Dockerサービス

nginx: Webサーバー（ポート9000）
backend: Laravelアプリケーション
db: MySQL 8.0（ポート3307）
