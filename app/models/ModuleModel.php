<?php
require_once dirname(__DIR__) . '/core/Model.php';
abstract class ModuleModel extends Model
{
    protected function tableCount(string $table): int { return (int) $this->db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn(); }
    protected function tableTotal(string $table, string $column): float { return (float) $this->db->query("SELECT COALESCE(SUM({$column}), 0) FROM {$table}")->fetchColumn(); }
}
