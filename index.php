<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

if (is_logged_in()) {
	redirect('chats.php');
}

redirect('login.php');

?>


