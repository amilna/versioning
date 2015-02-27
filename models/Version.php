<?php

namespace amilna\versioning\models;

use Yii;
use amilna\versioning\components\Libs;
use creocoder\nestedsets\NestedSetsBehavior;

/**
 * This is the model class for table "{{%versioning_version}}".
 *
 * @property integer $id
 * @property integer $route_id
 * @property integer $record_id
 * @property string $attributes
 * @property integer $type
 * @property boolean $status
 * @property integer $isdel
 *
 * @property VersioningRecord $record
 * @property VersioningRoute $route
 */
class Version extends \yii\db\ActiveRecord
{    
  
    public function behaviors() {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                'treeAttribute' => 'tree',
                'leftAttribute' => 'lft',
                'rightAttribute' => 'rgt',
                'depthAttribute' => 'depth',
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }
	
    public static function find()
    {
        return new VersionQuery(get_called_class());
    }
    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%versioning_version}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route_id', 'record_id', 'type'], 'required'],
            [['route_id', 'record_id', 'type', 'isdel'], 'integer'],
            [['record_attributes','route_ids'], 'string'],
            [['status'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'route_id' => Yii::t('app', 'Route ID'),
            'record_id' => Yii::t('app', 'Record ID'),
            'record_attributes' => Yii::t('app', 'Attributes'),
            'type' => Yii::t('app', 'Type'),
            'status' => Yii::t('app', 'Status'),
            'isdel' => Yii::t('app', 'Isdel'),
        ];
    }	
    
	public function itemAlias($list,$item = false,$bykey = false)
	{
		$lists = [
			/* example list of item alias for a field with name field */
			'type'=>[							
						0=>\yii\db\ActiveRecord::EVENT_AFTER_DELETE,
						1=>\yii\db\ActiveRecord::EVENT_AFTER_INSERT,
						2=>\yii\db\ActiveRecord::EVENT_BEFORE_UPDATE,
						3=>\yii\db\ActiveRecord::EVENT_AFTER_UPDATE,
					],	
			'status'=>[							
						0=>Yii::t('app','Not Active'),
						1=>Yii::t('app','Active'),						
					],							
						
		];				
		
		if (isset($lists[$list]))
		{					
			if ($bykey)
			{				
				$nlist = [];
				foreach ($lists[$list] as $k=>$i)
				{
					$nlist[$i] = $k;
				}
				$list = $nlist;				
			}
			else
			{
				$list = $lists[$list];
			}
							
			if ($item !== false)
			{			
				return	(isset($list[$item])?$list[$item]:false);
			}
			else
			{
				return $list;	
			}			
		}
		else
		{
			return false;	
		}
	}    
    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecord()
    {
        return $this->hasOne(Record::className(), ['id' => 'record_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoute()
    {
        return $this->hasOne(Route::className(), ['id' => 'route_id']);
    }        
    
    public function getVersion()
    {
		$modelClass = $this->record->model;
		$record_id = $this->record->record_id;
		if ($record_id == null) {
			$sql = "";
			$key = [];
			foreach (json_decode($this->record_attributes) as $a=>$v)
			{
				$sql .= ($sql == ""?"":" AND ").$a.($v === null?" is null":" = :".$a);
				if ($v !== null)
				{
					$key[":".$a] = $v;	
				}
			}			
			$model = $modelClass::find()->where($sql,$key)->one();			
		}
		else
		{
			$model = $modelClass::findOne($record_id);		
		}
		
		if (!$model) {
			$model = new $modelClass();					
		}
		
		$parents = $this->parents()->orderBy("depth")->all();		
		
		$attributes = [];
		foreach ($parents as $p)
		{
			$attr = json_decode($p->record_attributes);
			foreach ($attr as $a=>$v)
			{
				$attributes[$a] = $v;
			}
		}
		
		$attr = json_decode($this->record_attributes);
		foreach ($attr as $a=>$v)
		{
			$attributes[$a] = $v;
		}				
		
		if (count($attributes) > 0) {
			$model->attributes = $attributes;
		}
		
		if ($model->isNewRecord && isset($model->id))
		{
			$model->id = null;
		}
				
		return $model;	
		
	}	
     
}
