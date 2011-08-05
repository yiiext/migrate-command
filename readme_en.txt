Extended Migration Command
==========================

Documentation coming soon...


notes:

if you already used MigrateCommand make sure to add the column

 ALTER TABLE `tbl_migration` ADD COLUMN `module` varchar(32) DEFAULT NULL;
 UPDATE `tbl_migration` SET module='core';
