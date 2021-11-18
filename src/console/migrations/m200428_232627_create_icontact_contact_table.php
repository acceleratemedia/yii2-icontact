<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%icontact_contact}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m200428_232627_create_icontact_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%icontact_contact}}', [
            'user_id' => $this->primaryKey(),
            'contact_id' => $this->integer()->notNull()->comment('ID of the contact in iContact\'s system'),
            'last_sync_time' => $this->timestamp()->null(),
            'create_time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'update_time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-icontact_contact-user_id}}',
            '{{%icontact_contact}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-icontact_contact-user_id}}',
            '{{%icontact_contact}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-icontact_contact-user_id}}',
            '{{%icontact_contact}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-icontact_contact-user_id}}',
            '{{%icontact_contact}}'
        );

        $this->dropTable('{{%icontact_contact}}');
    }
}
