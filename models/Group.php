<?php

namespace amilna\versioning\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%versioning_group}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $owner_id
 * @property integer $status
 * @property string $time
 * @property integer $isdel
 *
 * @property VersioningRecord[] $versioningRecords
 * @property User $owner
 * @property VersioningGrpUsr[] $versioningGrpUsrs
 */
class Group extends \yii\db\ActiveRecord
{
    public $dynTableName = '{{%versioning_group}}';    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {        
        $mod = new Group();        
        return $mod->dynTableName;
    }
    
    public $memberJson;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'owner_id', 'status'], 'required'],
            [['description'], 'string'],
            [['owner_id', 'status', 'isdel'], 'integer'],
            [['time'], 'safe'],
            [['title'], 'string', 'max' => 65],
            [['title'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'owner_id' => Yii::t('app', 'Owner'),
            'status' => Yii::t('app', 'Status'),
            'time' => Yii::t('app', 'Time'),
            'isdel' => Yii::t('app', 'Isdel'),
        ];
    }	
    
	public function itemAlias($list,$item = false,$bykey = false)
	{
		$userClass = Yii::$app->getModule('versioning')->userClass;
		$owner = ArrayHelper::map($userClass::find()->all(), 'id', 'username');
		
		$lists = [
			/* example list of item alias for a field with name field */
			'status'=>[							
							0=>Yii::t('app','Disabled'),							
							1=>Yii::t('app','Active'),														
						],									
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
    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['group_id' => 'id']);
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
    public function getMember()
    {
        return $this->hasMany(GrpUsr::className(), ['group_id' => 'id']);
    }
    
    public function getAvailableUsers()
    {
		$userClass = Yii::$app->getModule('versioning')->userClass;
		return $userClass::find()->where(['not in','id',$this->member])->all();
		//return ArrayHelper::map($userClass::find()->where(['not in','id',$this->member])->all(),"id","username");
	}
	
	public function getMemberId()
    {		
		$members = $this->db->createCommand("SELECT array_agg(user_id) as id FROM ".GrpUsr::tableName()."
				WHERE group_id = :id AND isdel = 0")->bindValues([":id"=>$this->id])->queryScalar();								
		
		return json_decode(str_replace(["{","}"],["[","]"],$members));
	}
}
