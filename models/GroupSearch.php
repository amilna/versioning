<?php

namespace amilna\versioning\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\versioning\models\Group;

/**
 * GroupSearch represents the model behind the search form about `amilna\versioning\models\Group`.
 */
class GroupSearch extends Group
{

	
	/*public $recordsId;*/
	public $ownerUsername;
	/*public $memberId;*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'status', 'isdel'], 'integer'],
            [['title', 'description', 'time','ownerUsername'/*, 'recordsId', 'ownerId', 'memberId'*/], 'safe'],
        ];
    }

	/* uncomment to undisplay deleted records (assumed the table has column isdel) */
	public static function find()
	{
		return parent::find()->where([Group::tableName().'.isdel' => 0]);
	}
	
	public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'ownerUsername' => Yii::t('app', 'Owner'),                        
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

	private function queryString($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				if (substr($this->$field,0,2) == "< " || substr($this->$field,0,2) == "> " || substr($this->$field,0,2) == "<=" || substr($this->$field,0,2) == ">=" || substr($this->$field,0,2) == "<>") 
				{					
					array_push($params,[str_replace(" ","",substr($this->$field,0,2)), "lower(".($tab?$tab.".":"").$field.")", strtolower(trim(substr($this->$field,2)))]);
				}
				else
				{					
					array_push($params,["like", "lower(".($tab?$tab.".":"").$field.")", strtolower($this->$field)]);
				}				
			}
		}	
		return $params;
	}	
	
	private function queryNumber($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$number = explode(" ",trim($this->$field));							
				if (count($number) == 2)
				{									
					if (in_array($number[0],['>','>=','<','<=','<>']) && is_numeric($number[1]))
					{
						array_push($params,[$number[0], ($tab?$tab.".":"").$field, $number[1]]);	
					}
				}
				elseif (count($number) == 3)
				{															
					if (is_numeric($number[0]) && is_numeric($number[2]))
					{
						array_push($params,['>=', ($tab?$tab.".":"").$field, $number[0]]);		
						array_push($params,['<=', ($tab?$tab.".":"").$field, $number[2]]);		
					}
				}
				elseif (count($number) == 1)
				{					
					if (is_numeric($number[0]))
					{
						array_push($params,['=', ($tab?$tab.".":"").$field, str_replace(["<",">","="],"",$number[0])]);		
					}	
				}
			}
		}	
		return $params;
	}
	
	private function queryTime($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$time = explode(" - ",$this->$field);			
				if (count($time) > 1)
				{								
					array_push($params,[">=", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);	
					array_push($params,["<=", "concat('',".($tab?$tab.".":"").$field.")", $time[1]." 24:00:00"]);
				}
				else
				{
					if (substr($time[0],0,2) == "< " || substr($time[0],0,2) == "> " || substr($time[0],0,2) == "<=" || substr($time[0],0,2) == ">=" || substr($time[0],0,2) == "<>") 
					{					
						array_push($params,[str_replace(" ","",substr($time[0],0,2)), "concat('',".($tab?$tab.".":"").$field.")", trim(substr($time[0],2))]);
					}
					else
					{					
						array_push($params,["like", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);
					}
				}	
			}
		}	
		return $params;
	}

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->find();
        
                
        $query->joinWith([/*'records', 'owner', 'member'*/]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
         $userClass = Yii::$app->getModule('versioning')->userClass;
        
        /* uncomment to sort by relations table on respective column */
		$dataProvider->sort->attributes['ownerUsername'] = [			
			'asc' => [$userClass::tableName().'.username' => SORT_ASC],
			'desc' => [$userClass::tableName().'.username' => SORT_DESC],
		];
		/*
		$dataProvider->sort->attributes['ownerId'] = [			
			'asc' => ['{{%owner}}.id' => SORT_ASC],
			'desc' => ['{{%owner}}.id' => SORT_DESC],
		];
		$dataProvider->sort->attributes['memberId'] = [			
			'asc' => ['{{%member}}.id' => SORT_ASC],
			'desc' => ['{{%member}}.id' => SORT_DESC],
		];*/

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }				
		
        $params = self::queryNumber([['id',$this->tableName()],['owner_id'],['status'],['isdel']/*['id','{{%records}}'],['id','{{%owner}}'],['id','{{%member}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryString([['title'],['description']/*['id','{{%records}}'],['id','{{%owner}}'],['id','{{%member}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryTime([['time']/*['id','{{%records}}'],['id','{{%owner}}'],['id','{{%member}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}		
		/* example to use search all in field1,field2,field3 or field4
		if ($this->term)
		{
			$query->andFilterWhere(["OR","lower(field1) like '%".strtolower($this->term)."%'",
				["OR","lower(field2) like '%".strtolower($this->term)."%'",
					["OR","lower(field3) like '%".strtolower($this->term)."%'",
						"lower(field4) like '%".strtolower($this->term)."%'"						
					]
				]
			]);	
		}	
		*/
		
		$query->andFilterWhere(['like','lower('.$userClass::tableName().'.username)',strtolower($this->ownerUsername)]);

        return $dataProvider;
    }
}
