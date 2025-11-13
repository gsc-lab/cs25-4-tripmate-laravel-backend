-- 0) DB가 없으면 생성
CREATE DATABASE IF NOT EXISTS tripmate
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

-- 사용할 DB
USE tripmate;

-- Users: 사용자 기본 정보
CREATE TABLE IF NOT EXISTS Users (
  user_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
  email_norm    VARCHAR(255) NOT NULL UNIQUE COMMENT '정규화 이메일(소문자/trim)',
  password_hash VARCHAR(255) NOT NULL,
  name          VARCHAR(50)  NULL COMMENT '표시용 이름(nickname)',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Region: 여행 지역(국가/도시 상위 분류)
CREATE TABLE IF NOT EXISTS Region (
  region_id    BIGINT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100) NOT NULL,
  country_code CHAR(2) NOT NULL COMMENT 'ISO-3166-1 alpha-2',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_region_country_code (country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PlaceCategory: 장소 카테고리
CREATE TABLE IF NOT EXISTS PlaceCategory (
  category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(64)  NOT NULL UNIQUE,
  name        VARCHAR(100) NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Place: 장소(외부/내부 혼합)
CREATE TABLE IF NOT EXISTS Place (
  place_id           BIGINT AUTO_INCREMENT PRIMARY KEY,
  category_id        BIGINT NOT NULL,
  name               VARCHAR(255) NOT NULL,
  address            VARCHAR(255) NOT NULL,
  lat                DECIMAL(10,7) NULL,
  lng                DECIMAL(10,7) NULL,
  external_provider  VARCHAR(32) NOT NULL DEFAULT 'google' COMMENT '외부 제공자',
  external_ref       VARCHAR(128) NULL COMMENT '외부 참조 ID',
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_place_category
    FOREIGN KEY (category_id) REFERENCES PlaceCategory(category_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_place_provider_ref (external_provider, external_ref),
  INDEX idx_place_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trip: 여행(유저 소유)
CREATE TABLE IF NOT EXISTS Trip (
  trip_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT NOT NULL,
  region_id   BIGINT NULL, -- 필요 시 NOT NULL로 정책 변경 가능
  title       VARCHAR(100) NOT NULL,
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  day_count   INT GENERATED ALWAYS AS (DATEDIFF(end_date, start_date) + 1) STORED,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_trip_user
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_trip_region
    FOREIGN KEY (region_id) REFERENCES Region(region_id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_trip_dates CHECK (start_date <= end_date),
  INDEX idx_trip_user_date (user_id, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TripDay: 여행 일차(1..day_count)
CREATE TABLE IF NOT EXISTS TripDay (
  trip_day_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  trip_id     BIGINT NOT NULL,
  day_no      INT NOT NULL COMMENT '1..day_count',
  memo        VARCHAR(255) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tripday_trip
    FOREIGN KEY (trip_id) REFERENCES Trip(trip_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_tripday_day_no CHECK (day_no >= 1),
  UNIQUE KEY uq_trip_day (trip_id, day_no),
  INDEX idx_tripday_trip (trip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ScheduleItem: 일차 내 일정 아이템
CREATE TABLE IF NOT EXISTS ScheduleItem (
  schedule_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  trip_day_id      BIGINT NOT NULL,
  place_id         BIGINT NULL,
  seq_no           INT NOT NULL COMMENT '일차 내 순번(1..n)',
  visit_time       DATETIME NULL, 
  memo             VARCHAR(255) NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_scheduleitem_tripday
    FOREIGN KEY (trip_day_id) REFERENCES TripDay(trip_day_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_scheduleitem_place
    FOREIGN KEY (place_id) REFERENCES Place(place_id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_scheduleitem_seq CHECK (seq_no >= 1),
  UNIQUE KEY uq_scheduleitem_seq (trip_day_id, seq_no),
  INDEX idx_scheduleitem_tripday (trip_day_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;