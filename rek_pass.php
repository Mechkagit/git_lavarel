<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
header('Content-type: text/html; charset=windows-1251');
?>
<? if($_SERVER["REQUEST_METHOD"] == "POST") {

   $_REQUEST["rs"] = $APPLICATION->ConvertCharset($_REQUEST["rs"], 'utf-8', LANG_CHARSET);
   $_REQUEST["phone"] = $APPLICATION->ConvertCharset($_REQUEST["phone"], 'utf-8', LANG_CHARSET);
   $_REQUEST["message"] = '������ ������ ��� ������� � ������ �������';
  } 
   ?>
<? if (!empty($_REQUEST['rs']) and !empty($_REQUEST['phone'])) {?>
<?
// **** ��������� ������ ������������ �� ������ �������� ����� ****

// ��������� ID ������������ ����������� � �������� �����
	$dbAccounts = CTszhAccount::GetList($arOrder = array(), $arFilter = array("XML_ID"=>$_REQUEST["rs"]), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array("*"));
		while ($arAccount = $dbAccounts->GetNext()) 
			{
	//print_r($arAccount);
	//print_r($arAccount["USER_ID"]);
				$arResultID = $arAccount["USER_ID"];
				$arResultNAME = $arAccount["NAME"]." �/� � ".$_REQUEST['rs'];
			}
?>




<?$el = new CIBlockElement;
$new_element = array(
    'IBLOCK_ID' => '338',
    'NAME' => $arResultNAME,
    'CODE' => $_REQUEST['phone'],
    'DETAIL_TEXT' => $_REQUEST['message'],
    'ACTIVE' => 'Y',
    'PROPERTY_VALUES' => array(
	'PASS_TEL_NUMBER' => $_REQUEST['phone'],  //����� �������� - ��������
	'PASS_USER' => $arResultID,				  //�������� � ������������ �� ��� ID
	'PASS_CHECKING_ACC' => $_REQUEST["rs"]    //����� �������� ����� - ��������
   )
);

if ($el->Add($new_element)) {
    echo '111';
} else {
    echo '������ ���������� �������� � ��������: '.$el->LAST_ERROR;
}

    ?>            
<?}?>





<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
