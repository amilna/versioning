<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use amilna\yap\GridView;

/* @var $this yii\web\View */
/* @var $searchModel amilna\versioning\models\GroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a(Yii::t('app', 'Create {modelClass}', [
    'modelClass' => 'Group',
]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        
        'containerOptions' => ['style'=>'overflow: auto'], // only set when $responsive = false		
		'caption'=>Yii::t('app', 'Group'),
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
            'title',
            'description:ntext',
             [	
				'class' => 'kartik\grid\EditableColumn',
				'attribute'=>'ownerUsername',
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
				'attribute'=>'status',
				'filterType'=>GridView::FILTER_SELECT2,				
				'filterWidgetOptions'=>[
					'data'=>$searchModel->itemAlias('status'),
					'options' => ['placeholder' => Yii::t('app','Filter by status...')],
					'pluginOptions' => [
						'allowClear' => true
					],
					
				],
				'value'=>function($data){										
					return $data->itemAlias('status',$data->status);
				},
				'editableOptions'=> function ($model, $key, $index) {
					return [
						'header'=>Yii::t('app','Status'), 
						'size'=>'sm',
						'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
						'options' => [
							'data'=>$model->itemAlias('status'),
							'options' => ['placeholder' => Yii::t('app','Set status...')],
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
            // 'time',
            // 'isdel',

            ['class' => 'kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>
