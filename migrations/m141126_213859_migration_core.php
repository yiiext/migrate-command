<?php

/**
 * This migration can be used to adjust your migration table when you switch from
 * the original migration command to this extension.
 * You should adjust the timestamp of the migration to the current date and time to
 * ensure migrations are always applied in the right order.
 */
class m141126_213859_migration_core extends CDbMigration
{
    /**
     * Get the migration table as defined in the MigrateCommand
     */
    private function getMigrationTable() {
        $table='tbl_migration';
        if(Yii::app() instanceof CConsoleApplication) {
            /* @var $app CConsoleApplication */
            $app=Yii::app();
            $command=$app->command;
            if($command instanceof MigrateCommand) {
                /* @var $command MigrateCommand */
                $table=$command->migrationTable;
            }
        }  
        return $table;
    }
    
    public function up() {
        $table=$this->getMigrationTable();
        $column='module';
        $this->addColumn($table, $column,  'VARCHAR(32) DEFAULT "core"'/*.$this->comment("Module of the migration")*/);
        $this->refreshTableSchema($table);
        $this->update($table, array($column=>'core'));
    }

    public function down() {
        $table=$this->getMigrationTable();
        $column='module';
        $this->dropColumn($table, $column);
        $this->refreshTableSchema($table);
    }
}
