<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/citrus.tszh/include.php");

use Bitrix\Main\Type\DateTime;
use Citrus\Tszh\HouseTable;
use Vdgb\Tszhepasport\EpasportTable;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader,
	Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog;

Loader::includeModule('iblock');

$modulePermissions = $APPLICATION->GetGroupRight("citrus.tszh");
if ($modulePermissions <= "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$bFatalError = false;

ClearVars();
ClearVars("str_");

$strRedirect = BX_ROOT . "/admin/tszh_house_edit.php?lang=" . LANG . GetFilterParams("find_", false);
$strRedirectList = BX_ROOT . "/admin/tszh_house_list.php?lang=" . LANG . GetFilterParams("find_", false);

$errorMessage = "";
$bVarsFromForm = false;
$message = null;

$ID = IntVal($_REQUEST['ID']);
$houseEntityFields = HouseTable::getFieldNames();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("TSZH_HOUSE_EDIT"), "ICON" => "citrus.tszh", "TITLE" => GetMessage("TSZH_HOUSE_TITLE"))
);

// Если установлен модуль электронных паспортов, то добавим дополнительную вкладку
if (CModule::IncludeModule('vdgb.tszhepasport'))
{
	$aTabs[] = array(
		"DIV" => "edit2",
		"TAB" => GetMessage("TSZH_EPASPORT_EDIT"),
		"ICON" => "vdgb.tszhepasport",
		"TITLE" => GetMessage("TSZH_EPASPORT_TITLE")
	);
	$epasportEntityFields = EpasportTable::getFieldNames();
	$entityFields = array_merge($houseEntityFields, $epasportEntityFields);
	$EP_ID = IntVal($_REQUEST['EP_ID']);
	$aTabs[] = array(
		"DIV" => "edit3",
		"TAB" => GetMessage("HOUSE_DOCUMENTS"),
		"ICON" => "vdgb.tszhepasport",
		"TITLE" => GetMessage("TSZH_HOUSE_DOCUMENTS_TITLE")
	);
}
else
{
    $entityFields = $houseEntityFields;
}

$UF_ENTITY = "TSZH_HOUSE";

$tabControl = new CAdminForm(basename(__FILE__, '.php'), $aTabs);

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['Update']) && check_bitrix_sessid())
{
	if ($modulePermissions < "W")
	{
		$errorMessage .= GetMessage("ACCESS_DENIED") . ".<br />";
	}

	$ID = IntVal($ID);
	if ($ID < 0)
	{
		$errorMessage .= GetMessage("TSZH_GROUP_SAVE_ERROR") . ' ' . GetMessage("TSZH_GROUP_SAVE_ERROR_ITEM_NOT_FOUND") . "<br />";
	}

	if (strlen($errorMessage) <= 0)
	{
		$fieldValues = $ufValues = array();
		array_map(function ($fieldName) use (&$fieldValues)
		{
			if (isset($_POST[$fieldName]))
			{
				$fieldValues[$fieldName] = $_POST[$fieldName];
			}
		}, $entityFields);

		$houseFieldValues = array(); /* выбираем из общего списка полей только те, которые относятся к основной таблице с домами */
		array_map(function ($fieldName) use (&$houseFieldValues, $fieldValues)
		{
			if (isset($fieldValues[$fieldName]))
			{
				$houseFieldValues[$fieldName] = $fieldValues[$fieldName];
			}
		}, $houseEntityFields);

		if (CModule::IncludeModule('vdgb.tszhepasport'))
		{
			/* выбираем из общего списка полей только те, которые относятся к таблице с электронными паспортами */
			$epasportFieldValues = array();
			array_map(function ($fieldName) use (&$epasportFieldValues, $fieldValues)
			{
				if (isset($fieldValues[$fieldName]))
				{
					$epasportFieldValues[$fieldName] = $fieldValues[$fieldName];
				}
			}, $epasportEntityFields);
		}

		$USER_FIELD_MANAGER->EditFormAddFields($UF_ENTITY, $ufValues);
		
		$houseFieldValues["DATE_OF_COMMISSIONING"] = DateTime::createFromText($houseFieldValues["DATE_OF_COMMISSIONING"]);
		$houseFieldValues["DATE_OF_MAINTENANCE"] = DateTime::createFromText($houseFieldValues["DATE_OF_MAINTENANCE"]);

		if ($ID > 0)
		{
			$result = HouseTable::update($ID, $houseFieldValues);
		}
		else
		{
			$result = HouseTable::add($houseFieldValues);
			if ($result->isSuccess())
			{
				$ID = $result->getId();
			}
		}

		if (!$result->isSuccess())
		{
			$errorMessage .= GetMessage("TSZH_GROUP_SAVE_ERROR") . '<br>' . implode('<br>', $result->getErrorMessages());
		}
		else
		{
			$USER_FIELD_MANAGER->Update($UF_ENTITY, $ID, $ufValues);
		}
	}

	// добавление/изменение информации в модуле электронных паспортов
	if (CModule::IncludeModule('vdgb.tszhepasport'))
	{
		if ($_FILES["EP_HOUSE_IMAGE_ID"]["error"] == 0)
		{
			$res = EpasportTable::Getlist(array("select" => array("EP_HOUSE_IMG_ID",), "filter" => array("EP_ID" => $_POST['EP_ID'])))->fetch();
			if (is_array($res))
			{
				CFile::Delete($res["EP_HOUSE_IMG_ID"]);
			}
			$epasportFieldValues["EP_HOUSE_IMG_ID"] = CFile::SaveFile(array_merge($_FILES["EP_HOUSE_IMAGE_ID"], array("MODULE_ID" => "vdgb.tszhepasport")), "/vdgb.tszhepasport");
		}
		if ($EP_ID > 0)
		{
			$epasportFieldValues["DATE_UPDATE"] = DateTime::createFromTimestamp(time());
			$ep_result = EpasportTable::update($_POST['EP_ID'], $epasportFieldValues);
		}
		else
		{
			$epasportFieldValues["EP_HOUSE_ID"] = $result->getId();
			$epasportFieldValues["DATE_CREATE"] = $epasportFieldValues["DATE_UPDATE"] = DateTime::createFromTimestamp(time());
			$ep_result = EpasportTable::add($epasportFieldValues);
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (isset($_REQUEST["apply"]) && strlen($_REQUEST["apply"]))
		{
			LocalRedirect($strRedirect . "&ID=" . $ID . "&" . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect($strRedirectList);
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

$fieldValues = array();
if ($ID > 0)
{
	if (CModule::IncludeModule('vdgb.tszhepasport'))
	{
		$arSelect = array("*", '' => 'Vdgb\Tszhepasport\EpasportTable:HOUSE.*');
	}
	else
	{
		$arSelect = array("*");
	}
	$db = HouseTable::getList(
		array(
			"filter" => array("ID" => $ID),
			"limit" => 1,
			"select" => $arSelect,
		)
	);

	if (!$fieldValues = $db->fetch())
	{
		$errorMessage .= GetMessage("TSZH_EDIT_ITEM_NOT_FOUND") . ".<br />";
		$bFatalError = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/citrus.tszh/prolog.php");

$APPLICATION->SetTitle(($ID > 0) ? str_replace(array('#STREET#','#HOUSE#'),array($fieldValues["STREET"], $fieldValues["HOUSE"]), GetMessage("TSZH_EDIT_PAGE_TITLE")) : GetMessage("TSZH_ADD_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if ($bVarsFromForm)
{
	array_map(function ($fieldName) use ($fieldValues)
	{
		if (isset($_POST[$fieldName]))
		{
			$fieldValues[$fieldName] = $_POST[$fieldName];
		}
	}, $entityFields);
}


if (strlen($errorMessage) > 0)
{
	CAdminMessage::ShowMessage(Array(
		"DETAILS" => $errorMessage,
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("TSZH_EDIT_ERRORS") . ':',
		"HTML" => true
	));
}

if ($bFatalError)
{
	require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");

	return;
}

/**
 *   CAdminForm()
 **/

$aMenu = array(
	array(
		"TEXT" => GetMessage("TSZH_ITEMS_LIST"),
		"LINK" => $strRedirectList,
		"ICON" => "btn_list",
		"TITLE" => GetMessage("TSZH_ITEMS_LIST_TITLE"),
	)
);

if ($ID > 0 && $modulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("TSZH_GROUP_DELETE_TITLE"),
		"LINK" => "javascript:if(confirm('" . GetMessage("TSZH_GROUP_DELETE_CONFIRM") . "')) window.location='{$strRedirectList}&ID=" . $ID . "&edit_form_del=1&" . bitrix_sessid_get() . "#tb';",
		"WARNING" => "Y",
		"ICON" => "btn_delete",
	);
}
if (!empty($aMenu))
{
	$aMenu[] = array("SEPARATOR" => "Y");
}

$link = DeleteParam(array("mode"));
$link = $APPLICATION->GetCurPage() . "?mode=settings" . ($link <> "" ? "&" . $link : "");
$aMenu[] = array(
	"TEXT" => GetMessage("TSZH_EDIT_SETTING"),
	"TITLE" => GetMessage("TSZH_EDIT_SETTING_TITLE"),
	"LINK" => "javascript:" . $tabControl->GetName() . ".ShowSettings('" . htmlspecialcharsbx(CUtil::addslashes($link)) . "')",
	"ICON" => "btn_settings",
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin(array(
	"FORM_ACTION" => $APPLICATION->GetCurPage(),
	"FORM_ATTRIBUTES" => "method=\"POST\" enctype=\"multipart/form-data\"",
));
$tabControl->BeginNextFormTab();

// проверка прав для портала
$arTszhFilter = $arTszhRight = array();
if (CTszhFunctionalityController::isPortal() && !$USER->IsAdmin())
{
	global $USER;
	$arGroups = $USER->GetUserGroupArray();

	$rsPerms = \Vdgb\PortalTszh\PermsTable::getList(
		array(
			"filter" => array("@GROUP_ID" => $arGroups, "ENTITY_TYPE" => "tszh")
		)
	);

	while ($arPerms = $rsPerms->fetch())
	{
		if ($arPerms["PERMS"] >= "W")
		{
			$arTszhRight[$arPerms["ENTITY_ID"]] = $arPerms["PERMS"];
		}
	}

	$arTszhFilter["@ID"] = array_keys($arTszhRight);
}

$fieldsMap = HouseTable::getEditableFields();
if (CModule::IncludeModule('vdgb.tszhepasport'))
{
	$fieldsMap = array_merge($fieldsMap, EpasportTable::getEditableFields()); /*объединяем массива с полями таблиц*/
	array_pop($fieldsMap); /*удаляем из списка полей сущности поле, описывающее reference-связь (в нашем случае последнее в массиве)*/
}
foreach ($fieldsMap as $fieldName => $fieldSettings)
{
	$fieldTitle = $fieldSettings['title'] . ':';
	switch ($fieldName)
	{
		case 'ID':
			$tabControl->AddViewField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			break;
		case 'SITE_ID':
			$rsSites = CSite::GetList($by = 'sort', $order = 'desc', Array());
			$arSites = Array('' => '');
			while ($arSite = $rsSites->Fetch())
			{
				$arSites[$arSite['ID']] = '[' . $arSite['ID'] . '] ' . $arSite['NAME'];
			}
			$tabControl->AddDropDownField($fieldName, $fieldTitle, false, $arSites, $fieldValues[$fieldName]);
			break;
		case 'BANK':
			$tabControl->AddSection('HOUSE_BANK_TITLE', GetMessage("HOUSE_BANK_TITLE"));
			$tabControl->AddEditField($fieldName, $fieldTitle, $fieldSettings['requied'], array(
				"size" => 40,
				"maxlength" => 255
			), $fieldValues[$fieldName]);
			break;
		case 'OVERHAUL_BANK':
			$tabControl->AddSection('HOUSE_OVERHAUL_BANK_TITLE', GetMessage("HOUSE_OVERHAUL_BANK_TITLE"));
			$tabControl->AddEditField($fieldName, $fieldTitle, $fieldSettings['requied'], array(
				"size" => 40,
				"maxlength" => 255
			), $fieldValues[$fieldName]);
			break;
		case 'TSZH_ID':
			$rsTszh = CTszh::GetList(array(), $arTszhFilter);
			$arTszhs = Array();
			while ($arTszh = $rsTszh->Fetch())
			{
				$arTszhs[$arTszh['ID']] = "[{$arTszh['ID']}] {$arTszh['NAME']}";
			}
			$tabControl->AddDropDownField($fieldName, $fieldTitle, true, $arTszhs, $fieldValues[$fieldName]);
			break;
		case 'DATE_OF_COMMISSIONING':
		case 'DATE_OF_MAINTENANCE':
			$tabControl->AddCalendarField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			break;
		// поля электронного паспорта
		case 'EP_ID':
			$tabControl->BeginNextFormTab();
			if (isset($fieldValues[$fieldName]))
			{
				$EP_ID = $fieldValues[$fieldName];
			}
			else
			{
				$EP_ID = 0;
			}
			//$tabControl->AddSection('HTML_CODE', GetMessage("HOUSE_EPASPORT"));
			$tabControl->AddSection('HOUSE_EPASPORT', GetMessage("HOUSE_EPASPORT"));
			$tabControl->AddViewField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			break;
		case 'EP_HOUSE_ID':
			$tabControl->AddViewField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			break;
		case 'EP_HTML':
			$tabControl->AddTextField($fieldName, $fieldTitle, $fieldValues[$fieldName], array('cols' => 70, 'rows' => 25), false);
			break;
		case 'DATE_CREATE':
			$tabControl->AddViewField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			break;
		case 'EP_HOUSE_IMG_ID':
			$tabControl->BeginCustomField("EP_HOUSE_IMG", GetMessage("TSZH_HOUSE_IMAGE"));
			?>
			<tr id="tr_EP_HOUSE_IMG">
				<td width="40%"><?=GetMessage("TSZH_HOUSE_IMAGE")?></td>
				<td align="left">
					<?
					echo CFileInput::Show("EP_HOUSE_IMAGE_ID", $fieldValues[$fieldName], array(
						"IMAGE" => "Y",
						"PATH" => "Y",
						"FILE_SIZE" => "Y",
						"DIMENSIONS" => "Y",
						"IMAGE_POPUP" => "Y",
						"MAX_SIZE" => array("H" => 200, "W" => 200),
					), array(
						'upload' => true,
						'medialib' => true,
						'file_dialog' => true,
						'cloud' => false,
						'del' => false,
						'description' => false,
					));
					?>
				</td>
			</tr>
			<?
			$tabControl->EndCustomField("EP_HOUSE_IMG", "");
			break;
		case 'DATE_UPDATE':
			$tabControl->AddViewField($fieldName, $fieldTitle, $fieldValues[$fieldName]);
			if (Loader::includeModule("vdgb.documents"))
			{
				$tabControl->BeginNextFormTab();
				$tabControl->AddSection('HOUSE_EPASPORT', GetMessage("HOUSE_DOCUMENTS_LIST"));
				$tabControl->BeginCustomField("HOUSE_LIST_ADD", GetMessage("TSZH_HOUSE_DOCUMENTS_ADD"));
				CJSCore::Init(array('ajax', "jquery"));
				?>
				<script>
                    function ShowTableHeader(TableNode, data) {
                        TheadNode = BX.create('thead', {'attrs': {class: "adm-list-table-header"}})
                        BX.append(TheadNode, TableNode);
                        TrNode = BX.create('tr', {'attrs': {"class": "adm-list-table-row"}});
                        BX.append(TrNode, TheadNode);
                        data.forEach(function (dataitem, i, data) {
                            TdNode = BX.create('td', {'attrs': {"class": "adm-list-table-cell "}});
                            BX.append(TdNode, TrNode);
                            DivInnerNode = BX.create('div', {'attrs': {"class": 'adm-list-table-cell-inner'}, text: dataitem.content})
                            BX.append(DivInnerNode, TdNode);
                        })
                        TdNode = BX.create('td', {'attrs': {"class": "adm-list-table-cell "}});
                        BX.append(TdNode, TrNode);
                        DivInnerNode = BX.create('div', {
                            'attrs': {"class": 'adm-list-table-cell-inner'},
                            text: "<?=GetMessage("TSZH_HOUSE_ACTIONS")?>"
                        });
                        BX.append(DivInnerNode, TdNode);
                        TbodyNode = BX.create('tbody');
                        BX.append(TbodyNode, TableNode);
                    }
                    function show_headers(id, parentNode) {
                        BX.showWait(parentNode);
                        //BX(parentNode).innerHTML = '<p><b>Получаем список документов для текущего дома</b></p>';
                        BX.ajax({
                            url: '/bitrix/modules/vdgb.documents/admin/ajax.handler.php',
                            data: {'table_headers': 'value2'},
                            method: 'POST',
                            dataType: 'json',
                            timeout: 30,
                            async: true,
                            processData: true,
                            scriptsRunFirst: true,
                            emulateOnload: true,
                            start: true,
                            cache: false,
                            onsuccess: function (data) {

                                if (data.length > 0) {
                                    ObjNode = BX(parentNode);
                                    BX.cleanNode(ObjNode);
                                    TableNode = BX.create('table', {'attrs': {'class': "adm-list-table"}});
                                    BX.append(TableNode, ObjNode);
                                    ShowTableHeader(TableNode, data);
                                    show_documents_by_id(id, parentNode);
                                }
                                else {
                                    ObjNode = BX(parentNode);
                                    BX.cleanNode(ObjNode);
                                    ObjNode.append("Список документов пуст");
                                }
                            },
                            onfailure: function () {
                                console.log("error");
                            }
                        });
                    }
                    function AddRow(TbodyNode, dataitem) {
                        var TrNode = BX.create('tr', {'attrs': {"class": "adm-list-table-row", "id": "document_" + dataitem.ID}});
                        BX.append(TrNode, TbodyNode);
                        for (var key in dataitem) {
                            if (key == "DOCUMENT_ID") continue;
                            if (key == "ENTITY_ID") continue;
                            var TdNode = BX.create('td', {'attrs': {"class": "adm-list-table-cell"}});
                            BX.append(TdNode, TrNode);
                            switch (key) {
                                case 'ID' : {
                                    var aNode = BX.create('a', {
                                        'attrs': {'href': "document_edit.php?ID=" + dataitem.ID + "&lang=<?echo LANGUAGE_ID?>" + "&backUrl=<?=$APPLICATION->GetCurPageParam()?>"},
                                        text: dataitem[key]
                                    });
                                    BX.append(aNode, TdNode);
                                    break;
                                }
                                default: {
                                    var divNode = BX.create('div', {text: dataitem[key]});
                                    BX.append(divNode, TdNode);
                                    break;
                                }
                            }
                        }
                        ;
                        var TdNode = BX.create('td', {'attrs': {"class": "adm-list-table-cell"}});
                        BX.append(TdNode, TrNode);
                        var aNode = BX.create('a', {
                            'attrs': {"href": dataitem.DOCUMENT_ID, "download": "1"},
                            'text': "<?=GetMessage("TSZH_HOUSE_DOWNLOAD_FILE")?>"
                        });
                        BX.append(aNode, TdNode);
                        var aNode = BX.create('a', {
                            'attrs': {
                                "style": "cursor:pointer",
                                'onclick': "delete_element('list_of_documents','" + dataitem.ID + "')"
                            }, "text": "<?=GetMessage("TSZH_HOUSE_DELETE_FILE")?>"
                        });
                        BX.append(aNode, TdNode);
                    }
                    function ShowTableBody(TableNode, data) {
                        var TbodyNode = BX.findChild(BX(TableNode), {"tag": "tbody"}, true);
                        data.forEach(function (dataitem, i, data) {
                            AddRow(TbodyNode, dataitem);
                        });
                        BX.closeWait(TableNode);
                    }
                    function DeleteRow(id) {
                        ObjNode = BX("document_" + id);
                        BX.cleanNode(ObjNode, true);
                    }
                    function show_documents_by_id(id, parentNode) {
                        BX.ajax({
                            url: '/bitrix/modules/vdgb.documents/admin/ajax.handler.php',
                            data: {'entity_id': id, 'table_data': id + 'value2'},
                            method: 'POST',
                            dataType: 'json',
                            timeout: 30,
                            async: true,
                            processData: true,
                            scriptsRunFirst: true,
                            emulateOnload: true,
                            start: true,
                            cache: false,
                            onsuccess: function (data) {

                                if (data.length > 0) {
                                    ShowTableBody(TableNode, data);
                                }
                                else {
                                    BX.closeWait(parentNode);
                                }
                            },
                            onfailure: function () {
                                console.log("error");
                                BX.closeWait(parentNode);
                            }
                        });
                    }
                    function delete_element(parentNode, id) {
                        BX.showWait(parentNode, '<?=GetMessage("TSZH_HOUSE_DELETING_FILE")?>');
                        BX.ajax({
                            url: '/bitrix/modules/vdgb.documents/admin/ajax.handler.php',
                            data: {'delete_id': id},
                            method: 'POST',
                            dataType: 'json',
                            timeout: 30,
                            async: true,
                            processData: true,
                            scriptsRunFirst: true,
                            emulateOnload: true,
                            start: true,
                            cache: false,
                            onsuccess: function (data) {
                                DeleteRow(data);
                                BX.closeWait(parentNode);
                            },
                            onfailure: function () {
                                console.log("error");
                            }
                        });
                    }
                    function add_element(parentNode, id) {
                        var fd = new FormData($("#tszh_house_edit_form")[0]);
                        fd.append('add_element', "true");
                        fd.append("entity_id", id);
                        BX.showWait(parentNode, '<?=GetMessage("TSZH_HOUSE_UPLOADING_FILE")?>');
                        $.ajax({
                            type: 'POST',
                            url: '/bitrix/modules/vdgb.documents/admin/ajax.handler.php',
                            data: fd,
                            dataType: 'json',
                            async: true,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                TbodyNode = BX.findChild(BX(parentNode), {"tag": "tbody"}, true);
                                AddRow(TbodyNode, data);
                                BX.closeWait(parentNode);
                            }
                        });
                    }
                    BX.ready(function () {
                        BX.bind(BX("IMAGE_ID"), "change", function () {
                            if (BX("IMAGE_ID").value != "") {
                                add_element('list_of_documents',<?echo urlencode($fieldValues["ID"])?>)
                            }
                        });
                        show_headers(<?echo urlencode($fieldValues["ID"])?> , "list_of_documents");
                    });
				</script>
				<tr>
					<td colspan="2" id="list_of_documents"></td>
				</tr>
				<tr>
					<td width="40%">
					<td align="left">
						<?
						echo CFile::InputFile("IMAGE_ID\" id=\"IMAGE_ID", 20, 0);
						?>
					</td>
				</tr>
				<?
				$tabControl->EndCustomField("HOUSE_LIST_ADD", "");
			}
			break;
		default:
			$tabControl->AddEditField($fieldName, $fieldTitle, $fieldSettings['requied'], array(
				"size" => 40,
				"maxlength" => 255
			), $fieldValues[$fieldName]);
			break;
	}
}

// ============ FORM PROLOG ==================
$tabControl->BeginEpilogContent();
echo GetFilterHiddens("find_");
?>
	<input type="hidden" name="Update" value="Y"/>
	<input type="hidden" name="lang" value="<?=LANG?>"/>
	<input type="hidden" name="ID" value="<?=$ID?>"/>
	<? if (CModule::IncludeModule('vdgb.tszhepasport')): ?>
		<input type="hidden" name="EP_ID" value="<?=$EP_ID?>"/>
	<? endif; ?>
<?
echo bitrix_sessid_post();
$tabControl->EndEpilogContent();
// ===========================================

// вывод пользовательских полей
if (
	(count($USER_FIELD_MANAGER->GetUserFields($UF_ENTITY)) > 0) ||
	($USER_FIELD_MANAGER->GetRights($UF_ENTITY) >= "W")
)
{
	$tabControl->AddSection('USER_FIELDS', GetMessage("TSZH_USER_FIELDS"));
	$tabControl->ShowUserFields($UF_ENTITY, $ID, $bVarsFromForm);
}

$tabControl->Buttons(Array(
	"disabled" => ($modulePermissions < "W"),
	"back_url" => $strRedirectList,
));

$tabControl->Show();

if (method_exists($USER_FIELD_MANAGER, 'showscript'))
{
	$tabControl->BeginPrologContent();
	echo $USER_FIELD_MANAGER->ShowScript();
	$tabControl->EndPrologContent();
}

$tabControl->ShowWarnings($tabControl->GetName(), $message);

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):?>
	<? echo BeginNote(); ?>
	<span class="required">*</span> <? echo GetMessage("REQUIRED_FIELDS") ?>
	<? echo EndNote(); ?>
<? endif; ?>
<? require_once($DOCUMENT_ROOT . BX_ROOT . "/modules/main/include/epilog_admin.php"); ?>