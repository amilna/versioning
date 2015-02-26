<?php

namespace amilna\versioning\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\versioning\models\Version;

/**
 * VersionSearch represents the model behind the search form about `amilna\versioning\models\Version`.
 */
class VersionSearch extends Version
{

	
	public $recordModel;
	public $time;
	public $routeUser;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'route_id', 'record_id', 'type', 'isdel'], 'integer'],
            [['record_attributes','time','routeUser','recordModel'/*, 'recordId', 'routeId'*/], 'safe'],
            [['status'], 'boolean'],
        ];
    }

	/* uncomment to undisplay deleted records (assumed the table has column isdel) */
	public static function find()
	{
		return parent::find()->where([Version::tableName().'.isdel' => 0]);
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
				array_push($params,["like", "lower(".($tab?$tab.".":"").$field.")", strtolower($this->$field)]);
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
					if (in_array($number[0],['>','>=','<','<=']) && is_numeric($number[1]))
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
					if (substr($time[0],0,2) == "< " || substr($time[0],0,2) == "> " || substr($time[0],0,2) == "<=" || substr($time[0],0,2) == ">=") 
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
        
                
        $query->joinWith(['record', 'route','route.user']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $userClass = Yii::$app->getModule('versioning')->userClass;
        
        /* uncomment to sort by relations table on respective column */
		$dataProvider->sort->attributes['recordModel'] = [			
			'asc' => ['{{%versioning_record}}.model,{{%versioning_record}}.id' => SORT_ASC],
			'desc' => ['{{%versioning_record}}.model,{{%versioning_record}}.id' => SORT_DESC],
		];
		$dataProvider->sort->attributes['time'] = [			
			'asc' => ['{{%versioning_route}}.time' => SORT_ASC],
			'desc' => ['{{%versioning_route}}.time' => SORT_DESC],
		];
		$dataProvider->sort->attributes['routeUser'] = [			
			'asc' => [$userClass::tableName().'username' => SORT_ASC],
			'desc' => [$userClass::tableName().'username' => SORT_DESC],
		];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }				
		
        $query->andFilterWhere([
            'status' => $this->status,
            /*['id','{{%record}}']
            ['id','{{%route}}']*/
        ]);

        $params = self::queryNumber([['id'],['route_id'],['record_id'],['type'],['isdel']/*['id','{{%record}}'],['id','{{%route}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryString([['record_attributes']/*['id','{{%record}}'],['id','{{%route}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
		$params = self::queryTime([['time','{{%versioning_route}}']]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
		
		$query->andFilterWhere(["like","lower(concat({{%versioning_record}}.model,':',{{%versioning_record}}.record_id))",strtolower($this->recordModel)]);
		$query->andFilterWhere(['like','lower('.$userClass::tableName().'.username)',strtolower($this->routeUser)]);
				
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

        return $dataProvider;
    }
}
