<?php

namespace amilna\versioning\controllers;

use Yii;
use amilna\versioning\models\Record;
use amilna\versioning\models\RecordSearch;
use amilna\versioning\models\VersionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RecordController implements the CRUD actions for Record model.
 */
class RecordController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Record models.
     * @params string $format, array $arraymap, string $term
     * @return mixed
     */
    public function actionIndex($format= false,$arraymap= false,$term = false)
    {
        $searchModel = new RecordSearch();        
        $req = Yii::$app->request->queryParams;
        if ($term) { $req[basename(str_replace("\\","/",get_class($searchModel)))]["term"] = $term;}        
        $dataProvider = $searchModel->search($req);	
        $query = $dataProvider->query;
        
        $module = Yii::$app->getModule("versioning");
        $allow = false;
        if (isset(Yii::$app->user->identity->isAdmin))
		{
			$allow = Yii::$app->user->identity->isAdmin;
		}
		else
		{
			$allow = in_array(Yii::$app->user->identity->username,$module->admins);
		}																		                
        
        if (!$allow)
        {
			$query->andWhere([Record::tableName().'.owner_id'=>Yii::$app->user->id]);
		}
        
        $dataProvider->pagination = [
			"pageSize"=>20	
		];
		
		if (Yii::$app->request->post('hasEditable')) {			
			$Id = Yii::$app->request->post('editableKey');
			$model = Record::findOne($Id);
	 
			$out = json_encode(['id'=>$Id,'output'=>'', 'message'=>'','data'=>'null']);				
			
			$post = [];						
			
			$posted = current($_POST['RecordSearch']);			
			$post['Record'] = $posted;												
			
			if ($model->owner_id == Yii::$app->user->id)
			{			
				$transaction = Yii::$app->db->beginTransaction();
				try {				
					if ($model->load($post)) {													
						
						$model->attributes;										
						$model->owner_id = (isset($posted['ownerUsername'])?$posted['ownerUsername']:$model->owner_id);
						$model->group_id = (isset($posted['groupTitle'])?$posted['groupTitle']:$model->group_id);
						
						$model->save();	
						
						$output = '';	 	
						if (isset($posted['groupTitle'])) {				   
							$output =  $model->itemAlias('groups',$model->group_id); // new value for edited td					   
							$data = [];
							
							$version = VersionSearch::find()->where(['record_id'=>$model->id,'status'=>true])->one();
							$versions = $version->route->versions;
							
							foreach ($versions as $v)
							{
								$v->record->group_id = $model->group_id;
								$v->record->save();							   
							}
						} 
						
						if (isset($posted['ownerUsername'])) {				   
							$output =  $model->itemAlias('owner',$model->owner_id); // new value for edited td					   
							$data = [];
							
							$version = VersionSearch::find()->where(['record_id'=>$model->id,'status'=>true])->one();
							$versions = $version->route->versions;
							
							foreach ($versions as $v)
							{
								$v->record->owner_id = $model->owner_id;
								$v->record->save();							   
							}
						}
						
						if (isset($posted['filter_viewers'])) {				   
						   $output =  $model->itemAlias('filter_viewers',$model->filter_viewers?1:0); // new value for edited td					   
						   $data = [];
						}
							 
						$out = json_encode(['id'=>$model->id,'output'=>$output, "data"=>$data,'message'=>'']);
					} 			
					$transaction->commit();				
				} catch (Exception $e) {
					$transaction->rollBack();
				}
			}
									
			echo $out;
			return;
		}
		
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
						$obj = (isset($d[$arraymap])?$d[$arraymap]:null);
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
     * Displays a single Record model.
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
     * Creates a new Record model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Record();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Record model.
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
     * Deletes an existing Record model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {        
		$model = $this->findModel($id);        
        $model->isdel = 1;
        $model->save();
        //$model->delete(); //this will true delete
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the Record model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Record the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Record::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
