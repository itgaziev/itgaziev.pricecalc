<?php
use ITGaziev\PriceCalc;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\CIBlock;
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('itgaziev.pricecalc');

Loc::loadMessages(__FILE__);

$arJsConfig = [
    'itgaziev.pricecalc' => [
        'js' => '/local/modules/itgaziev.pricecalc/install/themes/.default/itgaziev.pricecalc/script.js',
        'css' => '/local/modules/itgaziev.pricecalc/install/themes/.default/itgaziev.pricecalc/style.css',
        'rel' => []
    ]
];

foreach ($arJsConfig as $ext => $arExt) CJSCore::RegisterExt($ext, $arExt);

CJSCore::Init(array('jquery'));
if($arJsConfig) {
    CUtil::InitJSCore(array_keys($arJsConfig));
}

$POST_RIGHT = $APPLICATION->GetGroupRight('itgaziev.pricecalc');

if ($POST_RIGHT == 'D') $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$aTabs = [[
        'DIV' => 'edit',
        'TAB' => Loc::getMessage('ITGAZIEV_PRICECALC_TAB'),
        'ICON' => 'main_user_edit',
        'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_TAB_TITLE')
    ]
];

if ($ID > 0) {
    $result = PriceCalc\PriceCalcTable::getById($ID);
    $condition = $result->fetch();
    if ($condition['PARAMETERS']) 
        $condition['PARAMETERS'] = unserialize($condition['PARAMETERS']);
}

$tabControl = new CAdminTabControl('tabControl', $aTabs, false);

if ($REQUEST_METHOD == 'POST' && $POST_RIGHT == 'W' && check_bitrix_sessid()) {

    $arFields = [
        'ACTIVE' => $_POST['ACTIVE'],
        'NAME' => $_POST['NAME'],
        'PRICE_TYPE' => $_POST['PRICE_TYPE'],
        'BASE_PRICE_TYPE' => $_POST['BASE_PRICE_TYPE']
    ];

    if ($ID > 0) {
        $arFields['PARAMETERS'] = serialize($_POST['PARAMETERS']);

        $result = PriceCalc\PriceCalcTable::update($ID, $arFields);

        if ($result->isSuccess()) {
            $res = true;
        } else {
            $errors = $result->getErrorMessages();
            $res = false;
        }
    } else {
        $arFields['PARAMETERS'] = serialize($_POST['PARAMETERS']);
        $arFields['TIME_CREATE'] = new \Bitrix\Main\Type\DateTime();
        $result = PriceCalc\PriceCalcTable::add($arFields);
        if ($result->isSuccess()) {
            $ID = $result->getID();
            $res = true;
        } else {
            $errors = $result->getErrorMessages();

            $res = false;
        }
    }
}

if($ID > 0) {
    $APPLICATION->SetTitle(Loc::getMessage('ITGAZIEV_PRICECALC_TITLE_HEAD', ['#ID#' => $ID]));
} else {
    $APPLICATION->SetTitle(Loc::getMessage('ITGAZIEV_PRICECALC_CREATE_TITLE_HEAD'));
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = array(
    array(
        'TEXT' => Loc::getMessage('ITGAZIEV_PRIECALC_BACK'),
        'TITLE' => Loc::getMessage('ITGAZIEV_PRIECALC_BACK_TITLE'),
        'LINK' => 'itgaziev.pricecalc_list.php?lang='.LANG,
        'ICON' => 'btn_list'
    )
);


if($ID > 0) {
    $aMenu[] = array('SEPARATOR' => 'Y');

    $aMenu[] = array(
        'TEXT' => Loc::getMessage('ITGAZIEV_PRICECALC_ADD'),
        'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_ADD_TITLE'),
        'LINK' => 'itgaziev.pricecalc_detail.php?lang='.LANG,
        'ICON' => 'btn_new'
    );

    $aMenu[] = array(
        'TEXT' => Loc::getMessage('ITGAZIEV_PRICECALC_DELETE'),
        'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_DELETE_TITLE'),
        'LINK' => "javascript:if(confirm('" . Loc::getMessage("ITGAZIEV_PRICECALC_DELETE_CONF") . "')) window.location='itgaziev.pricecalc_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
        'ICON' => 'btn_new'
    );

    $aMenu[] = array('SEPARATOR' => 'Y');
}

$context = new CAdminContextMenu($aMenu);

$context->Show();

if($ID > 0) {
    if($_REQUEST['mess'] == 'ok') {
        CAdminMessage::ShowMessage(array(
            'MESSAGE' => Loc::getMessage('ITGAZIEV_PRICECALC_SAVED'),
            'TYPE' => 'OK'
        ));
    }
}

?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?lang=<?=LANG?>" enctype="multipart/form-data" name="post_form">
<?php
echo bitrix_sessid_post();
$tabControl->Begin();
$tabControl->BeginNextTab();

PriceCalc\Main::getListPrices();
?>
<tr>
    <td>Активный</td>
    <td>
        <input type="checkbox" name="ACTIVE" value="Y" <?=$condition['ACTIVE'] ? ($condition['ACTIVE'] == "Y" ? "checked" : "") : "checked"?>>
    </td>
</tr>
<? if($ID > 0): ?>
    <tr>
        <td width="40%">ID:</td>
        <td width="60%">
            <span><?= $ID ?></span>
            <input type="hidden" name="ID" value="<?= $ID ?>"/>
        </td>
    </tr>
<? endif; ?>
<tr>
    <td width="40%"><span class="required">*</span>Название</td>
    <td width="60%"><input type="text" name="NAME" value="<?= $condition['NAME'] ?>" size="44" maxlength="255" /></td>
</tr>
<tr>
    <td width="40%"><span class="required">*</span>Тип Цены</td>
    <td>
        <?= SelectBoxFromArray('PRICE_TYPE', PriceCalc\Main::getListPrices(), $condition['PRICE_TYPE'], '', 'style="min-width: 350px; margin-right: 5px;"', false, ''); ?>
    </td>
</tr>
<tr>
    <td width="40%"><span class="required">*</span>Базовый тип Цены</td>
    <td>
        <?= SelectBoxFromArray('BASE_PRICE_TYPE', PriceCalc\Main::getListPrices(), $condition['BASE_PRICE_TYPE'], '', 'style="min-width: 350px; margin-right: 5px;"', false, ''); ?>
    </td>
</tr>
<tr>
    <td width="40%"><span class="required">*</span>Тип наценки</td>
    <td>
        <?= SelectBoxFromArray('PARAMETERS[TYPE_RULE]', PriceCalc\Main::getTypeRule(), $condition['PARAMETERS']['TYPE_RULE'], '', 'style="min-width: 350px; margin-right: 5px;"', false, ''); ?>
    </td>
</tr>
<tr>
    <td width="40%"><span class="required">*</span>Число</td>
    <td width="60%"><input type="text" name="PARAMETERS[NUMBER]" value="<?= $condition['PARAMETERS']['NUMBER'] ?>" size="44" maxlength="255" /></td>
</tr>
<?
$tabControl->Buttons(
    array(
      "disabled"=>($POST_RIGHT<"W"),
      "back_url"=>"itgaziev.pricecalc_list.php?lang=".LANG,
      
    )
);

$tabControl->End();
?>
</form>
<?php

if ($ID > 0):
    ob_start();
    $params = [];
    ?>
    <script>
        window.onload = () => {

        }
    </script>
    <?
    $jsString = ob_get_clean();
    Asset::getInstance()->addString($jsString);
endif;
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';