<?php
require dirname(__DIR__) . '/app/core/bootstrap.php';
$sql = file_get_contents(dirname(__DIR__) . '/sql/schema.sql');
DB::conn()->exec($sql);
echo "Schema imported\n";
