<?php
namespace amilna\versioning\components;

use yii\base\Event;
use yii\db\ActiveRecord;

use yii\base\BootstrapInterface;

use amilna\versioning\models\Route;
use amilna\versioning\components\Libs;

class Versioning implements BootstrapInterface
{       
    public function bootstrap($app)
    {

		$events = [ActiveRecord::EVENT_AFTER_INSERT, ActiveRecord::EVENT_BEFORE_UPDATE, ActiveRecord::EVENT_AFTER_DELETE];
				
		$res0 = false;
		$res = false;
		foreach ($events as $eventName) {
			Event::on(ActiveRecord::className(), $eventName, function ($event) use ($app,$eventName) {				
				$model = $event->sender;				
				$res0 = Libs::mkVersion($app,$eventName,$model);
			});
		}
		
		if ($res0)
		{		
			$eventName = ActiveRecord::EVENT_AFTER_UPDATE;						
			Event::on(ActiveRecord::className(), $eventName, function ($event) use ($app,$eventName,$res0) {				
				$model = $event->sender;
				foreach ($model->attributes as $a=>$v)
				{
					$m = $res0[1];
					$res = ($m[$a] != $v?false:$res);
				}						
			});											
			
			if (!$res)
			{
				$route = Route::findOne($res0[0]);
				if ($route)
				{
					$route->delete();
				}
			}					
		}
		
    }
}
?>
