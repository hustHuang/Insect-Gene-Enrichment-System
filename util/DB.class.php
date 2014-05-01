<?php

/**
 * Description of db.class.php
 *
 * @author GGCoke
 */
class DB {
    var $driver;
    var $host;
    var $username;
    var $password;
    var $table;
    var $debug;
    var $conn;

    function __construct($driver, $host, $username, $password, $table, $debug) {
	$this->driver = $driver;
	$this->host = $host;
	$this->username = $username;
	$this->password = $password;
	$this->table = $table;
	$this->debug = $debug;
    }

    function get_connection() {
	if (is_null($this->conn)) {
	    if (!file_exists(ABSPATH . '/lib/adodb5/adodb.inc.php')) {
		echo "Cannot load lib " . (ABSPATH . '/lib/adodb5/adodb.inc.php') . ". Please check it.<br/>";
		return null;
	    }
	    require_once (ABSPATH . '/lib/adodb5/adodb.inc.php');
	    $this->conn = NewADOConnection($this->driver);
	    $this->conn->debug = $this->debug;
	    $this->conn->autoRollback = true;
	    $this->conn->PConnect($this->host, $this->username, $this->password, $this->table);
	    if (is_null($this->conn)){
		sprintf('
<h1>数据库连接错误</h1>
<p>您在 <code>cfg.php</code> 文件中提供的数据库用户名和密码可能不正确，或者无法连接到 <code>%s</code> 上数据库服务器，这意味着您的主机数据库服务器已停止工作。</p>
<ul>
	<li>您确认您提供的用户名和密码正确么？</li>
	<li>您确认您提供的主机名正确么？</li>
	<li>您确认数据库服务器正常运行么？</li>
</ul>
<p>如果您无法确定这些问题，请联系您的主机管理员。</p>
', $this->host);
	    }
	}
	return $this->conn;
    }

}

//end of script
