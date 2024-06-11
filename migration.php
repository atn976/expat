<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up()
    {
        // Create the database if it does not exist
        $this->execute('CREATE DATABASE IF NOT EXISTS expat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Use the created database
        $this->execute('USE expat');

        // Create the article table
        $this->table('article')
            ->addColumn('title', 'string', ['limit' => 80])
            ->addColumn('content', 'text')
            ->create();

        // Create the category table
        $this->table('category')
            ->addColumn('name', 'string', ['limit' => 45])
            ->addColumn('description', 'string', ['limit' => 255])
            ->create();

        // Create the article_has_category table
        $this->table('article_has_category')
            ->addColumn('article_id', 'integer')
            ->addColumn('category_id', 'integer')
            ->addForeignKey('article_id', 'article', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('category_id', 'category', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();

        // Insert data into category table
        $this->table('category')
            ->insert([
                ['name' => 'emploi', 'description' => 'Recherche et demande d\'emploi'],
                ['name' => 'immobilier', 'description' => 'Recherche et proposition de logement']
            ])
            ->saveData();
    }

    public function down()
    {
        $this->execute('DROP DATABASE IF EXISTS expat');
    }
}
?>
