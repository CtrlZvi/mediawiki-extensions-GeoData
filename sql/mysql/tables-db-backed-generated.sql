-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/GeoData/sql/abstract/tables-db-backed.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/geo_tags (
  gt_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  gt_page_id INT UNSIGNED NOT NULL,
  gt_globe VARCHAR(32) NOT NULL,
  gt_primary TINYINT(1) NOT NULL,
  gt_lat_int SMALLINT NOT NULL,
  gt_lon_int SMALLINT NOT NULL,
  gt_lat NUMERIC(11, 8) NOT NULL,
  gt_lon NUMERIC(11, 8) NOT NULL,
  gt_dim INT DEFAULT NULL,
  gt_type VARCHAR(32) DEFAULT NULL,
  gt_name VARCHAR(255) DEFAULT NULL,
  gt_country CHAR(2) DEFAULT NULL,
  gt_region VARCHAR(3) DEFAULT NULL,
  INDEX gt_page_primary (gt_page_id, gt_primary),
  INDEX gt_page_id_id (gt_page_id),
  INDEX gt_spatial (
    gt_lat_int, gt_lon_int, gt_lon, gt_primary
  ),
  PRIMARY KEY(gt_id)
) /*$wgDBTableOptions*/;
