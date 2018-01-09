<?php

session_start();
session_destroy();

echo 'You have successfully logged out and ended your session';

echo '<br />';
echo '<a href="index.php">Return to Login Screen</a>';

