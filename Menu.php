<?php
/*
 * @property integer $id
 * @property string $type
 * @property integer $id_item
 * @property integer $weight
 * @property integer $id_parent
 */
class Menu extends CActiveRecord
{

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'menu';
	}

	public function rules()
	{
		return array(
			array('type, id_item, weight, id_parent', 'required'),
			array('id_item, weight, id_parent', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>255),
			array('id, type, id_item, weight, id_parent', 'safe', 'on'=>'search'),
		);
	}

	public function relations()
	{
		return array(
		);
	}

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

    public function search(){

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