-- This file is automatically generated using maintenance/generateSchemaChangeSql.php.
-- Source: extensions/GeoData/sql/abstractSchemaChanges/patch-postgres-externally-backed.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
DROP  INDEX gt_page_primary;
DROP  INDEX gt_page_id_id;
CREATE TEMPORARY TABLE /*_*/__temp__geo_tags AS
SELECT  gt_id,  gt_page_id,  gt_globe,  gt_primary,  gt_lat,  gt_lon,  gt_dim,  gt_type,  gt_name,  gt_country,  gt_region
FROM  /*_*/geo_tags;
DROP  TABLE  /*_*/geo_tags;
CREATE TABLE  /*_*/geo_tags (    gt_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,    gt_page_id INTEGER UNSIGNED NOT NULL,    gt_globe VARCHAR(32) NOT NULL,    gt_primary SMALLINT NOT NULL,    gt_lat NUMERIC(11, 8) NOT NULL,    gt_lon NUMERIC(11, 8) NOT NULL,    gt_dim INTEGER DEFAULT NULL,    gt_type VARCHAR(32) DEFAULT NULL,    gt_name BLOB DEFAULT NULL,    gt_country CHAR(2) DEFAULT NULL,    gt_region VARCHAR(3) DEFAULT NULL  );
INSERT INTO  /*_*/geo_tags (    gt_id, gt_page_id, gt_globe, gt_primary,    gt_lat, gt_lon, gt_dim, gt_type, gt_name,    gt_country, gt_region  )
SELECT  gt_id,  gt_page_id,  gt_globe,  gt_primary,  gt_lat,  gt_lon,  gt_dim,  gt_type,  gt_name,  gt_country,  gt_region
FROM  /*_*/__temp__geo_tags;
DROP  TABLE /*_*/__temp__geo_tags;
CREATE INDEX gt_page_primary ON  /*_*/geo_tags (gt_page_id, gt_primary);
CREATE INDEX gt_page_id_id ON  /*_*/geo_tags (gt_page_id, gt_id);