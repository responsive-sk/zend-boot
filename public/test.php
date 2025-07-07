<?php

header('Content-Type: text/plain');
echo "PHP verzia: " . phpversion() . "\n";
echo "disk_free_space existuje? " . (function_exists('disk_free_space') ? 'Áno' : 'Nie') . "\n";
echo "Zakázané funkcie: " . ini_get('disable_functions') . "\n";