<?php

namespace amilna\versioning;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'amilna\versioning\controllers';
    public $userClass = 'common\models\User';//'dektrium\user\models\User';
    public $defaults = ["create"=>"create","view"=>"view"];

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
