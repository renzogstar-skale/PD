<?php

// Entrada para Vercel: la base demo se copia a /tmp (unico disco escribible) y se reinicia con cada instancia nueva
$seed = __DIR__ . '/../demo-seed';
$data = '/tmp/data';
$seedDb = $seed . '/grocy_es.db';
$targetDb = $data . '/grocy_es.db';

if (!file_exists($targetDb) || (file_exists($seedDb) && filemtime($seedDb) > filemtime($targetDb)))
{
	foreach (['', '/viewcache', '/storage', '/plugins', '/logs'] as $dir)
	{
		@mkdir($data . $dir, 0777, true);
	}
	@copy($seed . '/config.php', $data . '/config.php');
	@copy($seedDb, $targetDb);
}

define('GROCY_IS_EMBEDDED_INSTALL', false);
define('GROCY_DATAPATH', $data);

if (extension_loaded('zlib') && strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === false)
{
	ini_set('zlib.output_compression', '1');
}

require_once __DIR__ . '/../app.php';
