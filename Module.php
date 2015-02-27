<?php

namespace amilna\versioning;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'amilna\versioning\controllers';
    public $userClass = 'common\models\User';//'dektrium\user\models\User';
    public $defaults = ["create"=>"create","view"=>"view"]; // default action for create and view in the controllers
    public $nomodels = []; // list of models that versioning don't applied on
    public $noroutes = []; // list of routes that versioning don't applied on
    public $onmodels = []; // ex: ['amilna\blog\models','amilna\yes\models\Products'] List of models that versioning only apllied on, if not set then versioning will run on all models except listed in nomodels
    public $onroutes = []; // ex: ['yes/product','blog/post/update'] list of routes that versioning only apllied on, if not set then versioning will run on all routes except listed in noroutes

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
