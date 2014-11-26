<?php
class m141126_213859_migration_core extends CDbMigration
{
    public function safeUp() {
        $table='tbl_migration';
        $column='module';
        $this->addColumn($table, $column,  'VARCHAR(32) DEFAULT "core"'/*.$this->comment("Module of the migration")*/);
        $this->refreshTableSchema($table);
        $this->update($table, array($column=>'core'));
    }

    public function safeDown() {
        $table='tbl_migration';
        $column='module';
        $this->dropColumn($table, $column);
        $this->refreshTableSchema($table);
    }
}
