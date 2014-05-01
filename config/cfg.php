<?php
define('DB_DRIVER', 'mysql');

/*
define('DB_NAME', 'cell_map ');
define('DB_USER', 'cell_map_build');
define('DB_PASSWORD', '@He45_Re#');
 */

define('DB_NAME', 'sga');
define('DB_USER', 'root');
define('DB_PASSWORD', 'elwg324');
define('DB_HOST', '115.156.216.95');
//define('DB_HOST', '115.156.216.110');
//define('DB_NAME', 'cellmap');
//define('DB_USER', 'root');
//define('DB_PASSWORD', '123');
//define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');

//define('DO_DEBUG', true);
define('SGA_LOG', true);
define('SGA_LOG_FILE', 'sga_log.log');

/** Set default value of search */
define('DEFAULT_GENE', 'TP53');
define('DEFAULT_DISEASE', 'primary breast cancer');

//define('TIMEZONE', 'Asia/Chongqing');

define('STRING_SEPARATOR', ' ');
if (defined('ICG_DEBUG'))
    define('DB_DEBUG', true);
else
    define('DB_DEBUG', false);
// End of script

define('FILE_INTERACTION_NAME', ABSPATH . '/data/TS30_120210_scored_120212.txt');
