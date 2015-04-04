<?php

namespace amilna\versioning\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\versioning\models\Record;

/**
 * RecordSearch represents the model behind the search form about `amilna\versioning\models\Record`.
 */
class RecordSearch extends Record
{

	
	public $ownerUsername;
	public $groupTitle;
	public $recordModel;
	/*public $versionsId;*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'record_id', 'owner_id', 'group_id', 'isdel'], 'integer'],
            [['model', 'viewers', 'groupTitle', 'recordModel','ownerUsername'/*, 'ownerId', 'groupId', 'versionsId'*/], 'safe'],
            [['filter_viewers'], 'boolean'],
        ];
    }

	/* uncomment to undisplay deleted records (assumed the table has column isdel) */
	public static function find()
	{
		return parent::find()->where([Record::tableName().'.isdel' => 0]);
	}
	
	public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'ownerUsername' => Yii::t('app', 'Owner'),                        
            'recordModel' => Yii::t('app', 'Model'),
            'groupTitle' => Yii::t('app', 'Group'),
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
        
                
        $query->joinWith(['owner', 'group' /* 'versions'*/]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $userClass = Yii::$app->getModule('versioning')->userClass;
        
        /* uncomment to sort by relations table on respective column */
		$dataProvider->sort->attributes['ownerUsername'] = [			
			'asc' => [$userClass::tableName().'.username' => SORT_ASC],
			'desc' => [$userClass::tableName().'.username' => SORT_DESC],
		];
		$dataProvider->sort->attributes['groupTitle'] = [			
			'asc' => ['{{%versioning_group}}.title' => SORT_ASC],
			'desc' => ['{{%versioning_group}}.title' => SORT_DESC],
		];
		$dataProvider->sort->attributes['recordModel'] = [			
			'asc' => ['concat({{%versioning_record}}.model,{{%versioning_record}}.id)' => SORT_ASC],
			'desc' => ['concat({{%versioning_record}}.model,{{%versioning_record}}.id)' => SORT_DESC],
		];
		
		

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }				
		
        $query->andFilterWhere([
            'filter_viewers' => $this->filter_viewers,
            /*['id','{{%owner}}']
            ['id','{{%group}}']
            ['id','{{%versions}}']*/
        ]);

        $params = self::queryNumber([['id',$this->tableName()],['record_id'],['owner_id',$this->tableName()],['group_id'],['isdel']/*['id','{{%owner}}'],['id','{{%group}}'],['id','{{%versions}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryString([['model'],['viewers']/*['id','{{%owner}}'],['id','{{%group}}'],['id','{{%versions}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}	
		
		$query->andFilterWhere(['like','lower('.$userClass::tableName().'.username)',strtolower($this->ownerUsername)]);
		$query->andFilterWhere(['like','lower({{%versioning_group}}.title)',strtolower($this->groupTitle)]);
		$query->andFilterWhere(["like","lower(concat({{%versioning_record}}.model,' ',{{%versioning_record}}.record_id))",strtolower($this->recordModel)]);
			
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
