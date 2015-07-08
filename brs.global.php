<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
[END_COT_EXT]
==================== */
defined('COT_CODE') or die('Wrong URL.');

// Banners API is available everywhere
if(!defined('COT_ADMIN')) require_once cot_incfile('brs', 'module');
