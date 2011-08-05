<?php

Yii::import('system.cli.commands.MigrateCommand');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'EDbMigration.php');

/**
 * EMigrateCommand manages the database migrations.
 *
 * This class is an extension to yiis db migration command.
 *
 * It adds the following features:
 *  - module support
 *    you can create migrations in different modules
 *    so you are able to disable modules and also having their
 *    database tables removed/never set up
 *    yiic migrate down 1000 --module=examplemodule
 *
 *  - module dependencies (planned, not yet implemented)
 *
 * @link http://www.yiiframework.com/doc/guide/1.1/en/database.migration
 * @author Carsten Brandt <mail@cebe.cc>
 * @version 0.3.0
 */
class EMigrateCommand extends MigrateCommand
{
	/**
	 * @var array list of all modules
	 * array(
	 *      'modname' => 'application.modules.modname.db.migrations',
	 * )
	 */
	public $modulePaths = array();

	/**
	 * @var array list of disabled modules
	 * array(
	 *      'examplemodule1',
	 *      'examplemodule2',
	 *      ...
	 * )
	 */
	public $disabledModules = array();

	/**
	 * @var string|null the current module(s) to use for current command (comma separated list)
	 * defaults to null which means all modules are used
	 * examples:
	 * --module=core
	 * --module=core,user,admin
	 */
	public $module;

	/**
	 * @var string the application core is handled as a module named 'core' by default
	 */
	public $applicationModuleName = 'core';

	/**
	 * @var string delimiter for modulename and migration name for display
	 */
	public $moduleDelimiter = ': ';

	protected $migrationModuleMap = array();

	/**
	 * prepare paths before any action
	 *
	 * @param $action
	 * @param $params
	 * @return bool
	 */
	public function beforeAction($action, $params)
	{
		Yii::import($this->migrationPath . '.*');
		if ($return = parent::beforeAction($action, $params)) {

			echo "extended with EMigrateCommand by cebe <mail@cebe.cc>\n\n";

			if ($action == 'create' && !is_null($this->module)) {
				$this->usageError('create command can not be called with --module parameter!');
			}
			if (!is_null($this->module) && !is_string($this->module)) {
				$this->usageError('parameter --module must be a comma seperated list of modules or a single module name!');
			}


			// add a pseudo-module 'core'
			$this->modulePaths[$this->applicationModuleName] = $this->migrationPath;

			// remove disabled modules
			$disabledModules = array();
			foreach($this->modulePaths as $module => $pathAlias) {
				if (in_array($module, $this->disabledModules)) {
					unset($this->modulePaths[$module]);
					$disabledModules[] = $module;
				}
			}
			if (!empty($disabledModules)) {
				echo "The following modules are disabled: " . implode(', ', $disabledModules) . "\n";
			}

			// only add modules that are desired by command
			$modules = false;
			if (!is_null($this->module)) {
				$modules = explode(',', $this->module);

				// error if specified module does not exist
				foreach ($modules as $module) {
					if (!isset($this->modulePaths[$module])) {
						die("\nError: module '$module' is not available!\n\n");
					}
				}
				echo "Current call limited to module" . (count($modules)>1 ? "s" : "") . ": " . implode(', ', $modules) . "\n";
			}
			echo "\n";

			// initialize modules
			foreach($this->modulePaths as $module => $pathAlias) {
				if ($modules === false || in_array($module, $modules)) {
					// nothing to do for application core module
					if ($module == $this->applicationModuleName) {
						continue;
					}
					$path = Yii::getPathOfAlias($pathAlias);
					if($path === false || !is_dir($path))
						die('Error: The migration directory does not exist: ' . $pathAlias . "\n");
					$this->modulePaths[$module] = $path;
					Yii::import($pathAlias . '.*');
				} else {
					unset($this->modulePaths[$module]);
				}
			}
		}
		return $return;
	}

	public function actionCreate($args)
	{
		// if module is given adjust path
		if(count($args)==2) {
			$this->migrationPath = $this->modulePaths[$args[0]];
			$args = array($args[1]);
		} else {
			$this->migrationPath = $this->modulePaths[$this->applicationModuleName];
		}

		parent::actionCreate($args);
	}

	public function actionTo($args)
	{
		die('migrate to does not yet work with modules.' . "\n\n");
	}

	public function actionMark($args)
	{
		die('migrate mark does not yet work with modules.' . "\n\n");
	}

	protected function instantiateMigration($class)
	{
		require_once($class.'.php');
		$migration=new $class;
		$migration->setDbConnection($this->getDbConnection());
		return $migration;
	}

	// set to not add modules when getHistory is called for getNewMigrations
	private $_scopeNewMigrations = false;

	protected function getNewMigrations()
	{
		$this->_scopeNewMigrations = true;
		$migrations = array();
		// get new migrations for all new modules
		foreach($this->modulePaths as $module => $path)
		{
			$this->migrationPath = $path;
			foreach(parent::getNewMigrations() as $migration) {
				$migrations[$migration] = $module.$this->moduleDelimiter.$migration;
			}
		}
		$this->_scopeNewMigrations = false;

		ksort($migrations);
		return $migrations;
	}

	protected function getMigrationHistory($limit)
	{
		$db=$this->getDbConnection();
		if($db->schema->getTable($this->migrationTable)===null)
		{
			echo 'Creating migration history table "'.$this->migrationTable.'"...';
			$db->createCommand()->createTable($this->migrationTable, array(
				'version'=>'string NOT NULL PRIMARY KEY',
				'apply_time'=>'integer',
				'module'=>'VARCHAR(32)',
			));
			echo "done.\n";
		}

		if ($this->_scopeNewMigrations) {
			$select = "version, apply_time";
			$params = array();
		} else {
			$select = "CONCAT(module,:delimiter,version) AS versionName, apply_time";
			$params = array(':delimiter' => $this->moduleDelimiter);
		}

		$command = $db->createCommand()
					  ->select($select)
					  ->from($this->migrationTable)
					  ->order('version DESC')
					  ->limit($limit);

		if (!is_null($this->module)) {
			$criteria = new CDbCriteria();
			$criteria->addInCondition('module', explode(',', $this->module));
			$command->where = $criteria->condition;
			$params += $criteria->params;
		}

		return CHtml::listData($command->queryAll(true, $params), 'versionName', 'apply_time');
	}

	protected function migrateUp($class)
	{
		$module = $this->applicationModuleName;
		// remove module if given
		if (($pos = mb_strpos($class, $this->moduleDelimiter)) !== false) {
			$module = mb_substr($class, 0, $pos);
			$class = mb_substr($class, $pos + mb_strlen($this->moduleDelimiter));
		}
		// create base migration for module if none exists
		$db = $this->getDbConnection();
		if (!$db->createCommand()->select('version')
								 ->from($this->migrationTable)
								 ->where('module=:module')
								 ->queryRow(true, array(':module'=>$module)))
		{
			$db->createCommand()->insert($this->migrationTable, array(
				'version'=>self::BASE_MIGRATION . '_' . $module,
				'apply_time'=>time(),
				'module'=>$module,
			));
		}
		if(mb_strpos($class, self::BASE_MIGRATION) === 0) {
			return;
		}
		if (($ret = parent::migrateUp($class)) !== false) {
			// add module information to migration table
			$this->getDbConnection()->createCommand()->update(
				$this->migrationTable,
				array('module'=>$module),
				'version=:version',
				array(':version' => $class)
			);
		}
		return $ret;
	}

	protected function migrateDown($class)
	{
		// remove module if given
		if (($pos = mb_strpos($class, $this->moduleDelimiter)) !== false) {
			$class = mb_substr($class, $pos + mb_strlen($this->moduleDelimiter));
		}

		if(mb_strpos($class, self::BASE_MIGRATION) !== 0) {
			return parent::migrateDown($class);
		}
	}


	public function getHelp()
	{
		return parent::getHelp() . <<<EOD

EXTENDED USAGE EXAMPLES (with modules)
  for every action except create you can specify the modules to use
  with the parameter --module=<modulenames>
  where <modulenames> is a comma seperated list of module names (or a single name)

 * yiic migrate create modulename create_user_table
   Creates a new migration named 'create_user_table' in module 'modulename'.

  all other commands work exactly as described above.

  commands 'mark' and 'to' are not yet available.

EOD;
	}

}
