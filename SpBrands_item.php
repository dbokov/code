<?php

// SpBrands.ru - модель товара

class Item extends CActiveRecord
{

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	

	public function tableName()
	{
		return 'items';
	}

	public function getModelName()
	{
		return __CLASS__;
	}


	public function rules()
	{
		return array(
			array('id_collection, title', 'required'),
			array('id_collection, price', 'numerical', 'integerOnly'=>true),
			array('id, id_collection, title, descr, price', 'safe', 'on'=>'search'),
		);
	}


	public function relations()
	{
		return array(
			'options'=>array(self::HAS_MANY, 'Option', 'id_item'),
			'collection'=>array(self::BELONGS_TO, 'Collection', 'id_collection'),
			'orders'=>array(self::HAS_MANY, 'Order', 'id_item'),
        		'photos'=>array(self::HAS_MANY, 'Photo', 'id_item'),
		);
	}


    public function makeUrl(){
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );

        $text = strtr($this->title, $converter);
        $text = strtolower($text);
        $converter = array( '   ' => '-',   '  ' => '-',  ' ' => '-',);
        $text = strtr($text,$converter);
        $text = preg_replace('/[^a-z0-9_-]/u', '', $text);
        $text = trim(trim($text), "-");
        $this->url =  $this->id."-".$text;
    }

	public function getPrice(){
		$price = round($this->price + (($this->price / 100) * $this->collection->offer->overprice));
		return $price;	
	}

	public function getCollection(){
		$collection = Collection::model()->findByPk($this->id_collection);
		return $collection;
	}
	
	
	public function getTitle(){
		$ret = $this->title;
		mb_internal_encoding('UTF-8');
		if(strlen($this->title)>25) $ret = mb_substr($this->title,0,25)."...";
		return $ret;
	}
	
	public function getImage($type='small'){
		if(!sizeof($this->photos)) {
			$photo_small = "/images/small_empty.jpg";
			$photo_big = "/images/empty.jpg";
			}
		else {
			$photo_small = "/images/items/sm_".$this->photos[0]->photo;
			$photo_big =  "/images/items/".$this->photos[0]->photo;
		}
	
		if($type=='small') {
			$ret = $photo_small;
		}
		if($type=='big'){
			$ret = $photo_big;
		}
	
		return $ret;
	}
	
	public function getImages(){
		$ret = array();
		if(sizeof($this->photos)) {
			foreach($this->photos as $photo) {
				$ar['photo_small'] = "/images/items/sm_".$photo->photo;
				$ar['photo_big'] =  "/images/items/".$photo->photo;
				$ret[] = $ar;
			}
		}
		return $ret;
	}
	
	public function hasColors(){
		$ret = 0;
		foreach($this->options as $opt) {
			if($opt->label=='Цвет') $ret = 1;
		}
		return $ret;
	}
	
	
	public function hasSizes(){
		$ret = 0;
		foreach($this->options as $opt) {
			if($opt->label=='Размер') $ret = 1;
		}
		return $ret;
	}
	
	
	public function getNumber(){
		return $this->id_collection."-".$this->id;
	}
	
	public function applyFilters($models,$filters){
		foreach($filters as $k=>$filter) {
		
			if($k=="price") {
				$pr = explode("_",$filter);
				$min = $pr[0];
				$max = $pr[1];
				foreach($models as $model){
					$price = $model->getPrice();
					if($price>=$min && $price<=$max)  $data[] =  $model; 
				}
				
				$models = $data;
				unset($data);
			}
			
			if($k=="size") {
				foreach($models as $model){
					foreach($model->options as $opt){
						if($opt->label=="Размер") {
						    if($opt->option == $filter)  $data[] = $model; 
						 }
					}
				}
			
				$models = $data;
				unset($data);
			}
		
			
			if($k=="color") {
				foreach($models as $model){
					foreach($model->options as $opt){
						if($opt->label=="Цвет") {
						    if($opt->option == $filter) $data[] = $model;
						}
					}
				}
			
				$models = $data;
				unset($data);
			}
		
			if($k=="brand") {
				foreach($models as $model){
					if($model->collection->id_brand==$filter) $data[] = $model;
				}
				$models = $data;
				unset($data);
			}
		
		}
		
		return $models;
	}
	
	public static function resetFilter($url,$type){
		$data = $_GET;
		unset($data[$type]);
		unset($data['name']);
		
		if(sizeof($data)) {
			foreach($data as $k=>$v){
				$params[] = $k."=".$v;
			}
			$ret =  $url."?".join("&",$params);
		}
		else $ret = $url;
		return $ret;
	}
	
	public static function getNew(){
		$criteria = new CDbCriteria;
		$criteria->order = 'id DESC';
		$criteria->limit = 12;
		$models = Item::model()->findAll($criteria);
		return $models;
	}
	
	
	public function attributeLabels()
		{
		return array(
			'id' => 'Номер',
			'id_collection' => 'Номер коллекции',
			'title' => 'Артикул',
			'descr' => 'Описание',
			'price' => 'Цена',
			'photo' => 'Фото',
		);
	}
	
	public function search(){

		$criteria=new CDbCriteria;
		
		$criteria->compare('id',$this->id);
		$criteria->compare('id_collection',$this->id_collection);
		$criteria->compare('title',$this->title);
		$criteria->compare('descr',$this->descr,true);
		$criteria->compare('price',$this->price);
		
		return new CActiveDataProvider($this, array(
		'criteria'=>$criteria,
		));
	}
}
