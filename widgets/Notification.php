<?php
namespace amilna\versioning\widgets;

use Yii;
use yii\helpers\Html;
use yii\base\Widget;
use yii\helpers\Json;

use amilna\versioning\models\VersionSearch;
use amilna\versioning\models\Version;
use amilna\versioning\models\Record;
use amilna\versioning\models\Route;

class Notification extends Widget
{    	
	public $models = [];
	public $route = []; // model => [route,[params]]
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
		$query->andWhere([Version::tableName().".status"=>true])
			->andWhere(Record::tableName().".record_id is not null");
				
		if (count($this->models) > 0)
		{										
			$query->andWhere([Record::tableName().".model"=>$this->models])
				->andWhere(Record::tableName().".filter_viewers = false");
		}				
		
		if ($user_id > 0)
		{	
			$query->andWhere("concat(',',".Record::tableName().".viewers,',') not like '%,".$user_id.",%'")
				->andWhere(Record::tableName().".filter_viewers = false OR (".Record::tableName().".filter_viewers = true AND (".Record::tableName().".owner_id = :uid OR ".Record::tableName().".group_id in (".implode(",",$groups).")) )",[":uid"=>$user_id]);
		}
		else
		{
			$query->limit(10);		
		}											
		
		$query->orderBy(Route::tableName().".time DESC,".Version::tableName().".id DESC");
						
		$script = "		
		" . PHP_EOL;
	
		$view->registerJs($script);		
		
		echo $this->render($this->viewPath,['searchModel'=>$searchModel,'dataProvider'=>$dataProvider,'module'=>$module,'widget'=>$this]);
        
    }
        
}
