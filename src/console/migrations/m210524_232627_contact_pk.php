<?php

use yii\db\Migration;

/**
 * Updates the primary key to be the `contact_id` and allows the 
 * `user_id` field to be null
 */
class m210524_232627_contact_pk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('PRIMARY', 'icontact_contact');

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-icontact_contact-user_id}}',
            '{{%icontact_contact}}'
        );

        $this->alterColumn('icontact_contact', 'user_id', $this->integer()->null());
        $this->renameColumn('icontact_contact', 'contact_id', 'id');
        $this->alterColumn('icontact_contact', 'id', $this->integer()->notNull()->first());

        // --- Remove duplicates which would cause key constraint errors
        $sql = <<<SQL
SELECT `id`, count(`user_id`) as `num_user_ids`, GROUP_CONCAT(`id`) as `ids_string`
FROM `icontact_contact` 
GROUP BY `id` 
HAVING COUNT(`user_id`) >1;
SQL;
        $results = Yii::$app->db->createCommand($sql)->queryAll();
        foreach($results as $result){
            $duplicateIds = explode(',', $result['ids_string']);
            $this->delete('icontact_contact', ['id' => array_pop($duplicateIds)]);
        }
        
        $this->addPrimaryKey('PRIME', 'icontact_contact', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('PRIMARY', 'icontact_contact');
        $this->alterColumn('icontact_contact', 'id', $this->integer()->notNull()->after('user_id'));
        $this->renameColumn('icontact_contact', 'id', 'contact_id');
        $this->alterColumn('icontact_contact', 'user_id', $this->integer()->notNull()->first());

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-icontact_contact-user_id}}',
            '{{%icontact_contact}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addPrimaryKey('PRIME', 'icontact_contact', 'user_id');
    }
}
