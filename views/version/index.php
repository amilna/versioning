<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use amilna\yap\GridView;

/* @var $this yii\web\View */
/* @var $searchModel amilna\versioning\models\VersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Versions');
$this->params['breadcrumbs'][] = $this->title;

$dataProvider->pagination = [
	"pageSize"=>10	
]
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
		
		/* uncomment to use megeer some columns
        'mergeColumns' => ['Column 1','Column 2','Column 3'],
        'type'=>'firstrow', // or use 'simple'
        */
        
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
				'attribute'=>'recordModel',
				'format'=>'raw',
				'value' => function($data) {					
					$version = $data->getVersion();
					
					$rtml = $version?Html::encode(str_replace([",","/"],[", ","/ "],json_encode($version->attributes))):"";					
					foreach (json_decode($data->record_attributes) as $a=>$v)
					{
						$r = Html::encode(str_replace([",","/"],[", ","/ "],json_encode([$a=>$v])));						
						$rtml = str_replace(substr($r,1,-1),str_replace(["{","}"],["<i style='color:blue'>","</i>"],$r),$rtml);						
					}
															
					$html =  Html::a($data->record->model.":".$data->record->record_id,["//versioning/version/apply","id"=>$data->id],["title"=>Yii::t("app","Click to apply this version!"),"data"=>["confirm"=>Yii::t("app","Are you sure to apply this version?"),"method"=>"post"]]);
					$html .= "<p><b>".Yii::t("app","Attributes")."</b>: ".
							$rtml.
							"</p>";
					return $html;
				}
            ],
            /*[
				'attribute'=>'record_attributes',
				'value'=> function($data) {
					return str_replace("\\n","",$data->record_attributes);
				}
            ],*/
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
            ],
            [                 			
				'attribute'=>'routeUser',
				'value' => 'route.user.username'
            ],
            [				
				'attribute'=>'status',				
				'value'=>function($data){										
					return $data->itemAlias('status',($data->status?1:0));
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
            // 'isdel',

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>
