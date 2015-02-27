<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use amilna\yap\GridView;

/* @var $this yii\web\View */
/* @var $searchModel amilna\versioning\models\VersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Versions');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="version-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create {modelClass}', [
    'modelClass' => 'Version',
]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        
        'containerOptions' => ['style'=>'overflow: auto'], // only set when $responsive = false		
		'caption'=>Yii::t('app', 'Version'),
		'headerRowOptions'=>['class'=>'kartik-sheet-style','style'=>'background-color: #fdfdfd'],
		'filterRowOptions'=>['class'=>'kartik-sheet-style skip-export','style'=>'background-color: #fdfdfd'],
		'pjax' => false,
		'bordered' => true,
		'striped' => true,
		'condensed' => true,
		'responsive' => true,
		'hover' => true,
		'showPageSummary' => true,
		'pageSummaryRowOptions'=>['class'=>'kv-page-summary','style'=>'background-color: #fdfdfd'],
		
		'panel' => [
			'type' => GridView::TYPE_DEFAULT,
			'heading' => false,
		],
		'toolbar' => [
			['content'=>				
				Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['index'], ['data-pjax'=>false, 'class' => 'btn btn-default', 'title'=>Yii::t('app', 'Reset Grid')])
			],
			'{export}',
			'{toggleData}'
		],
		'beforeHeader'=>[
			[
				/* uncomment to use additional header
				'columns'=>[
					['content'=>'Group 1', 'options'=>['colspan'=>6, 'class'=>'text-center','style'=>'background-color: #fdfdfd']], 
					['content'=>'Group 2', 'options'=>['colspan'=>6, 'class'=>'text-center','style'=>'background-color: #fdfdfd']], 					
				],
				*/
				'options'=>['class'=>'skip-export'] // remove this row from export
			]
		],
		'floatHeader' => true,		
		
		/* uncomment to use megeer some columns */
        'mergeColumns' => ['time','routeUser'],
        'type'=>'firstrow', // or use 'simple'
        
        
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            [				
				'attribute' => 'time',
				'value' => 'route.time',				
				'filterType'=>GridView::FILTER_DATE_RANGE,
				'filterWidgetOptions'=>[
					'pluginOptions' => [
						'format' => 'YYYY-MM-DD HH:mm:ss',				
						'todayHighlight' => true,
						'timePicker'=>true,
						'timePickerIncrement'=>15,
						//'opens'=>'left'
					],
					'pluginEvents' => [
					"apply.daterangepicker" => 'function() {									
									$(this).change();
								}',
					],			
				],
			],
			[                 			
				'attribute'=>'routeUser',
				'value' => 'route.user.username'
            ],       			                                 
            [                 			
				'attribute'=>'recordModel',
				'format'=>'raw',
				'value' => function($data) {					
					$version = $data->getVersion();
					
					$rtml = $version?Html::encode(str_replace([",","/"],[", ","/ "],json_encode($version->attributes))):"";					
					foreach (json_decode($data->record_attributes) as $a=>$v)
					{
						$r = Html::encode(str_replace([",","/"],[", ","/ "],json_encode([$a=>$v])));						
						$rtml = str_replace(substr($r,1,-1),str_replace(["{","}"],["<i class='text-danger'>","</i>"],$r),$rtml);						
					}
																				
					$html =  Html::tag("b",$data->record->model,["class"=>"text-primary"])."&nbsp;&nbsp;".Html::tag("span",$data->record->record_id,["class"=>"label label-primary"]);
					$html .= "<p><small>".Yii::t("app","Route")."</small>: <i class='text-success'>".$data->route->route."</i><br>".
							"<small>".Yii::t("app","Attributes")."</small>: ".
							$rtml.
							"</p>";
					return $html;
				}
            ],/*            
            [				
				'attribute'=>'type',				
				'value'=>function($data){										
					return $data->itemAlias('type',$data->type);
				},
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('type'),
					'options' => ['placeholder' => Yii::t('app','Filter by type...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
            ],*/
            [				
				'attribute'=>'status',
				'format'=>'raw',				
				'value'=>function($data){										
					if ($data->status)
					{
						return Html::tag("span",$data->itemAlias('status',($data->status?1:0)),["class"=>"btn btn-xs btn-".($data->status?'success':'danger')." btn-block"]);										
					}
					else
					{
						return Html::a($data->itemAlias('status',($data->status?1:0)),["//versioning/version/apply","id"=>$data->id],["class"=>"btn btn-xs btn-".($data->status?'success':'danger')." btn-block","title"=>Yii::t("app","Click to apply this version!"),"data"=>["confirm"=>Yii::t("app","Are you sure to apply this version?"),"method"=>"post"]]);										
					}
				},
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('status'),
					'options' => ['placeholder' => Yii::t('app','Filter by status...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
            ],  
            /*[
				'attribute'=>'record_attributes',
				'value'=> function($data) {
					return str_replace("\\n","",$data->record_attributes);
				}
            ],*/
            
            // 'isdel',

            [
				'class' => 'kartik\grid\ActionColumn',
				'template'=>'{delete}'
            ],
        ],
    ]); ?>

</div>
