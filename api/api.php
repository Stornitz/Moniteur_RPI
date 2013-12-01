<?php
/* --- Fonctions contrôles --- */
function queryBox($method, $params)
{
	$url = "http://192.168.1.1/api/1.0/?method=".$method;
	if(!empty($params))
	{
		foreach($params as $key => $value)
		{
			$url .= "&".$key."=".$value;
		}
	}
	return simplexml_load_file($url);
}

function getToken()
{
	$fichier = fopen('token.txt', 'r+');
	$token = fgets($fichier);
	fclose($fichier);
	
	return $token;
}

function IsValid($value, $title)
{
	if(isset($value->err))
	{
		echo "<script>console.log('".$title." - [".$value->err->attributes()['code']."] ".$value->err->attributes()['msg']."');</script>";
		return false;
	}
	else
	{
		return true;
	}
}

/* --- Fonctions données --- */

function getConnexion($token) // Status, Uptime, Ip
{
	$result = queryBox("wan.getInfo", Array("token" => $token));
	if(isValid($result, "getConnexion"))
	{
		$wan = $result->wan->attributes();
		$connexion["status"] = (string) $wan["status"];
		if($wan["status"] == "up")
		{
			$connexion["uptime"] = (string) $wan["uptime"];
			$connexion["ip"] = (string) $wan["ip_addr"];
		}
		
		return $connextion;
	}
	
	return -1;
}

function getWifi($token) // Wifi 
{
	$result = queryBox("wlan.getInfo", Array("token" => $token));
	if(isValid($result, "getWifi"))
	{
		$wifi["status"] = ($result->wlan->attributes()["active"] == "on") ? true : false;
		return $wifi;
	}

	return -1;
}

function getHosts()
{
	// Hosts
	$result = queryBox("lan.getHostsList", Array());
	if(isValid($result, "getHosts"))
	{
		$i = 0;
		$hosts = $result->host;
		foreach($hosts AS $host)
		{
			$host = $host->attributes();
			$Hosts[$i]["name"] = (string) $host["name"];
			$Hosts[$i]["mac"] = (string) $host["mac"];
			$Hosts[$i]["alive"] = (string) $host["alive"];
			$Hosts[$i]["status"] = (string) $host["status"];
			$i++;
		}
		return $Hosts;
	}
	
	return -1;
}
	
function getDebit($token)
{
	// Débit Up, Down
	$result = queryBox("dsl.getInfo", Array("token" => $token));
	if(isValid($result, "getDebit"))
	{
		$dsl = $result->dsl->attributes();
		if($dsl["status"] == "up")
		{
			$debit["enabled"] = true;
			$debit["down"] = round(($dsl["rate_down"]*(100-$dsl["attenuation_down"])/100)/1000, 2);
			$debit["up"] = round(($dsl["rate_up"]*(100-$dsl["attenuation_up"])/100)/1000, 2);
		}
		else
		{
			$debit["enabled"] = false;
		}
		return $debit;
	}
	
	return -1;
}

function getUptime()
{
	$result = queryBox("system.getInfo", Array());
	if(isValid($result, "getUptime"))
	{
		return $result->system->attributes()["uptime"];
	}
	
	return -1;
}

function getMeteo($ville)
{
	// Météo
	$url = "http://api.openweathermap.org/data/2.5/weather?q=".$ville."&mode=xml&units=metric&lang=fr";
	$result = simplexml_load_file($url);

	$weather["ville"] = (string) $result->city->attributes()["name"];
	$weather["pays"] = (string) $result->city->country;
	$weather["soleil"]["leve"] = (string) $result->city->sun->attributes()["rise"];
	$weather["soleil"]["couche"] = (string) $result->city->sun->attributes()["set"];
	$weather["vent"]["degre"] = (string) $result->wind->direction->attributes()["value"];
	$weather["vent"]["vitesse"] = $result->wind->speed->attributes()["value"]." m/s";
	$weather["temps"] = (string) $result->weather->attributes()["value"];
	$weather["img"] = "http://openweathermap.org/img/w/".$result->weather->attributes()["icon"].".png";
	$weather["temperature"] = (string) $result->temperature->attributes()["value"];
	
	$weather["last_update"] = (string) $result->lastupdate->attributes()["value"];
	
	return $weather;
}
