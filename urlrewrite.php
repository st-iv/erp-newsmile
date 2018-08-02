<?php
$arUrlRewrite=array (
  1 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^/stssync/calendar/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/calendar/index.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/personal/lists/#',
    'RULE' => '',
    'ID' => 'bitrix:lists',
    'PATH' => '/personal/lists/index.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/patientcard/#',
    'RULE' => '',
    'ID' => 'newSmile:patient.card',
    'PATH' => '/patientcard/index.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/work-chair/#',
    'RULE' => '',
    'ID' => 'newSmile:workchair',
    'PATH' => '/work-chair/index.php',
    'SORT' => 100,
  ),
  3 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
);
