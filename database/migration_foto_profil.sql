USE bengkel_db;

ALTER TABLE users
ADD COLUMN foto VARCHAR(255) NULL AFTER level;
