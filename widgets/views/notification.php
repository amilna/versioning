<?php

use yii\helpers\Html;
//$n = count($dataProvider->getModels());

$n = 0;
$litml = "";

$mods = (count($widget->models) > 0?implode(",",$widget->models):false);

$views = [];
foreach ($widget->models as $m)
{
	if (isset($module->views[$m])) {
		$views[$m] = $module->views[$m][0];	
	}
	
}																												
																							
foreach ($dataProvider->getModels() as $mod)
{																																	
	if ($mod->record->record_id != null)
	{
		$modname = $mod->record->model;
		
		$go = false;
		if (isset($widget->route[$modname]))
		{																
			$params = ["vrid"=>$mod->record->id];
			if (isset($widget->route[$modname][1]))
			{
				$version = $mod->version;									
				foreach ($widget->route[$modname][1] as $p)
				{
					if (!empty($version->$p))
					{
						$params[$p] = $version->$p;
						$go = true;
					}
				}
				
				if (isset($version->isdel))
				{
					if ($version->isdel == 1)
					{
						$go = false;
					}	
				}
			}
			
			$url = array_merge([$widget->route[$modname][0]],$params);
		}
		else
		{							
			$paths = explode("/",$mod->route->route);
			
			$url = "#";							
			if (class_exists ($modname)) {
				$model = $modname::findOne($mod->record->record_id);
				if ($model) {										
					$pk = $model->getPrimaryKey(true);								
					foreach ($pk as $k=>$v) {}									
					$route = "//".$paths[0]."/".$paths[1]."/".(isset($views[$modname])?$views[$modname]:$module->defaults["view"]);									
					$url = [$route,$k=>$v];
					$go = true;
					
					if (isset($model->isdel))
					{
						if ($model->isdel == 1)
						{
							$go = false;
						}	
					}
				}
			}	
		}
		
		$notif = [
			0 => '<i class="fa fa-warning text-red"></i>',
			1=> '<i class="fa fa-check-circle text-green"></i>',
			2=> '<i class="fa fa-exclamation-circle text-yellow"></i>',
			3=> '<i class="fa fa-exclamation-circle text-yellow"></i>',
			4 => '<i class="fa fa-warning text-red"></i>',
		];
		
		if ($go)
		{
			$litml .=  "<li>".Html::a($notif[$mod->type]." ".$mod->itemAlias("notif",$mod->type),$url,["title"=>$mod->itemAlias("notif",$mod->type)])."</li>";							
			$n += 1;
		}
		else
		{								
			$users = explode(",",$mod->record->viewers);
			array_push($users,Yii::$app->user->id);
			$mod->record->viewers = implode(",",array_unique($users));																
			$mod->record->save();														
		}
		
	}
}												
				

?>

<!-- Notifications: style can be found in dropdown.less -->
<li class="dropdown notifications-menu">
	<?php if ($n > 0) { ?>
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		<i class="fa fa-bell-o"></i>
		<span class="label label-warning"><?= $n ?></span>
	</a>
	<?php } else { ?>
	<a href="#" class="dropdown-toggle">
		<i class="fa fa-bell-o"></i>		
	</a>
	<?php } ?>
	<ul class="dropdown-menu">
		<li class="header"><?= Yii::t("app","You have {n} notifications",["n"=>$n]) ?></li>
		<li>
			<!-- inner menu: contains the actual data -->			
			<ul class="menu">				
				<?= $litml ?>						 						
			</ul>				
		</li>
		<li class="footer"><?= Html::a(Yii::t("app","Mark all as read"),["//versioning/version/readall","models"=>$mods])?></li>		
	</ul>		
</li>	
