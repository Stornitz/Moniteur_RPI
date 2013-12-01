<?php
require("api.php");

// On recupère username et password
require("config.php");

// On récupère un token temporaire
$result = queryBox("auth.getToken", Array());

if(isValid($result, "getTempToken"))
{
	$token_temp = (string) $result->auth->attributes()["token"];
		
	// On hash l'username et le password ensemble
	$hash_username = hash("sha256", $username);
	$hash_username = hash_hmac("sha256", $hash_username, $token_temp);
	$hash_password = hash("sha256", $password);
	$hash_password = hash_hmac("sha256", $hash_password, $token_temp);
		
	// On récupère le token d'authentification
	$result = queryBox("auth.checkToken", Array("token" => $token_temp, "hash" => $hash_username.$hash_password));
	if(isValid($result, "getToken"))
	{
		$token = $result->auth->attributes()["token"];
			
		$fichier = fopen('token.txt', 'r+');
		fseek($fichier, 0);
		fputs($fichier, $token);
		fclose($fichier);
		echo "Token ecrit ! - ".$token;
	}
}
?>
