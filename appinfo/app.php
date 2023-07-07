<?php
namespace OCA\WhmcsIntegration\AppInfo;
$app = new Application();
$app->getContainer()->query('UserHooks')->register();