<?php

echo "<pre>";

echo "Arquivo atual:\n";
echo __FILE__;

echo "\n\nConteúdo da pasta public:\n";

print_r(scandir(__DIR__));

echo "</pre>";