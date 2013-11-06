<?php

require_once('phpQuery/phpQuery.php');
require_once("stat.php");

class Weber
{
    public $url;
    public $proxy;
    public $doc;
    public $table;

	
    public function clearstring($str){
	$str = preg_replace('/[^`\!\?|\'\"\+\-\*_@:.,;#\$%\^\&\(\)\{\}\/=\w\sА-Яа-яё]/u','*', $str);
	$str = mysql_real_escape_string($str);
	return $str;
    }

    public function clearstring2($str){
	$str = preg_replace('/[^`\!\?|\'\"\+\-\*_@:.,;#\$%\^\&\(\)\{\}\/=\w\sА-Яа-я]/u','', $str);
	return $str;
    }

    public function __construct(){
        $this->proxy = 0;
        $db_user = "root";
        $db_pass = "gbdjcvfhnbyb";
        $db_host = "localhost";
        $db_name = "smm";

        mysql_connect($db_host,$db_user,$db_pass);
        mysql_select_db($db_name);
  	mysql_query("SET NAMES cp1251");

    }


    public function clear(){
    	$sql = "DELETE FROM ".$this->table." WHERE 1";
 	mysql_query($sql);
    }

    public function add($hash,$data){
    		$count = count($data) - 1;
		$z = 0;

		$str = "INSERT INTO ".$this->table." SET ";
        	foreach($data as $k=>$v){
			$str.=$k."='".$v."'";
			if($z<$count)
			$str.=",";		
			$z++;
		}
		
		echo $str."<hr>";
		mysql_query($str) or die(mysql_error());

    }

   public function addstat($hash,$data,$table){
    		$count = count($data) - 1;
		$z = 0;

		$str = "INSERT INTO ".$table." SET date_add='".time()."',author='".$data['author']."'";
        	echo $str."<hr>";
		mysql_query($str) or die(mysql_error());

    }


    public static function getquality($hash,$type){
    	$sql = "SELECT quality FROM quality WHERE hash='".$hash."' AND type='".$type."'";
	$res = mysql_query($sql);
	if(mysql_num_rows($res)) $ret = mysql_result($res,0);
	else $ret = 3;
	return $ret;
    }

    public static function setquality($hash,$type,$quality){
	// check before
	$sql = "SELECT quality FROM quality WHERE type='".$type."' AND hash='".$hash."'";
	$res = mysql_query($sql);
	if(mysql_num_rows($res)){
		$current = mysql_result($res,0);
        	$sql = "UPDATE quality SET quality='".$quality."' WHERE hash='".$hash."' AND type='".$type."'";
		mysql_query($sql);
		// change stat
		if($quality==1) stat::up($type,"good");
		if($quality==2) stat::up($type,"bad");

		if($current=="1") stat::down($type,"good");	
		if($current=="2") stat::down($type,"bad");	
		
        }
	else {
		$sql = "INSERT INTO quality SET quality='".$quality."',hash='".$hash."',type='".$type."'";
		mysql_query($sql);
		if($quality=="1") $qual = "good";
		if($quality=="2") $qual = "bad";
		stat::up($type,$qual);
	}
	
    }

    public function grab($clear=1){
        $uagent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko/20100101 Firefox/11.0";

	$headers = array('Content-type: text/html; charset=windows-1251');
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cook.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE,"cook.txt");
        if($this->proxy) curl_setopt($ch, CURLOPT_PROXY, $this->proxy);

        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );
	if(strlen($content)>500) {
		if($clear) $this->clear();
	//	$handle = fopen("lj.txt","a+");
	//	fwrite($handle,$content."------------------"."\r\n");
        	$this->doc = phpQuery::newDocument($content);
	}
	else $this->doc = 0;
    }

}
