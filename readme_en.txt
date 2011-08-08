Extended Migration Command
--------------------------

This extension is an enhanced version of the [Yii Database Migration Tool](http://www.yiiframework.com/doc/guide/1.1/en/database.migration)
that adds module support and many more usefull features. If there is anything you would like to have added, or you found a bug
please [report it](http://code.google.com/p/yiiext/issues/list) on google code or [contact me](mailto:mail@cebe.cc) via email.

Features
--------

* Module-Support (migrations are distributed in a seperate folder for every module) so you can...
 * ...enable and disable Modules
 * ...add new module by migrating it up
 * ...remove a module by running its migrations down
 * ...select the modules you want to run migrations for in every run
 * ...declare Module-dependencies (coming soon)
 * ...different migration templates depending on modules (coming soon)

Resources
---------

* [SVN](http://code.google.com/p/yiiext/source/browse/trunk/app/extensions/yiiext/commands/migrate)
* [Yii Database Migration Documentation](http://www.yiiframework.com/doc/guide/1.1/en/database.migration)
* [Discuss](http://www.yiiframework.com/forum/)
* [Report a bug](http://code.google.com/p/yiiext/issues/list)

Requirements
------------

* Yii 1.1.6 or above (MigrateCommand was introduced in this version)
if you copy MigrateCommand and [CDbMigration](http://www.yiiframework.com/doc/api/1.1/CDbMigration) you should be able to use this
extension with any yii version.

Installation
------------

* Extract the release file under `protected/extensions`.
* Add the following to your config file for yiic command:
~~~
[php]
	'commandMap' => array(
        'migrate' => array(
            // alias of the path where you extracted the zip file
            'class' => 'application.extensions.yiiext.commands.migrate.EMigrateCommand',
            // this is the path where you want your core application migrations to be created
            'migrationPath' => 'application.db.migrations',
            // the name of the table created in your database to save versioning information
            'migrationTable' => 'tbl_migration',
	        // the application migrations are in a pseudo-module called "core" by default
            'applicationModuleName' => 'core',
	        // define all available modules
	        'modulePaths' => array(
		        'admin'      => 'application.modules.admin.db.migrations',
		        'user'       => 'application.modules.user.db.migrations',
		        'yourModule' => 'application.any.other.path.possible',
		        // ...
	        ),
	        // here you can configrue which modules should be active, you can disable a module by adding its name to this array
	        'disabledModules' => array(
	            'admin', 'anOtherModule', // ...
	        ),
	        // the name of the application component that should be used to connect to the database
            'connectionID'=>'db',
            // alias of the template file used to create new migrations
            'templateFile'=>'application.db.migration_template',
        ),
    ),
~~~
Please note: if you already used MigrateCommand before, make sure to add the module column to your migrationTable:
~~~
[sql]
 ALTER TABLE `tbl_migration` ADD COLUMN `module` varchar(32) DEFAULT NULL;
 UPDATE `tbl_migration` SET module='core';
~~~

Usage
-----

* run `yiic migrate help` to see all parameters and how to use them
* some more documentation and usage examples will be provided here in a few days

