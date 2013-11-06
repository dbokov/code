<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Администратор
 * Date: 06.03.12
 * Time: 18:04
 * To change this template use File | Settings | File Templates.
 */

class youtube extends Weber {

    public function __construct(){
        $this->url = "http://www.youtube.com/channel/HCED2lR4JIL-c";
        $this->table = "youtube";
        parent::__construct();
    }

    public function parse(){
        $ret = "";

	echo $this->doc;

	$k = 0;
        foreach($this->doc['.channels-content-item'] as $item){
		if($k<30){
            $img = trim(pq($item)->find('.yt-thumb-clip-inner')->find('img')->attr('data-thumb'));
            if(!strpos($img,"ttp")) $img = "http:".$img;

	    $title = iconv('utf-8','windows-1251',$this->clearstring(trim(pq($item)->find('.yt-lockup-title')->find('a')->attr('title'))));
                  
            $link = "http://youtube.com".trim(pq($item)->find('.yt-lockup-title')->find('a')->attr('href'));
            $date = $this->rusdate(iconv("utf-8","windows-1251",trim(pq($item)->find(".yt-lockup-deemphasized-text")->text())));
            $author = iconv("utf-8","windows-1251",trim(pq($item)->find(".yt-user-name")->text()));
	    $views = $this->clearviews(trim(pq($item)->find(".context-data-item")->attr('data-context-item-views')));	   
	    $hash = md5($author.$title);

            $data = array("hash"=>$hash,"views"=>$views,"author"=>$author,"link"=>$link,"date"=>$date,"img"=>$img,"title"=>$title);

		print_r($data);
           
	    $this->add($hash,$data);
		} $k++;

        }
        return $ret;
    }

    private function clearviews($text){
    	$text = str_replace(array(".","Aufrufe","visualizzazioni"),"",$text);
        return $text;
    }          

    private function rusdate($text){
	$text = str_replace("vor","",$text);
	$text = str_replace(array("Tag","Tagen"),"д.",$text);
	$text = str_replace("Stunden","ч.",$text);
	$text = str_replace("Monaten","мес.",$text);
	
	/*
	$text = str_replace(array("giorni","giorno"),"д.",$text);
	$text = str_replace("ore","ч.",$text);
   	$text = str_replace("fa","назад",$text);
	*/

    	return iconv('utf-8','windows-1251',$text);
    }


    public function getxml(){
        $sql = "SELECT * FROM youtube";
        $res = mysql_query($sql);
        $ret = '';
        while($row = mysql_fetch_array($res)){
	    $qual = $this->getquality($row['hash'],"youtube");
            $ret.="<item>"."\n";
            $ret.="<link>";
	    $ret.="<![CDATA[".$row['link']."]]>";
	    $ret.="</link>"."\n";
	    $ret.="<title>";
	    $ret.="<![CDATA[".$row['title']."]]>";
	    $ret.="</title>"."\n";
            $ret.="<img>";
	    $ret.="<![CDATA[".$row['img']."]]>";
	    $ret.="</img>"."\n";
	    $ret.="<time>";
	    $ret.="<![CDATA[".$row['date']."]]>";
	    $ret.="</time>"."\n";
	    $ret.="<author>";
	    $ret.="<![CDATA[".$row['author']."]]>";
	    $ret.="</author>"."\n";
            $ret.="<text>";
            $ret.="<![CDATA[".$row['text']."]]>";
            $ret.="</text>"."\n";
            $ret.="<quality>".$qual."</quality>"."\n";
            $ret.="</item>"."\n";
        }
        return $ret;
    }



    public function grab($clear=1){
        $uagent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko/20100101 Firefox/11.0";

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	$cook = file_get_contents("ytc.txt");
	curl_setopt($ch, CURLOPT_COOKIE, $cook);
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
        	$this->doc = phpQuery::newDocumentHTML($content);
	}
	else $this->doc = 0;
    }


}
