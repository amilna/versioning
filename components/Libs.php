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
	
	public function arrItemIn($string,$array)
	{		
		foreach ($array as $a)
		{
			if (strpos($string,$a) === false)
			{				
				echo $string." gagal<br>";
				return false;	
			}			
		}
		echo $string."<br>";
		return true;		
	}
    
    public function mkVersion($app,$eventName,$model,$routeString=false)
    {
		$nomodels = ['amilna\versioning\models\Record','amilna\versioning\models\Version','amilna\versioning\models\Route'];		
		$noroutes = ['versioning/version/apply'];		
		$module = $app->getModule("versioning");
		$modname = get_class($model);										
						
		/*
		if (!self::arrItemIn($modname,$nomodels) && !self::arrItemIn($app->requestedRoute,$noroutes))								
		{
			die("masuk");	
		}
		else
		{
			die("tidak");	
		}
		*/
		
		//$res = (!self::arrItemIn($modname,$nomodels) && !self::arrItemIn($app->requestedRoute,$noroutes));
		
										
		$res = true;
		//if (!self::arrItemIn($modname,$nomodels) && !self::arrItemIn($app->requestedRoute,$noroutes))
		if (!in_array($modname,$nomodels) && !in_array($app->requestedRoute,$noroutes))		
		//if (self::arrItemIn($modname,$nomodels) == 2 && self::arrItemIn($app->requestedRoute,$noroutes) == 2)
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
					$r = (!$routeString?$app->requestedRoute:$routeString);
					
					$rid = $model->getPrimaryKey();																																	
					$record = Record::findOne(array_merge(["model"=>$modname],($rid == null?[]:["record_id"=>$rid])));
					
					if (!$record) {
						if ($version->itemAlias("type",$eventName,true) != 1) {
							$br = str_replace(basename($r),"",$r).$module->defaults["create"];
							//$res = self::mkVersion($app,$version->itemAlias("type",1),$model,$br);							
							$eventName = $version->itemAlias("type",1);
							$arr = $model->attributes;	
							$atr = json_encode($arr);
						}
												
						$record = Record::findOne(["model"=>$modname,"record_id"=>$rid]);
						if (!$record);
						{
							$record = new Record();
							$record->model = $modname;
							if ($rid != null)
							{
								$record->record_id = $rid;
							}
							$record->owner_id = $user_id;							
						}
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
					else
					{
						$origin = Version::findOne(["record_id"=>$record->id,"status"=>true]);	
					}															
					
					$version->isdel = 0;
					$version->record_id = $record->id;
					$version->route_id = $route->id;
					$version->type = $version->itemAlias("type",$eventName,true);
					
					if ($version->type == 1 && !$origin && !$version->isRoot())
					{						
						$version->makeRoot();	
						//print_r($version->attributes);
						//die();
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
					
					
					//print_r($version->getErrors());
					//die($version->isNewRecord."tes".get_class($version));
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
}	
