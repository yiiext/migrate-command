<?php

/**
 * EDbMigration
 *
 * this class will be rewritten the next weeks...
 *
 * @link http://www.yiiframework.com/extension/extended-database-migration/
 * @link http://www.yiiframework.com/doc/guide/1.1/en/database.migration
 * @author Carsten Brandt <mail@cebe.cc>
 * @version 0.4.0
 */
class EDbMigration extends CDbMigration
{
	public $module = null;

	public $interactive = true;

	public function confirm($message)
	{
		if(!$this->interactive)
			return true;
		echo $message.' [yes|no] ';
		return !strncasecmp(trim(fgets(STDIN)),'y',1);
	}

	public function __toString()
	{
		return $this->module . ': ' . get_class($this);
	}

	/**
	 * Executes a SQL statement. Silently. (only show sql on exception)
	 * This method executes the specified SQL statement using {@link dbConnection}.
	 * @param string $sql the SQL statement to be executed
	 * @param array $params input parameters (name=>value) for the SQL execution. See {@link CDbCommand::execute} for more details.
	 * @param
	 * @since 1.1.7
	 */
	public function execute($sql, $params=array(), $verbose=true)
	{
		if ($verbose) {
			parent::execute($sql, $params);
		} else {
			try {
				echo "    > execute SQL ...";
				$time=microtime(true);
				$this->getDbConnection()->createCommand($sql)->execute($params);
				echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
			} catch (CException $e) {
				echo " failed.\n\n";
				throw $e;
			}
		}
	}

}
