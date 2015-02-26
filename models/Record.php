<?php

namespace amilna\versioning\models;

use Yii;

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
        return $this->hasMany(Version::className(), ['record_id' => 'id']);
    }
}
