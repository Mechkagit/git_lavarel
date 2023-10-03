<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $not_show_personal_data;
if ($not_show_personal_data)
	$arResult["USER_NAME"] = "";