-- ============================================================
-- PHO Budgeting System — 2026 Consolidated Budget Proposal
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `pho_budgeting`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `pho_budgeting`;

-- Drop existing table if re-running this script
DROP TABLE IF EXISTS `budget_proposals`;

CREATE TABLE `budget_proposals` (
  `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,

  -- Program & Account Details
  `program_project`       VARCHAR(255)    NOT NULL COMMENT 'Program / Project / Activity',
  `account_code`          VARCHAR(100)    NOT NULL,
  `account_title`         VARCHAR(255)    NOT NULL,
  `performance_indicator` TEXT            NOT NULL,

  -- Physical Targets (Quarterly)
  `q1_target`             INT UNSIGNED    NOT NULL DEFAULT 0,
  `q2_target`             INT UNSIGNED    NOT NULL DEFAULT 0,
  `q3_target`             INT UNSIGNED    NOT NULL DEFAULT 0,
  `q4_target`             INT UNSIGNED    NOT NULL DEFAULT 0,
  `total_target`          INT UNSIGNED    NOT NULL DEFAULT 0,

  -- Financial Allocation (Monthly — stored as DECIMAL for money)
  `jan`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `feb`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `mar`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `apr`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `may`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `jun`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `jul`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `aug`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `sep`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `oct`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `nov`                   DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `dec_amt`               DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `total_allocation`      DECIMAL(15,2)   NOT NULL DEFAULT 0.00,

  -- Classifications
  `unit`                  ENUM('PHO CLINIC','ADMINISTRATIVE SUPPORT','ORAL HEALTH PROGRAM','PESU')
                          NOT NULL,
  `expense_class`         ENUM('MOOE','CAPITAL OUTLAY','PERSONAL SERVICES')
                          NOT NULL,
  `fund_source`           ENUM('GENERAL FUND','SPECIAL PROJECT')
                          NOT NULL,
  `lbp_code`              VARCHAR(100)    NOT NULL DEFAULT '',
  `justification`         TEXT            NOT NULL,

  -- Metadata
  `created_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_unit`          (`unit`),
  INDEX `idx_expense_class` (`expense_class`),
  INDEX `idx_fund_source`   (`fund_source`),
  INDEX `idx_created`       (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
