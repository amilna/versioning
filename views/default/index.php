<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="blog-default-index">
    
    <div class="jumbotron">
		<h2>Yii2 Extensions for Data Versioning</h2>
        <h1>Congratulations!</h1>
        

        <p class="lead">You have successfully installed Data Versioning extension for your Yii-powered application.</p>

        <p><?= Html::a(Yii::t('app','Get start to manage group'),['//versioning/group'],["class"=>"btn btn-lg btn-success"])?></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-md-6">
                <h2>Record</h2>

                <p>Representation of single, implicitly structured data item in a table. In simple terms, a database table can be thought of as consisting of rows and columns or fields.[1] Each row in a table represents a set of related data, and every row in the table has the same structure.</p>

                <p><?= Html::a(Yii::t('app','Manage Records'),['//versioning/record'],["class"=>"btn btn-primary"])?></p>
            </div>
            <div class="col-md-6">
                <h2>Version</h2>

                <p>The management of changes to data, and other collections of information, since the begining of record creation.</p>

                <p><?= Html::a(Yii::t('app','Manage Versions'),['//versioning/version'],["class"=>"btn btn-primary"])?></p>
            </div>            
        </div>

    </div>
</div>
