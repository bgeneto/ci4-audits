<?php

namespace Bgeneto\Audits\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_table_audits extends Migration
{
    public function up()
    {
        // audit logs
        $fields = [
            'id'        => ['type' => 'int', 'unsigned' => true, 'auto_increment' => true],
            'source'    => ['type' => 'varchar', 'constraint' => 127],
            'source_id' => ['type' => 'int', 'unsigned' => true],
            'user_id'   => ['type' => 'int', 'unsigned' => true, 'null' => true],
            'event'     => ['type' => 'varchar', 'constraint' => 63],
            'summary'   => ['type' => 'varchar', 'constraint' => 254],
            'data'      => ['type' => 'json', 'null' => true],
        ];

        $this->forge->addField($fields);
        $this->forge->addField('created_at DATETIME NOT NULL DEFAULT current_timestamp()');

        $this->forge->addPrimaryKey('id');

        $this->forge->addKey(['source', 'source_id', 'event']);
        $this->forge->addKey(['user_id', 'source', 'event']);
        $this->forge->addKey(['event', 'user_id', 'source', 'source_id']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('audits');
    }

    public function down()
    {
        $this->forge->dropTable('audits');
    }
}
