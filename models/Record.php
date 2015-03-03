<?php

namespace amilna\versioning\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%versioning_record}}".
 *
 * @property integer $id
 * @property string $model
 * @property integer $record_id
 * @property integer $owner_id
 * @property integer $group_id
 * @property string $viewers
 * @property integer $isdel
 *
 * @property User $owner
 * @property VersioningGroup $group
 * @property VersioningVersion[] $versioningVersions
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%versioning_record}}';
    }		
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model', 'owner_id'], 'required'],
            [['record_id', 'owner_id', 'group_id', 'isdel'], 'integer'],
            [['viewers'], 'string'],
            [['filter_viewers'], 'boolean'],
            [['model'], 'string', 'max' => 65]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model' => Yii::t('app', 'Model'),
            'record_id' => Yii::t('app', 'Record ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'viewers' => Yii::t('app', 'Viewers'),
            'filter_viewers' => Yii::t('app', 'Filter Viewers'),
            'isdel' => Yii::t('app', 'Isdel'),
        ];
    }	
    
	public function itemAlias($list,$item = false,$bykey = false)
	{
		$userClass = Yii::$app->getModule('versioning')->userClass;
		$owner = ArrayHelper::map($userClass::find()->all(), 'id', 'username');
		$groups = ArrayHelper::map(Group::find()->where(["isdel"=>0])->all(), 'id', 'title');		
		
		$lists = [
			/* example list of item alias for a field with name field */
			'filter_viewers'=>[														
							0=>Yii::t('app','Do not filter'),														
							1=>Yii::t('app','Filter viewers'),							
						],	
			'groups'=>$groups,					
			'owner'=>$owner,					
						
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
    public function getOwner()
    {
        $userClass = Yii::$app->getModule('versioning')->userClass;        
        return $this->hasOne($userClass::className(), ['id' => 'owner_id']);        
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(Version::className(), ['record_id' => 'id'])->where("isdel=0");
    }
    
    public function getVersion()
    {
		$modelClass = $this->model;
		$record_id = $this->record_id;
		
		$model = null;
		
		if (class_exists ($modelClass)) {
		
			$version = VersionSearch::find()->where(['record_id'=>$this->id,'status'=>true])->one();
			
			if ($record_id == null) {
				$sql = "";
				$key = [];
				foreach (json_decode($version->record_attributes) as $a=>$v)
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
			
			$parents = $version->parents()->orderBy("depth")->all();		
			
			$attributes = [];
			foreach ($parents as $p)
			{
				$attr = json_decode($p->record_attributes);
				foreach ($attr as $a=>$v)
				{
					$attributes[$a] = $v;
				}
			}
			
			$attr = json_decode($version->record_attributes);
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
		}		
		return $model;	
		
	}		
}
