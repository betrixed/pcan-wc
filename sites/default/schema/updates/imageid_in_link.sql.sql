ALTER TABLE links ADD imageid INT NULL DEFAULT NULL AFTER refid, 
ADD INDEX ix_links_image (imageid);