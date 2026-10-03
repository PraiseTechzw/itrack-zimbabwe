<?php
class Validator { public static function required(array $data, array $fields): array { $errors=[]; foreach($fields as $field) if(trim((string)($data[$field]??''))==='') $errors[$field]='This field is required.'; return $errors; } public static function email(string $email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL)!==false; } }
