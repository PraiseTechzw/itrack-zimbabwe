<?php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
http_response_code(403);
exit('Access denied.');
