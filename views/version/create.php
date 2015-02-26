<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model amilna\versioning\models\Version */

$this->title = Yii::t('app', 'Create {modelClass}', [
    'modelClass' => 'Version',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Versions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="version-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
