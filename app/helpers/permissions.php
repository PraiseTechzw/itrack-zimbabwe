<?php
if (!function_exists('hasRole')) { function hasRole(array|string $roles): bool { $roles=(array)$roles; $role=$_SESSION['user']['role']??''; if($role==='admin') $role='Administrator'; return in_array($role,$roles,true); } }
