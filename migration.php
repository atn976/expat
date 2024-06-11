<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up()
    {
        // Créer la base de données si elle n'existe pas
        $this->execute('CREATE DATABASE IF NOT EXISTS expat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Utiliser la base de données créée
        $this->execute('USE expat');

        // Créer la table article
        $this->table('article')
            ->addColumn('title', 'string', ['limit' => 80])
            ->addColumn('content', 'text')
            ->create();

        // Créer la table category
        $this->table('category')
            ->addColumn('name', 'string', ['limit' => 45])
            ->addColumn('description', 'string', ['limit' => 255])
            ->create();

        // Créer la table article_has_category
        $this->table('article_has_category')
            ->addColumn('article_id', 'integer')
            ->addColumn('category_id', 'integer')
            ->addForeignKey('article_id', 'article', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('category_id', 'category', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();

        // Insérer des données dans la table category
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
