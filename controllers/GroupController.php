<?php

namespace amilna\versioning\controllers;

use Yii;
use amilna\versioning\models\Group;
use amilna\versioning\models\GrpUsr;
use amilna\versioning\models\GroupSearch;
use amilna\versioning\models\RecordSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

use yii\helpers\Html;

/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends Controller
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
     * Lists all Group models.
     * @params string $format, array $arraymap, string $term
     * @return mixed
     */
    public function actionIndex($format= false,$arraymap= false,$term = false)
    {
        $searchModel = new GroupSearch();        
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
			$query->andWhere([Group::tableName().'.owner_id'=>Yii::$app->user->id]);
		}		
		
		if (Yii::$app->request->post('hasEditable')) {			
			$Id = Yii::$app->request->post('editableKey');
			$model = Group::findOne($Id);
	 
			$out = json_encode(['id'=>$Id,'output'=>'', 'message'=>'','data'=>'null']);	 			
			$post = [];						
			
			$posted = current($_POST['GroupSearch']);			
			$post['Group'] = $posted;												
			
			if ($model->owner_id == Yii::$app->user->id)
			{			
				$transaction = Yii::$app->db->beginTransaction();
				try {				
					if ($model->load($post)) {													
						
						$model->attributes;										
						$model->owner_id = (isset($posted['ownerUsername'])?$posted['ownerUsername']:$model->owner_id);						
						$model->save();	
						
						$output = '';	 							
						
						if (isset($posted['ownerUsername'])) {				   
							$output =  $model->itemAlias('owner',$model->owner_id); // new value for edited td					   
							$data = [];
						   
							$record = RecordSearch::find()->where(['record_id'=>$model->id,'model'=>get_class($model)])->one();
							$record->owner_id = $model->owner_id;
							$record->save();							   
							
						}
						
						if (isset($posted['status'])) {				   
						   $output =  $model->itemAlias('status',$model->status); // new value for edited td					   
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
     * Displays a single Group model.
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
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Group();
        $model->time = date('Y-m-d H:i:s');
        $model->isdel = 0;                
                
        $model->memberJson = json_encode($model->memberId);
		$post = Yii::$app->request->post();
		
        if (isset($post["Group"])) 
		{				
			$model->load($post);
			
			$transaction = Yii::$app->db->beginTransaction();
			try {				
			
				if ($model->save()) {
					
					$member = json_decode($post["Group"]["memberJson"]);
					$gu = GrpUsr::deleteAll("group_id = :id",["id"=>$model->id]);
							
					foreach ($member as $m)
					{
						$c = new GrpUsr();							
						$c->group_id = $model->id;
						$c->user_id = intval($m);
						$c->isdel = 0;					
						$c->save();				
					}											
				
					$transaction->commit();                                    
					return $this->redirect(['view', 'id' => $model->id]);
				}
				else
				{
					$transaction->rollBack();
				}				
			} catch (Exception $e) {
				$transaction->rollBack();
			}     
        }
        
        return $this->render('create', [
			'model' => $model,
		]);
    }

    /**
     * Updates an existing Group model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);				
		
        $model->memberJson = json_encode($model->memberId);        
		$post = Yii::$app->request->post();
		
        if (isset($post["Group"])) 
		{				
			$model->load($post);
			
			$transaction = Yii::$app->db->beginTransaction();
			try {				
				
				if ($model->save()) {
				
					$member = json_decode($post["Group"]["memberJson"]);
					$gu = GrpUsr::deleteAll("group_id = :id",["id"=>$model->id]);
																			
					foreach ($member as $m)
					{
						//$c = GrpUsr::find()->where(["group_id"=>$model->id,"user_id"=>intval($m)])->one();
						//if (!$c)
						//{
							$c = new GrpUsr();	
						//}					
						$c->group_id = $model->id;
						$c->user_id = intval($m);
						$c->isdel = 0;					
						$c->save();				
					}
															
					$transaction->commit();                                    
					return $this->redirect(['view', 'id' => $model->id]);
				}
				else
				{
					$transaction->rollBack();
				}				
			} catch (Exception $e) {
				$transaction->rollBack();
			}     
        }
        
        return $this->render('update', [
			'model' => $model,
		]);
    }

    /**
     * Deletes an existing Group model.
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
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Group the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
