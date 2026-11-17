<?php

// Entrada para Vercel: la base demo se copia a /tmp (unico disco escribible) y se reinicia con cada instancia nueva
$seed = __DIR__ . '/../demo-seed';
$data = '/tmp/data';
$seedDb = $seed . '/grocy_es.db';
$targetDb = $data . '/grocy_es.db';

$seedVerFile = $seed . '/seed_version.txt';
$targetVerFile = $data . '/seed_version.txt';
$seedVer = file_exists($seedVerFile) ? trim((string)@file_get_contents($seedVerFile)) : '';
$targetVer = file_exists($targetVerFile) ? trim((string)@file_get_contents($targetVerFile)) : '';

$seedSize = file_exists($seedDb) ? (int)@filesize($seedDb) : 0;
$targetSize = file_exists($targetDb) ? (int)@filesize($targetDb) : 0;

if (!file_exists($targetDb) || $seedVer !== $targetVer || $seedSize !== $targetSize)
{
	foreach (['', '/viewcache', '/storage', '/plugins', '/logs'] as $dir)
	{
		@mkdir($data . $dir, 0777, true);
	}
	@copy($seed . '/config.php', $data . '/config.php');
	@copy($seedDb, $targetDb);
	if (file_exists($seedVerFile))
	{
		@copy($seedVerFile, $targetVerFile);
	}
}

define('GROCY_IS_EMBEDDED_INSTALL', false);
define('GROCY_DATAPATH', $data);

if (extension_loaded('zlib') && strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === false)
{
	ini_set('zlib.output_compression', '1');
}

require_once __DIR__ . '/../app.php';
