<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\jui\AutoComplete;
use kartik\money\MaskMoney;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model amilna\versioning\models\Group */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="group-form">

    <?php $form = ActiveForm::begin(); ?>

	<div class='row'>
		<div class='col-sm-6'>
			
			<?= $form->field($model, 'title')->textInput(['maxlength' => 65]) ?>
			
			<?= $form->field($model,'status')->widget(Select2::classname(),[						
				'data' => $model->itemAlias('status'),						
				'options' => [
					'placeholder' => Yii::t('app','Select status ...'), 
					'multiple' => false
				],
			])
			?>
			
			<?= $form->field($model,'owner_id')->widget(Select2::classname(),[						
				'data' => $model->itemAlias('owner'),						
				'options' => [
					'placeholder' => Yii::t('app','Select owner ...'), 
					'multiple' => false
				],
			])
			?>
		
		</div>
		<div class='col-sm-6'>
			<?= $form->field($model, 'description')->textarea(['rows' => 8]) ?>
		</div>
	</div>
	
	
	<div class='row-fluid'>
		<div class='col-xs-12'>
			<div class="form-group">
			<?php			
			
			$userClass = Yii::$app->getModule('versioning')->userClass;        
			echo amilna\yap\DualListBox::widget([
				'model' => $model,
				'attribute' => 'memberJson',	
				'title' => Yii::t("app","Member"),
				//'data' => $userClass::find()->where(['not in','id',$model->memberId]),
				'data' => $userClass::find(),
				'data_id'=> 'id',
				'data_value'=> 'username'
			  ]);
		  
			?>
			</div>
		</div>
	</div>
	
	<br>
	
 <?php /*	
	<div class='row'>
		<div class='col-sm-6'>
			<?= Select2::widget([						
				'name'=>'Group[availableUsers]',
				'data' => $model->availableUsers,				
				'options' => [
					'placeholder' => Yii::t('app','Select user ...'), 
					'multiple' => true
				],
			])
			?>
		</div>
		<div class='col-sm-6'>
			<?= Select2::widget([						
				'name'=>'Group[member]',
				'data' => $model->member,				
				'options' => [
					'placeholder' => Yii::t('app','Select member ...'), 
					'multiple' => true
				],
			])
			?>
		</div>
	</div>	
	*/ ?>		
	<div class='row'>
		<div class='col-sm-12'>
			<div class="form-group">
				<?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right']) ?>
			</div>
		</div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
