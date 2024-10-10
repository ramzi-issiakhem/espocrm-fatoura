<?php
return [
  'database' => [
    'host' => 'mysql',
    'port' => '',
    'charset' => NULL,
    'dbname' => 'espocrm',
    'user' => 'espocrm',
    'password' => 'database_password',
    'platform' => 'Mysql'
  ],
  'smtpPassword' => '',
  'logger' => [
    'path' => 'data/logs/espo.log',
    'level' => 'DEBUG',
    'rotation' => true,
    'maxFileNumber' => 30,
    'printTrace' => false,
    'databaseHandler' => false,
    'sql' => false,
    'sqlFailed' => false
  ],
  'restrictedMode' => false,
  'cleanupAppLog' => true,
  'cleanupAppLogPeriod' => '30 days',
  'webSocketMessager' => 'ZeroMQ',
  'clientSecurityHeadersDisabled' => false,
  'clientCspDisabled' => false,
  'clientCspScriptSourceList' => [
    0 => 'https://maps.googleapis.com'
  ],
  'adminUpgradeDisabled' => false,
  'isInstalled' => true,
  'microtimeInternal' => 1728471450.325194,
  'passwordSalt' => '8ace7b295d0d8cdc',
  'cryptKey' => 'e1e938907f060a91c5cf6d3ee36abd7c',
  'hashSecretKey' => 'cfbfa92c01d84fcc06578e6fb492df3f',
  'defaultPermissions' => [
    'user' => 33,
    'group' => 33
  ],
  'actualDatabaseType' => 'mysql',
  'actualDatabaseVersion' => '8.4.2',
  'instanceId' => 'ffafa741-b636-4d87-abe6-e36a864186e1'
];
