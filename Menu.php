<?php

/**
 * This is the model class for table "menu".
 *
 * The followings are the available columns in table 'menu':
 * @property integer $id
 * @property string $type
 * @property integer $id_item
 * @property integer $weight
 * @property integer $id_parent
 */
class Menu extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Menu the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'menu';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, id_item, weight, id_parent', 'required'),
			array('id_item, weight, id_parent', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, id_item, weight, id_parent', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type' => 'Type',
			'id_item' => 'Id Item',
			'weight' => 'Порядок',
			'id_parent' => 'Id Parent',
		);
	}

    public function getItem(){
        $class = ucfirst($this->type);
        if($class=='Content') $class = 'ContentType';
        $data = $class::model()->findByPk($this->id_item);
        return $data;
    }

    protected function getLink(){
        $ret = '';
        if($this->type=='content') $ret = "/contenttype/";
        if($this->type=='module') $ret = "/module/";
        if($this->type=='page') $ret = "/page/";
        return $ret;
    }

    public function getItems(){
        $data = array();
        $models = Menu::model()->findAllByAttributes(array("id_parent"=>0),array('order'=>'id ASC'));
        foreach($models as $model) {
            unset($ar);
            $ar['label'] = ($model->type=="sub") ? $model->name : $model->getItem()->title;
            $ar['url'] = ($model->type=="sub") ? '#' : $model->getLink().$model->id_item;
                if($model->getChildren()) {
                $ar['items'] = array();
                foreach($model->getChildren() as $child) {
                    $arr['label'] = $child->getItem()->title;
                    $arr['url'] = ($child->type=="sub") ? '#' : $child->getLink().$child->id_item;
                    $ar['items'][] = $arr;
                }
            }
            $data[] = $ar;
        }
        return $data;
    }

    public function getMenuClass(){
        if($this->type=='sub') $class = 'admin-menu-sub';
        if($this->type=='content') $class = 'admin-menu-content';
        if($this->type=='page') $class = 'admin-menu-page';
        if($this->type=='module') $class = 'admin-menu-module';
        return $class;
    }

    public function getChildren(){
        $ret = 0;
        $models = Menu::model()->findAllByAttributes(array("id_parent"=>$this->id));
        if(sizeof($models)) $ret = $models;
        return $ret;
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('id_item',$this->id_item);
		$criteria->compare('weight',$this->weight);
		$criteria->compare('id_parent',$this->id_parent);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}