<?php
// Mediatopograf.ru - класс парсинга Ютуба

class youtube extends Weber {

    public function __construct(){
        $this->url = "http://www.youtube.com/channel/HCED2lR4JIL-c";
        $this->table = "youtube";
        parent::__construct();
    }

    public function parse(){
        $ret = 0;

        foreach($this->doc['.channels-content-item'] as $k=>$item){
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
		    if($this->add($hash,$data)) $ret = 1;
	    } 

        }
        return $ret;
    }

    // сервер в Голландии - страницы отдаются то на нидерландском, то на немецком. Колхоз...
    private function clearviews($text){
    	$text = str_replace(array(".","Aufrufe","visualizzazioni"),"",$text);
        return $text;
    }          

    private function rusdate($text){
	$text = str_replace("vor","",$text);
	$text = str_replace(array("Tag","Tagen"),"д.",$text);
	$text = str_replace("Stunden","ч.",$text);
	$text = str_replace("Monaten","мес.",$text);
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

}
