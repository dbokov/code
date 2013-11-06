<?php

// MindFight - онлайн игра - мозговой штурм, по типу командной викторины, брейн ринга. Пока не запущена. Модель игры.

class Game extends CActiveRecord
{

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'Game';
	}

	public function rules()
	{
		return array(
			array('id_league, type, id_style, title, mode, descr, money', 'required'),
			array('id_league, type, id_style, mode, money', 'numerical', 'integerOnly'=>true),
			array('id, author, id_league, mode, type, id_style, date_start, date_finish, title, descr, money', 'safe', 'on'=>'search'),
		);
	}


	public function relations()
	{

		return array(
	            'style'=>array(self::BELONGS_TO, 'Style', 'id_style'),
	            'users'=>array(self::HAS_MANY, 'GameUsers', 'id_game'),
	            'levels'=>array(self::HAS_MANY, 'Level', 'id_game'),
        );
	}

    public function setTimeStart($time){
        $this->TimeStart = $time;
    }
    public function setTimeFinish($time){
        $this->TimeFinish = $time;
    }
    public function getTimeStart(){
        return $this->TimeStart;
    }
    public function getTimeFinish(){
        return $this->TimeFinish;
    }

    public function scopes()
    {
        return array(
            'active'=>array(
                'condition'=>'status=1',
                'order'=>'date_start',
            ),
            'past'=>array(
                'condition'=>'status=2',
                'order'=>'date_start',
            ),
            'off'=>array(
                'condition'=>'status=0',
                'order'=>'date_start',
            ),
            'pending'=>array(
                'condition'=>'status=4',
                'order'=>'date_start',
            ),
        );
    }


	public function attributeLabels()
	{
		return array(
			'id' => '#',
			'author' => 'Автор(ы)',
			'id_league' => 'Лига',
            		// команды/одиночки
			'type' => 'Тип',
			'id_style' => 'Формат',
			'date_start' => 'Время старта',
			'date_finish' => 'Время финиша',
			'title' => 'Название',
			'descr' => 'Описание',
            		// прямая/параллельная
        		'mode' => 'Ход игры',
			'money' => 'Взнос',
		);
	}


    public static function getGamesByLeagues(){
        $criteria = new CDbCriteria();
        $criteria->condition = "id_league=1";

        $games = array();
        $games[0] = Game::model()->active()->findAll($criteria);
        $criteria->condition = "id_league=2";
        $games[1] = Game::model()->active()->findAll($criteria);
        $criteria->condition = "id_league=3";
        $games[2] = Game::model()->active()->findAll($criteria);

        return $games;
    }

    public function hasUser($id){
        $ret = 0;
        foreach($this->users as $user){
            if($user->id_user==$id) $ret = 1;
        }
        return $ret;
    }

    public function getLastLevel(){
        // переписать на MAX NUMBER
        if(sizeof($this->levels)) {
            $ret = sizeof($this->levels)+1;
        }
        else $ret = 1;
        return $ret;
    }

    public function setData($data){
        $this->attributes=$data;
        $date = explode("-",$data['date_start']);
        $time = explode(":",$data['time_start']);
        $this->date_start = mktime($time[0],$time[1],0,$date[1],$date[2],$date[0]);
        $date = explode("-",$data['date_finish']);
        $time = explode(":",$data['time_finish']);
        $this->date_finish = mktime($time[0],$time[1],0,$date[1],$date[2],$date[0]);

        $authors = explode(',',$data['author']);
        $auth = array();

        foreach($authors as $author) {
            if($user = User::model()->findByAttributes(array("username"=>trim($author))))
                $auth[] = $user->id;
        }

        $this->author = serialize($auth);

        if($this->save()) $ret = 1;
        else {
            $ret = 0;
            print_r($this->getErrors());
        }
        return $ret;
    }
    
    public function getAuthorsAr(){
        $authors = unserialize($this->author);
        if(sizeof($authors)<2) $ret = User::get($authors[0]);
        else {
            $ret = array();
            foreach($authors as $auth) {
                $ret[] = User::get($auth);
            }
        }

        return $ret;
    }


    public function getState(){
        if(time()>$this->date_start) $ret = 'start';
        else $ret = 'join';
        return $ret;
    }

    public function getLinkTitle(){

        $tr = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        );
        return strtr($this->title,$tr);
    }


    public static function countByLeague($id) {
        return Game::model()->countByAttributes(array("id_league"=>$id));
    }

    public function getDateStart(){
        return CDate::prepare($this->date_start);
    }

    public function getDateFinish(){
        return CDate::prepare($this->date_finish);
    }

    public function calculateFund(){
        return 100;
    }

    public function addPenalty($hint) {
        $gameuser = GameUsers::model()->findByAttributes(array("id_user"=>Yii::app()->user->getId(),"id_game"=>$hint->level->id_game,"level"=>$hint->level->number));
        $gameuser->penalty += $hint->cost;
        if($gameuser->save()) $ret = 1;
        else $ret = 0;
        return $ret;
    }

    public function addBonus($bonus,$level){
        $gameuser = GameUsers::model()->findByAttributes(array("id_user"=>Yii::app()->user->getId(),"id_game"=>$level->id_game,"level"=>$level->number));
        $gameuser->bonus += $bonus->bonus;
        if($gameuser->save()) $ret = 1;
        else $ret = 0;
        return $ret;
    }

    public function getEndTime(){
        $ret = 0;
        if($this->date_finish > time()) {
            $ret = $this->date_finish - time();
        }
        return $ret;
    }

    public function getUserGameStat(){
        $criteria = new CDbCriteria;
        $criteria->condition = "id_game=".$this->id;
        $criteria->order = "level DESC, date DESC";
        $criteria->limit = 30;
        $models = GameUsers::model()->findAll($criteria);
        $ret = array();
        foreach($models as $k=>$model) {
            if($model->id_user == Yii::app()->user->getId()) {
                $ret['place'] = $k+1;
                $ret['bonus'] = $model->bonus;
                $ret['penalty'] = $model->penalty;
            }
        }
        if(!isset($ret['place'])) {
                $ret['place'] = "30+";
                $mod = GameUsers::model()->findByAttributes(array("id_game"=>$this->id,"id_user"=>Yii::app()->user->getId()));
                $ret['bonus'] = $mod->bonus;
                $ret['penalty'] = $mod->penalty;
        }
        return $ret;
    }

    public function isCompleted(){
        $ret = 0;
        // check levels done
        $levelsdone = Gameplay::model()->countByAttributes(array("id_user"=>Yii::app()->user->getId(),"id_game"=>$this->id,"type"=>1,"status"=>1));
        if($levelsdone>=sizeof($this->levels)) $ret = 1;
        return $ret;
    }

    public function getPoints($k){
        if($k>10) $ret = 0;
        else {
            $ret = 11 - $k;
        }
        return $ret;
    }

    public function getPlaceClass($place){
        $ret = '';
        if(!$place) $ret = 'gold';
        if($place==1) $ret = 'silver';
        if($place==2) $ret = 'bronze';
        if($place>2 && $place<11) $ret = 'prize';
        return $ret;
    }

    public function getUserPlace(){
        $ret = 0;
        $models = GameUsers::model()->findAllByAttributes(array("id_game"=>$this->id,"status"=>2));
        foreach($models as $k=>$model){
            if($model->id_user == Yii::app()->user->getId()) $ret = ++$k;
        }
        return $ret;
    }

    public function getFinished(){
        $models = GameUsers::model()->findAllByAttributes(array("id_game"=>$this->id,"status"=>2));
        return $models;
    }

	public function search()
	{

	
		$criteria=new CDbCriteria;
		$criteria->compare('id',$this->id);
		$criteria->compare('author',$this->id_author);
		$criteria->compare('id_league',$this->id_league);
		$criteria->compare('type',$this->type);
		$criteria->compare('id_style',$this->id_style);
		$criteria->compare('date_start',$this->date_start,true);
		$criteria->compare('date_finish',$this->date_finish,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('descr',$this->descr,true);
		$criteria->compare('mode',$this->descr,true);
		$criteria->compare('money',$this->money);
	
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
