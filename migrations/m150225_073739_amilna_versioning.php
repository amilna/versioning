<?php

use yii\db\Schema;
use yii\db\Migration;

class m150225_073739_amilna_versioning extends Migration
{
    public function up()
    {
		$this->createTable($this->db->tablePrefix.'versioning_group', [
            'id' => 'pk',
            'title' => Schema::TYPE_STRING . '(65) NOT NULL',
            'description' => Schema::TYPE_TEXT.'',
            'owner_id' => Schema::TYPE_INTEGER.' NOT NULL',            
            'status' => Schema::TYPE_SMALLINT.' NOT NULL',
            'time' => Schema::TYPE_TIMESTAMP. ' NOT NULL DEFAULT NOW()',
            'isdel' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
        ]);
        $this->createIndex($this->db->tablePrefix.'versioning_group_title'.'_key', $this->db->tablePrefix.'versioning_group', 'title', true);
        $this->addForeignKey( $this->db->tablePrefix.'versioning_group_owner_id', $this->db->tablePrefix.'versioning_group', 'owner_id', $this->db->tablePrefix.'user', 'id', 'CASCADE', null );
        
        $this->createTable($this->db->tablePrefix.'versioning_grp_usr', [            
            'group_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'user_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'isdel' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
        ]);
        $this->addForeignKey( $this->db->tablePrefix.'versioning_grp_usr_user_id', $this->db->tablePrefix.'versioning_grp_usr', 'user_id', $this->db->tablePrefix.'user', 'id', 'CASCADE', null );
        $this->addForeignKey( $this->db->tablePrefix.'versioning_grp_usr_group_id', $this->db->tablePrefix.'versioning_grp_usr', 'group_id', $this->db->tablePrefix.'versioning_group', 'id', 'CASCADE', null );                
		
		$this->createTable($this->db->tablePrefix.'versioning_record', [
            'id' => 'pk',            
            'model' => Schema::TYPE_STRING . '(65) NOT NULL',
            'record_id' => Schema::TYPE_INTEGER.'',            
            'owner_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'group_id' => Schema::TYPE_INTEGER.'',
            'viewers' => Schema::TYPE_TEXT.'',
            'isdel' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
        ]);        
        $this->addForeignKey( $this->db->tablePrefix.'versioning_record_owner_id', $this->db->tablePrefix.'versioning_record', 'owner_id', $this->db->tablePrefix.'user', 'id', 'CASCADE', null );
        $this->addForeignKey( $this->db->tablePrefix.'versioning_record_group_id', $this->db->tablePrefix.'versioning_record', 'group_id', $this->db->tablePrefix.'versioning_group', 'id', 'SET NULL', null );
		
		$this->createTable($this->db->tablePrefix.'versioning_route', [
            'id' => 'pk',
            'route' => Schema::TYPE_STRING.'(255) NOT NULL',
            'user_id' => Schema::TYPE_INTEGER.'',
            'time' => Schema::TYPE_TIMESTAMP. ' NOT NULL DEFAULT NOW()',
            'isdel' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
        ]);        
        $this->addForeignKey( $this->db->tablePrefix.'versioning_route_user_id', $this->db->tablePrefix.'versioning_route', 'user_id', $this->db->tablePrefix.'user', 'id', 'SET NULL', null );
		
		$this->createTable($this->db->tablePrefix.'versioning_version', [
            'id' => 'pk',
            'route_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'record_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'record_attributes' => Schema::TYPE_TEXT.'',
            'route_ids' => Schema::TYPE_TEXT.'',
            'type' => Schema::TYPE_SMALLINT.' NOT NULL',            
            'status' => Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT TRUE',
            'tree' => Schema::TYPE_INTEGER.'',
            'lft' => Schema::TYPE_INTEGER.' NOT NULL',
            'rgt' => Schema::TYPE_INTEGER.' NOT NULL',
            'depth' => Schema::TYPE_INTEGER.' NOT NULL',
            'isdel' => Schema::TYPE_SMALLINT.' NOT NULL DEFAULT 0',
        ]);                
        $this->addForeignKey( $this->db->tablePrefix.'versioning_version_record_id', $this->db->tablePrefix.'versioning_version', 'record_id', $this->db->tablePrefix.'versioning_record', 'id', 'CASCADE', null );                                
        $this->addForeignKey( $this->db->tablePrefix.'versioning_version_route_id', $this->db->tablePrefix.'versioning_version', 'route_id', $this->db->tablePrefix.'versioning_route', 'id', 'CASCADE', null );                                
    }

    public function down()
    {
        echo "m150225_073739_amilna_versioning cannot be reverted.\n";

        return false;
    }
}
