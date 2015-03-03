Data Versioning and CRUD Watching
============================
Extensions for Yii2 for data versioning and CRUD watching. You can manage data version history that edited by user and manage user/group access at record level.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist amilna/yii2-versioning "dev-master"
```

or add

```
"amilna/yii2-versioning": "dev-master"
```

to the require section of your `composer.json` file.


run migration for database

```
./yii migrate --migrationPath=@amilna/versioning/migrations
```

add in bootstrap section of main config

```
	'bootstrap' => [
		...
		'amilna\versioning\components\Versioning',
		...
    	],   

```


add in modules section of main config

```
	'gridview' =>  [
		'class' => 'kartik\grid\Module',
	],
	'versioning' => [
		'class' => 'amilna\versioning\Module',
		'userClass' =>  'dektrium\user\models\User',//'common\models\User',            
		'defaults' => ["create"=>"create","view"=>"view"],
		//'onroutes' => ['yes/product','blog/post/update'], /* example to apply versioning on certain routes only */
		//'onmodels' => ['amilna\blog\models\BlogCatPos'], /* example to apply versioning on certain models only */
	]
```



Usage
-----

Once the extension is installed, check the url:
[your application base url]/index.php/versioning

Try it with edit some active record model an see the version that automatically created.

To use notification widget (it will inform you what has changed), just put in your view

```
	<?php
		echo amilna\versioning\widgets\Notification::widget();              
	?>
```