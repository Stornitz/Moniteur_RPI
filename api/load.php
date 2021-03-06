<?php
/* --- Variables --- */

require("config.php");

/* --- Require --- */

require("api.php");

/* --- Fonctions --- */

function FormatDate($date)
{
	$date = explode("T", $date);
	$jour = explode("-", $date[0]);
	$jour = $jour[2]."-".$jour[1]."-".$jour[0];
	$heure = FormatHeure($date[1]);
	return $date = $jour." à ".$heure ;
}

function FormatHeure($heure)
{
	$heure = explode(":", substr($heure, -8, -3));
	return $heure = $heure[0]+1 .":".$heure[1];
}

function DureeDuJour($lever, $coucher)
{
	$Duree["lever"] = explode(":", $lever);
	$Duree["lever"] = $Duree["lever"][0]*60+$Duree["lever"][1];
	
	$Duree["coucher"] = explode(":", $coucher);
	$Duree["coucher"] = $Duree["coucher"][0]*60+$Duree["coucher"][1];
	
	$DureeDuJour = $Duree["coucher"]-$Duree["lever"];
	
	$heures = (int)($DureeDuJour/60);
	$minutes = $DureeDuJour%60;
		
	return $heures."h".$minutes;
}

function FormatUptime($uptime)
{
	$minutes = (int)($uptime/60);
	if($minutes >= 60)
	{
		$heures = (int)($minutes/60);
		$minutes = $minutes%60;
	}
	
	$minutes_text = ($minutes == 1) ? "une minute" : $minutes." minutes";
	if(isset($heures)) {$heures_text = ($heures == 1) ? "une heure" : $heures." heures";}
	
	$uptime_text = (isset($heures)) ? "Il y a ".$heures_text." et ".$minutes_text."." : "Il y a ".$minutes_text.".";
	$uptime_text = ($minutes == 0 OR $minutes <= 5) ? "Actuellement en ligne." : $uptime_text;
	
	return $uptime_text;
}

/* ---------------- */
if(isset($_GET["partie"]))
{
	switch($_GET["partie"])
	{
		case "header":
		{
			$token = getToken();
			$debit = getDebit($token);
			$debit = ($debit["enabled"]) ? $debit["down"]." / ".$debit["up"] : "Erreur";
?>
			<div id="jour"><?php echo date("j-m-y"); ?></div>
			<div id="heure">
				<span id="heures"><?php echo date("H"); ?></span>
				<span id="colon">:</span>
				<span id="minutes"><?php echo date("i"); ?></span>
			</div>
			<div id="debit">
				<span id="titre">Débit Internet</span>
				<span><?php echo $debit; ?></span>
			</div>
<?php
			break;
		}
		case "meteo":
		{
			$weather = getMeteo($ville.",".$pays);
			$lever = FormatHeure($weather["soleil"]["leve"]);
			$coucher = FormatHeure($weather["soleil"]["couche"]);
?>
			<h1>Météo</h1>
			<img src="<?php echo $weather["img"]; ?>" alt="temps"/>
			<div id="location">
				<span id="pays"><?php echo $weather["pays"]; ?></span>
				<span id="ville"><?php echo $weather["ville"]; ?></span>
			</div>
			<div id="temps"><?php echo $weather["temps"]; ?></div>
			<div id="temperature">
				<span>Température</span>
				<span id="degre"><?php echo $weather["temperature"]; ?>°C</span>
			</div>
			<div id="vent">
				<?php echo "Vent à ".$weather["vent"]["vitesse"]; ?>
				<span style="-webkit-transform: rotate(<?php echo $weather["vent"]["degre"]-90; ?>deg);" class="arrow">  
					<span class="mask1"></span>  
					<span class="mask2"></span>  
					<span class="mask3"></span>  
				</span>
			</div>
			<div id="soleil">
				<div id="lever_coucher">Soleil de <?php echo $lever; ?> à <?php echo $coucher; ?></div>
				<div id="duree">Durée du jour : <?php echo DureeDuJour($lever, $coucher); ?></div>
			</div>
			<div id="derniere_maj">
				Dernière mise à jour le <?php echo FormatDate($weather["last_update"]); ?>
			</div>
<?php
			break;
		}
		case "internet":
		{
			$token = getToken();
			$wifi = getWifi($token);
			$Hosts = getHosts();
			$uptime = getUptime();
?>
			<h1>Internet</h1>
			<div id="wifi">
				<span class="wifi <?php echo ($wifi["status"]) ? "on" : "off"; ?>"></span>
				<span>Wifi <?php echo ($wifi["status"]) ? "activé" : "désactivé"; ?></span>
			</div>
			<div id="hosts">
				<span>Appareils connectés</span>
<?php
			foreach($Hosts AS $host)
			{
				if(!empty($host["name"]))
				{
?>
				<div class="host">
					<span class="status <?php echo $host["status"]; ?>"></span>
					<span id="host_name"><?php echo $host["name"]; ?></span>
					<span> - </span>
					<span id="alive"><?php echo FormatUptime($uptime-$host["alive"]); ?></span>
				</div>
<?php
				}
			}
?>
			</div>
<?php
			break;
		}
	}
}
