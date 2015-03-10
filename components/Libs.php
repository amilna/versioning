<?php

namespace amilna\versioning\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

use SebastianBergmann\Diff\Differ;

use amilna\versioning\models\Record;
use amilna\versioning\models\Route;
use amilna\versioning\models\Version;
use amilna\versioning\models\VersionSearch;
use amilna\versioning\models\GrpUsr;

class Libs extends Component
{
	public function strLine($str)
    {				
		$arr = str_split(str_replace("\n","\~",$str));
		$str = "";
		foreach ($arr as $a)
		{			
			$str .= "\n".$a;
		}
		return $str;		
	}
    
    public function difObj($obj1,$obj2)
    {		
		$differ = new Differ;
		$arr = [];
		foreach ($obj1 as $a=>$v)
		{						
			if ($v != $obj2[$a])
			{				
				if (is_numeric($v) && is_numeric($obj2[$a]))
				{
					$arr[$a] = $v;
				}
				else
				{
					/*
					if (substr_count( $v, "\n" ) > 1 && substr_count( $obj2[$a], "\n" ) > 1)
					{
						$arr[$a] = $differ->diff($v,$obj2[$a]);	
					}
					else
					{	
						$arr[$a] = $differ->diff(self::strLine($v),self::strLine($obj2[$a]));
					}
					*/ 					
					$arr[$a] = $differ->diff($v,$obj2[$a]);	
				}
			}
		}
		return $arr;		
	}
	
	public function verObj($obj1,$obj2)
    {			
		$arr = [];
		foreach ($obj1 as $a=>$v)
		{						
			if ($v != $obj2[$a])
			{				
				
				$arr[$a] = $obj2[$a];				
			}
		}
		return $arr;	
	}
	
	public function arrItemIn($string,$array,$defReturn)
	{					
		$return = $string;	
		foreach ($array as $a)
		{			
			if (strpos($string,$a) !== false)
			{										
				$return = $defReturn;
			}			
		}				
		return $return;		
	}
    
    public function mkVersion($app,$eventName,$model,$routeString=false)
    {
		$module = $app->getModule("versioning");
								
		$nomodels = array_merge(['amilna\versioning\models\Record','amilna\versioning\models\Version','amilna\versioning\models\Route'],$module->nomodels);		
		$noroutes = array_merge(['versioning/version/apply'],$module->noroutes);				
		
		$onmodels = $module->onmodels;
		$onroutes = $module->onroutes;
		
		$modname = self::arrItemIn(get_class($model),$nomodels,$nomodels[0]);
		$rotname = self::arrItemIn($app->requestedRoute,$noroutes,$noroutes[0]);		
		
		if (count($onmodels) > 0 || count($onroutes) > 0 )
		{
			$cekmod = true;
			if (count($onmodels) > 0)
			{
				$onmodname = self::arrItemIn(get_class($model),$onmodels,$onmodels[0]);
				$cekmod = in_array($onmodname,$onmodels);
			}	
			
			$cekrot = true;
			if (count($onroutes) > 0)
			{
				$onrotname = self::arrItemIn($app->requestedRoute,$onroutes,$onroutes[0]);
				$cekrot = in_array($onrotname,$onroutes);
			}	
			
			if (count($onmodels) > 0 && count($onroutes) > 0 )
			{
				$stat = (($cekmod || $cekrot) && (!in_array($modname,$nomodels) && !in_array($rotname,$noroutes)));
			}
			else
			{
				$stat = (($cekmod && $cekrot) && (!in_array($modname,$nomodels) && !in_array($rotname,$noroutes)));	
			}
			
			if ($stat)
			{
				$modname = get_class($model);	
				$rotname = $app->requestedRoute;
			}

		}
		else
		{			
			$stat = (!in_array($modname,$nomodels) && !in_array($rotname,$noroutes));
		}	
		
		$res = true;		
		if ($stat)
		{									
			$version = new Version();
			if ($version->itemAlias("type",$eventName,true) == 1)
			{								
				$arr = $model->attributes;	
				$atr = json_encode($arr);
			}
			elseif ($version->itemAlias("type",$eventName,true) == 0)
			{				
				$atr = null;
			}	
			else
			{
				$arr = self::verObj($model->oldAttributes,$model->attributes);
				$atr = json_encode($arr);
			}										
			
			if ($atr != "[]") {					
				
				$transaction = $app->db->beginTransaction();
				try {
					
					$user_id = ($app->user->isGuest?null:$app->user->id);					
					$time = date("Y-m-d H:i:s",$_SERVER["REQUEST_TIME"]);
					$r = (!$routeString?$rotname:$routeString);
					
					$rid = $model->getPrimaryKey();										
					$rid = (empty($rid) || !is_int($rid)? null:$rid);
																																	
					$record = Record::findOne(["model"=>$modname,"record_id"=>$rid]);
					
					if (!$record) {							
						$record = new Record();
						$record->model = $modname;
						if ($rid != null)
						{
							$record->record_id = $rid;
						}
						$record->owner_id = $user_id;												
					}
					$record->viewers = implode(",",[$user_id]);
					
					$res = (!$record->save()?false:$res);
					
					$route = Route::findOne(["route"=>$r,"time"=>$time,"user_id"=>$user_id]);
					if (!$route) {
						$route = new Route();
						$route->route = $r;
						$route->user_id = $user_id;
						$route->time = $time;
						$res = (!$route->save()?false:$res);
					}									
										
					$origin = Version::findOne(["record_id"=>$record->id,"status"=>true]);
					
					if ($version->itemAlias("type",$eventName,true) != 1) {
						$br = str_replace(basename($r),"",$r).$module->defaults["create"];
						
						if ($rid != null && !$origin)
						{								
							$version0 = new Version();
							$version0->record_attributes = json_encode($model->oldAttributes);	
							$version0->isdel = 0;
							$version0->record_id = $record->id;
							$version0->route_id = $route->id;
							$version0->type = 1;						
							$version0->status = false;
							$version0->makeRoot();
							$res = (!$version0->save()?false:$res);
							
							$version->prependTo($version0);
						}
						elseif ($rid == null)
						{
							$eventName = $version->itemAlias("type",1);
							$arr = $model->attributes;	
							$atr = json_encode($arr);
						}
					}
																								
					$version->record_attributes = $atr;					
										
					if ($rid == null)
					{
						$origin = Version::findOne(["route_id"=>$route->id,"record_id"=>$record->id,"record_attributes"=>$atr]);
						if (!$origin)
						{														
							$recs = Version::findAll(["record_id"=>$record->id]);
							foreach ($recs as $r)
							{
								$sql = "";
								$key = [];
								foreach (json_decode($r->record_attributes) as $a=>$v)
								{
									$sql .= ($sql == ""?"":" AND ").$a.($v === null?" is null":" = :".$a);
									if ($v !== null)
									{
										$key[":".$a] = $v;	
									}
								}									
								$rmod = $modname::find()->where($sql,$key)->one();
								$rts = $r->route_ids == null?[]:json_decode($r->route_ids);
								
								if (!$rmod)
								{
									if(($rts_key = array_search($route->id, $rts)) !== false) {
										unset($rts[$rts_key]);
									}
								}
								else
								{
									array_push($rts,$route->id);	
								}
								$r->route_id = $route->id;								
								$r->route_ids = json_encode(array_unique($rts));
								$r->type = ($rmod?1:0);
								$r->save();	
								if ($r->record_attributes."" == $atr)
								{
									$version = $r;	
								}
							}														
						}
						else
						{							
							$version = $origin;
							$origin = false;
						}
						
						$rts = $version->route_ids == null?[]:json_decode($version->route_ids);
						array_push($rts,$route->id);
						$version->route_ids = json_encode(array_unique($rts));																					
					}																		
					
					$version->isdel = 0;
					$version->record_id = $record->id;
					$version->route_id = $route->id;
					$version->type = $version->itemAlias("type",$eventName,true);
					
					if ($version->type == 1 && !$origin && !$version->isRoot())
					{						
						$version->makeRoot();							
					}
					else
					{
						if ($origin)
						{
							$version->prependTo($origin);
							$origin->status = false;
						}
					}																								
										
					if ($origin)				
					{
						$res = (!$origin->save()?false:$res);					
					}
					
					$res = (!$version->save()?false:$res);
					
					
					if ($res)
					{
						$transaction->commit();
						$res = [$route->id,$model->attributes];
					}
					else
					{
						$transaction->rollBack();		
					}
						
				} catch (Exception $e) {
					$transaction->rollBack();					
					$res = false;
				}												
			}				
			
		}
		return $res;
	}
	
	public function mkView($app,$eventName,$event)
	{
		$module = $app->getModule("versioning");
		$controller = $app->requestedAction->controller;
		$rotname = (isset($controller->module->module)?$controller->module->id."/":"").$controller->id;
		$user_id = $app->user->id;
		//$action_param = $controller->actionParams;												
		$action_param = $app->request->queryParams;
		
		if ($user_id > 0)
		{																	
			$params = [];
			foreach ($action_param as $p)
			{
				if (is_int($p))
				{
					array_push($params,$p);	
				}
			}
			
			if (count($params) > 0)
			{
				$searchModel = new VersionSearch();
				$dataProvider = $searchModel->search([]);
				$query = $dataProvider->query;
				$query->andWhere(["{{%versioning_version}}.status"=>true])
					->andWhere("{{%versioning_route}}.route like :route",[":route"=>$rotname."%"])
					->andWhere(["{{%versioning_record}}.record_id"=>$params]);							
				
				$groups = self::userGroups($user_id);
												
				foreach ($dataProvider->getModels() as $mod)
				{															
					foreach ($mod->route->versions as $m)
					{					
						if ($m->status && $m->record->record_id != null)
						{
							$users = $m->record->viewers == null?[]:explode(",",$m->record->viewers);
							$group_id = $m->record->group_id;
														
														
							$allow = false;
							if (in_array($group_id,$groups) || $m->record->owner_id == $user_id)
							{
								$allow = true;	
							}
							
							
							if (!$allow && !$m->record->filter_viewers)
							{
								$allow = ($app->requestedRoute == $rotname."/".$module->defaults["view"]);
							}
							
							if ($allow)
							{
								array_push($users,$user_id);
								$m->record->viewers = implode(",",array_unique($users));
								$m->record->save();																																
							}
							else
							{
								return $controller->redirect(["//".$rotname]);
							}																			
						}
					}
				}								
			}
			
		}
		
	}
	
	public function userGroups($user_id)
    {		
		$members = Yii::$app->db->createCommand("SELECT array_agg(group_id) as id FROM ".GrpUsr::tableName()."
				WHERE user_id = :id AND isdel = 0")->bindValues([":id"=>$user_id])->queryScalar();								
		
		return json_decode(str_replace(["{","}"],["[","]"],$members));
	}
		
}	
