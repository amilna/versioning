<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model amilna\versioning\models\RecordSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="record-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'model') ?>

    <?= $form->field($model, 'record_id') ?>

    <?= $form->field($model, 'owner_id') ?>

    <?= $form->field($model, 'group_id') ?>

    <?php // echo $form->field($model, 'viewers') ?>

    <?php // echo $form->field($model, 'isdel') ?>

    <?php // echo $form->field($model, 'filter_viewers')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
