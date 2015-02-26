<?php

namespace amilna\versioning\models;

use Yii;

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
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%versioning_group}}';
    }

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
            'owner_id' => Yii::t('app', 'Owner ID'),
            'status' => Yii::t('app', 'Status'),
            'time' => Yii::t('app', 'Time'),
            'isdel' => Yii::t('app', 'Isdel'),
        ];
    }	
    
	public function itemAlias($list,$item = false,$bykey = false)
	{
		$lists = [
			/* example list of item alias for a field with name field
			'afield'=>[							
							0=>Yii::t('app','an alias of 0'),							
							1=>Yii::t('app','an alias of 1'),														
						],			
			*/			
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
}
