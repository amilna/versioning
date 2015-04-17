<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use amilna\yap\GridView;

/* @var $this yii\web\View */
/* @var $searchModel amilna\versioning\models\RecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Records');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="record-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        
        'containerOptions' => ['style'=>'overflow: auto'], // only set when $responsive = false		
		'caption'=>Yii::t('app', 'Record'),
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
		'tableOptions'=>["style"=>"margin-bottom:50px;"],
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

            //'id',
            [                 			
				'attribute'=>'recordModel',
				'format'=>'raw',
				'value' => function($data) {					
					$version = $data->getVersion();
					
					$rtml = $version?Html::encode(str_replace([",","/"],[", ","/ "],json_encode($version->attributes))):"";					
																				
					$html =  Html::tag("span",$data->model,["class"=>"text-primary"])."&nbsp;&nbsp;".Html::tag("span",$data->record_id,["class"=>"label label-primary"]);
					$xtml = "<p><small>".Yii::t("app","Attributes")."</small>: ".
							$rtml.
							"</p>";
					return $html;
				}
            ],
            [	
				'class' => 'kartik\grid\EditableColumn',
				'attribute'=>'owner_id',
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('owner'),
					'options' => ['placeholder' => Yii::t('app','Filter by owner...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
				'value'=>function($data){										
					return $data->itemAlias('owner',$data->owner_id);
				},
				'editableOptions'=> function ($model, $key, $index) {
					return [
						'header'=>Yii::t('app','Owner Username'), 
						'size'=>'sm',
						'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
						'options' => [
							'data'=>$model->itemAlias('owner'),
							'options' => ['placeholder' => Yii::t('app','Select owner...')],
							'pluginOptions' => [
								'allowClear' => false
							],							
						],
						'placement'=>'left',	
						'showButtons'=>false,	
						'resetButton'=>false,	
						'pluginEvents'=>[
							'editableSuccess'=>"function(event, val, form, data) { 
													if (data.output != '".$model->itemAlias('owner',$model->owner_id)."')
													{
														location.reload();
													}													
												}",
						],					
					];
				},
				//'hAlign'=>'right',
            
            ],				
			[	
				'class' => 'kartik\grid\EditableColumn',
				'attribute'=>'groupTitle',
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('groups'),
					'options' => ['placeholder' => Yii::t('app','Filter by group...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
				'value'=>function($data){										
					return $data->itemAlias('groups',$data->group_id);
				},
				'editableOptions'=> function ($model, $key, $index) {
					return [
						'header'=>Yii::t('app','Group Title'), 
						'size'=>'sm',
						'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
						'options' => [
							'data'=>$model->itemAlias('groups'),
							'options' => ['placeholder' => Yii::t('app','Select group...')],
							'pluginOptions' => [
								'allowClear' => true
							],							
						],
						'placement'=>'left',	
						'showButtons'=>false,	
						'resetButton'=>false,						
					];
				},
				//'hAlign'=>'right',
            
            ],            
			[	
				'class' => 'kartik\grid\EditableColumn',
				'attribute'=>'filter_viewers',
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('filter_viewers'),
					'options' => ['placeholder' => Yii::t('app','Filter viewers...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
				'value'=>function($data){										
					return $data->itemAlias('filter_viewers',$data->filter_viewers?1:0);
				},
				'editableOptions'=> function ($model, $key, $index) {
					return [
						'header'=>Yii::t('app','Filter Viewers'), 
						'size'=>'sm',
						'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
						'options' => [
							'data'=>$model->itemAlias('filter_viewers'),
							'options' => ['placeholder' => Yii::t('app','Set filter viewers status...')],
							'pluginOptions' => [
								'allowClear' => false
							],							
						],
						'placement'=>'left',	
						'showButtons'=>false,	
						'resetButton'=>false,						
					];
				},
				//'hAlign'=>'right',
            
            ],            
            // 'viewers:ntext',
            // 'isdel',
            // 'filter_viewers:boolean',

            [
				'class' => 'kartik\grid\ActionColumn',
				'template'=>'{delete}'
            ],
        ],
    ]); ?>

</div>
