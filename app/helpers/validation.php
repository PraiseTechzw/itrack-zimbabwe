<?php
if (!function_exists('old')) { function old(string $key, mixed $default=''): string { return htmlspecialchars((string)($_POST[$key]??$default),ENT_QUOTES,'UTF-8'); } }
