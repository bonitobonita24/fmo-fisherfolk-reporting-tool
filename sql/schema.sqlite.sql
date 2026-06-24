-- Fisherfolk Management System Database Schema (SQLite)
-- Created for Calapan City FMO by Powerbyte IT Solutions
-- SQLite port of sql/schema.sql

CREATE TABLE IF NOT EXISTS fisherfolk (
    id_number          TEXT PRIMARY KEY,
    full_name          TEXT NOT NULL,
    date_of_birth      TEXT,
    address            TEXT,
    sex                TEXT,
    image              TEXT,
    signature          TEXT,
    rsbsa              TEXT,
    contact_number     TEXT,
    boat_owneroperator INTEGER DEFAULT 0,
    capture_fishing    INTEGER DEFAULT 0,
    gleaning           INTEGER DEFAULT 0,
    vendor             INTEGER DEFAULT 0,
    fish_processing    INTEGER DEFAULT 0,
    aquaculture        INTEGER DEFAULT 0,
    date_registered    TEXT DEFAULT CURRENT_TIMESTAMP,
    date_updated       TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_address       ON fisherfolk (address);
CREATE INDEX IF NOT EXISTS idx_sex           ON fisherfolk (sex);
CREATE INDEX IF NOT EXISTS idx_date_of_birth ON fisherfolk (date_of_birth);

-- Emulate MySQL's "ON UPDATE CURRENT_TIMESTAMP" for date_updated
CREATE TRIGGER IF NOT EXISTS trg_fisherfolk_updated
AFTER UPDATE ON fisherfolk
FOR EACH ROW
BEGIN
    UPDATE fisherfolk SET date_updated = CURRENT_TIMESTAMP WHERE id_number = OLD.id_number;
END;
