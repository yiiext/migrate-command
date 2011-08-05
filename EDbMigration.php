<?php


class EDbMigration extends CDbMigration
{
	public $module = null;

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
