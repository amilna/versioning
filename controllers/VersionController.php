<?php

namespace amilna\versioning\controllers;

use Yii;
use amilna\versioning\models\Version;
use amilna\versioning\models\VersionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * VersionController implements the CRUD actions for Version model.
 */
class VersionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'apply' => ['post'],
                ],
            ],
        ];
    }
	
	public function actionApply($id)
	{
		$model = $this->findModel($id);
		
		if ($model) {			
			if (!$model->status)
			{			
				$route_id = $model->route_id;
				
				$versions = VersionSearch::find()			
						->andWhere("route_id = :route_id OR concat(',',substring(route_ids from 2 for (length(route_ids)-2)),',') LIKE :lk",[":route_id"=>$route_id,":lk"=>'%,'.$route_id.',%'])
						->all();								
										
				$res = true;		
				$transaction = Yii::$app->db->beginTransaction();
				try {
					foreach ($versions as $model)
					{
						$record_id = $model->record->record_id;
						$modelClass = $model->record->model;
						
						if ($record_id != null)
						{
							$origin = Version::findOne(["record_id"=>$model->record_id,"status"=>true]);
							$origin->status = ($origin->id != $model->id?false:true);
							$origin->save();												
						}	
						else
						{
							$done = Version::findOne(["record_id"=>$model->record_id,"route_id"=>$route_id]);
							if (!$done)
							{
								$recs = Version::findAll(["record_id"=>$model->record_id]);							
								foreach ($recs as $r)
								{
									$sql = "";
									$key = [];
									foreach (json_decode($r->record_attributes) as $a=>$v)
									{
										$sql .= ($sql == ""?"":" AND ").$a.($v === null?" is null":" = :".$a);
										if ($v !== null)
										{
											$key[":".$a] = $v;	
										}
									}									
									$rmod = $modelClass::find()->where($sql,$key)->one();								
									if ($rmod && !in_array($route_id,json_decode($r->route_ids)))
									{
										$modelClass::deleteAll($sql,$key);
									}
									$r->route_id = $route_id;								
									$r->save();
								}																																	
							}
						}				
						
						$version = $model->version;
						$model->status = true;
						
						if (($record_id == null && $version->isNewRecord) || $record_id != null)
						{
							$res = (!$version->save()?false:$res);
						}					
						
						$res = (!$model->save()?false:$res);
					}
					
					if ($res)
					{
						$transaction->commit();
					}
					else
					{
						$transaction->rollBack();
					}
				} catch (Exception $e) {
					$transaction->rollBack();				
				}
			}																					
		}
		
		return $this->redirect(['index']);
	}
	
    /**
     * Lists all Version models.
     * @params string $format, array $arraymap, string $term
     * @return mixed
     */
    public function actionIndex($format= false,$arraymap= false,$term = false)
    {
        $searchModel = new VersionSearch();        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams+($term?['VersionSearch'=>['term'=>$term]]:[]));

        if ($format == 'json')
        {
			$model = [];
			foreach ($dataProvider->getModels() as $d)
			{
				$obj = $d->attributes;
				if ($arraymap)
				{
					$map = explode(",",$arraymap);
					if (count($map) == 1)
					{
						$obj = $d[$arraymap];
					}
					else
					{
						$obj = [];					
						foreach ($map as $a)
						{
							$k = explode(":",$a);						
							$v = (count($k) > 1?$k[1]:$k[0]);
							$obj[$k[0]] = ($v == "Obj"?json_encode($d->attributes):(isset($d->$v)?$d->$v:null));
						}
					}
				}
				
				if ($term)
				{
					if (!in_array($obj,$model))
					{
						array_push($model,$obj);
					}
				}
				else
				{	
					array_push($model,$obj);
				}
			}			
			return \yii\helpers\Json::encode($model);	
		}
		else
		{
			return $this->render('index', [
				'searchModel' => $searchModel,
				'dataProvider' => $dataProvider,
			]);
		}	
    }

    /**
     * Displays a single Version model.
     * @param integer $id
     * @additionalParam string $format
     * @return mixed
     */
    public function actionView($id,$format= false)
    {
        $model = $this->findModel($id);
        
        if ($format == 'json')
        {
			return \yii\helpers\Json::encode($model);	
		}
		else
		{
			return $this->render('view', [
				'model' => $model,
			]);
		}        
    }

    /**
     * Creates a new Version model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Version();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Version model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Version model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {        
		$model = $this->findModel($id);        
        $model->isdel = 1;
        if ($model->status)
        {
			$model->status = false;
			$parent = $model->parents(1)->one();
			if (!$parent)
			{
				$parent = $model->children(1)->one();
			}
			$parent->status = true;
			$version = $parent->version;
			$parent->save();
			$version->save();
		}
        $model->makeRoot();
        $model->save();
        //$model->delete(); //this will true delete
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the Version model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Version the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Version::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
