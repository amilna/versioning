<?php
namespace amilna\versioning\widgets;

use Yii;
use yii\helpers\Html;
use yii\base\Widget;
use yii\helpers\Json;

use amilna\versioning\models\VersionSearch;

class Notification extends Widget
{    	
	public $models = [];
	public $viewPath = '@amilna/versioning/widgets/views/notification';
	
	private $bundle;

    public function init()
    {
        parent::init();
        $view = $this->getView();				
		$module = Yii::$app->getModule("versioning");
		$user_id = Yii::$app->user->id;
		$groups = \amilna\versioning\components\Libs::userGroups($user_id);
		$groups = [1];
		
		$bundle = NotificationAsset::register($view);
		$this->bundle = $bundle;
		
		$searchModel = new VersionSearch();
		$dataProvider = $searchModel->search([]);
		$query = $dataProvider->query;
		$query->andWhere(["{{%versioning_version}}.status"=>true])
			->andWhere("{{%versioning_record}}.record_id is not null");
				
		if (count($this->models) > 0)
		{										
			$query->andWhere(["{{%versioning_record}}.model"=>$this->models])
				->andWhere("{{%versioning_record}}.filter_viewers = false");
		}				
		
		if ($user_id > 0)
		{	
			$query->andWhere("concat(',',{{%versioning_record}}.viewers,',') not like '%,".$user_id.",%'")
				->andWhere("{{%versioning_record}}.filter_viewers = false OR ({{%versioning_record}}.filter_viewers = true AND ({{%versioning_record}}.owner_id = :uid OR {{%versioning_record}}.group_id in (".implode(",",$groups).")) )",[":uid"=>$user_id]);
		}
		else
		{
			$query->limit(10);		
		}											
		
		$query->orderBy('{{%versioning_route}}.time DESC,{{%versioning_version}}.id DESC');
						
		$script = "		
		" . PHP_EOL;
	
		$view->registerJs($script);		
		
		echo $this->render($this->viewPath,['searchModel'=>$searchModel,'dataProvider'=>$dataProvider,'module'=>$module,'widget'=>$this]);
        
    }
        
}
