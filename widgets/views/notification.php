<?php

use yii\helpers\Html;
$n = count($dataProvider->getModels());

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
				<?php																									
					foreach ($dataProvider->getModels() as $mod)
					{																																	
						if ($mod->record->record_id != null)
						{
							$paths = explode("/",$mod->route->route);
							$modname = $mod->record->model;
							$url = "#";							
							if (class_exists ($modname)) {
								$model = $modname::findOne($mod->record->record_id);
								if ($model) {
									$pk = $model->getPrimaryKey(true);								
									foreach ($pk as $k=>$v) {}									
									$route = "//".$paths[0]."/".$paths[1]."/".$module->defaults["view"];
									$url = [$route,$k=>$v];
								}
							}	
							$notif = [
								0 => '<i class="fa fa-warning text-red"></i>',
								1=> '<i class="fa fa-check-circle text-green"></i>',
								2=> '<i class="fa fa-exclamation-circle text-yellow"></i>',
							];
							
							echo "<li>".Html::a($notif[$mod->type]." ".$mod->itemAlias("notif",$mod->type),$url,["title"=>$mod->itemAlias("notif",$mod->type)])."</li>";							
							
						}
					}												
				?>						 						
			</ul>				
		</li>
		<li class="footer"><?= Html::a(Yii::t("app","Mark all as read"),["//versioning/version/readall","models"=>(count($widget->models) > 0?implode(",",$widget->models):false)])?></li>		
	</ul>		
</li>	
