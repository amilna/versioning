<?php

namespace amilna\versioning\models;

use Yii;

/**
 * This is the model class for table "{{%versioning_route}}".
 *
 * @property integer $id
 * @property string $route
 * @property integer $user_id
 * @property string $time
 * @property integer $isdel
 *
 * @property VersioningVersion[] $versioningVersions
 * @property User $user
 */
class Route extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%versioning_route}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['route', 'user_id'], 'required'],
            [['user_id', 'isdel'], 'integer'],
            [['time'], 'safe'],
            [['route'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'route' => Yii::t('app', 'Route'),
            'user_id' => Yii::t('app', 'User ID'),
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
    public function getVersions()
    {
        return $this->hasMany(Version::className(), ['route_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $userClass = Yii::$app->getModule('versioning')->userClass;        
        return $this->hasOne($userClass::className(), ['id' => 'user_id']);        
    }
}
