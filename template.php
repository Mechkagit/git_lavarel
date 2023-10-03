<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(true);
$frame = $this->createFrame()->begin(getMessage("T_AUTH_PROMPT"));

?>
<ul>
<?if($arResult["FORM_TYPE"] == "login"):?>
	<li>
		<a href="javascript:void(0);" class="popup popup-auth-link"><?=GetMessage("AUTH_AUTH")?></a>
		<div class="header-top-popup block-auth">
	<!-- Запрос пароля-->
	<?$APPLICATION->IncludeFile(SITE_DIR . "include/send_sms_pass.php", Array(), Array("MODE"=>"html"));?> <!-- Область с формой отправки пароля по смс-->		
	<div style="border: 2px solid #007fff; border-radius: 4px;display: inline-block;padding: 5px;margin: 0px 2px 0px 10px;text-align: center;">
	<?echo GetMessage("T_AUTH_FIRST")?>
    <a href="#" class="hover-btn open-popup" data-popup="#popup-ps" style="color:#74bc40;"><?echo GetMessage("T_AUTH_FIRST_PASS")?></a>
    <!-- Запрос пароля конец-->
    </div>
			<form class="block-auth-form" name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
				<?
					if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
					{
						ShowMessage($arResult['ERROR_MESSAGE']);
						if ($arResult["ERROR_MESSAGE"]['TYPE'] != 'OK')
						{
							?>
							<script type="text/javascript">$().ready(function() { __toggleAuthPopup($('.popup-auth-link')); } ); </script>
							<?
						}
					}
				?>

				<?if($arResult["BACKURL"] <> ''):?>
					<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
				<?endif?>
				<?foreach ($arResult["POST"] as $key => $value):?>
					<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
				<?endforeach?>
					<input type="hidden" name="AUTH_FORM" value="Y" />
					<input type="hidden" name="TYPE" value="AUTH" />
				
				<p><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" placeholder="<?=GetMessage("AUTH_LOGIN")?>" /></p>
				<p><input type="password" name="USER_PASSWORD" maxlength="50" placeholder="<?=GetMessage("AUTH_PASSWORD")?>" /></p>
					<?if($arResult["SECURE_AUTH"]):?>
						<span class="bx-auth-secure" id="bx_auth_secure<?=$arResult["RND"]?>" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
							<div class="bx-auth-secure-icon"></div>
						</span>
						<noscript>
						<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
							<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
						</span>
						</noscript>
						<script type="text/javascript">
						document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
						</script>
					<?endif?>

<?if ($arResult["CAPTCHA_CODE"]):?>
		<p>
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /><br /><br />
			<input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>"/>
			<input type="text" name="captcha_word" maxlength="50" value=""  placeholder="<?echo GetMessage("AUTH_CAPTCHA_PROMT")?>" />
		</p>
<?endif?>


				<p class="buttons">
					<?if ($arResult["STORE_PASSWORD"] == "Y"):?>
						<label><input type="checkbox"  name="USER_REMEMBER" value="Y" /> <?echo GetMessage("AUTH_REMEMBER_SHORT")?></label>
					<?endif;?>
					<button type="submit"><?=GetMessage("AUTH_LOGIN_BUTTON")?></button>
				</p>

				<p><a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></p>

				<?/*if($arResult["AUTH_SERVICES"]):?>
						<div class="block-auth-social">
							<span class="block-auth-social-title"><?=GetMessage("socserv_as_user_form")?></span>
				<?
				$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "icons", 
					array(
						"AUTH_SERVICES"=>$arResult["AUTH_SERVICES"],
						"SUFFIX"=>"headerauth",
					), 
					$component, 
					array("HIDE_ICONS"=>"Y")
				);
				?>
						</div>
				<?endif*/?>

			</form>
		</div>
	</li>
	<?if($arResult["NEW_USER_REGISTRATION"] == "Y"):?>
		<li><a href="<?=$arResult["AUTH_REGISTER_URL"]?>"><?=GetMessage("AUTH_REGISTER")?></a></li>
	<?endif;?>
<?else:?>
	<li>
        <a href="<?=$arResult["PROFILE_URL"]?>" title="<?=GetMessage("AUTH_PROFILE")?>"><?=(strlen($arResult["USER_NAME"]) > 0 ? $arResult["USER_NAME"] : $arResult["USER_LOGIN"])?></a>
	</li>
	<li>
		<a href="<?=$APPLICATION->GetCurPageParam('logout=yes', Array('logout'))?>"><?=GetMessage("AUTH_LOGOUT_BUTTON")?></a>
	</li>
<?endif;?>
</ul>
<?/*$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "",
	array(
		"AUTH_SERVICES" => $arResult["AUTH_SERVICES"],
		"CURRENT_SERVICE" => $arResult["CURRENT_SERVICE"],
		"AUTH_URL" => $arResult["AUTH_URL"],
		"POST" => $arResult["POST"],
		"SHOW_TITLES" => $arResult["FOR_INTRANET"]?'N':'Y',
		"FOR_SPLIT" => $arResult["FOR_INTRANET"]?'Y':'N',
		"AUTH_LINE" => $arResult["FOR_INTRANET"]?'N':'Y',
		"POPUP" => "Y",
		"SUFFIX" => "headerauth",
	),
	$component,
	array("HIDE_ICONS"=>"Y")
);*/
?>
