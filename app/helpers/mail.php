<?php
if (!function_exists('sendMail')) { function sendMail(string $to, string $subject, string $message): bool { return filter_var($to,FILTER_VALIDATE_EMAIL)!==false; } }
