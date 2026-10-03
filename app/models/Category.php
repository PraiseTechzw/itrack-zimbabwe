<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Category extends ModuleModel { public function all():array{return $this->fetchAll('SELECT * FROM categories ORDER BY name');} public function create(string $name):string{$this->execute('INSERT INTO categories(name) VALUES(?)',[trim($name)]);return $this->lastInsertId();} }
