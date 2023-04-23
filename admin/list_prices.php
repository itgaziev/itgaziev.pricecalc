<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;
use ITGaziev\PriceCalc;
use Bitrix\Main\Entity\Base;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

$module_id = 'itgaziev.pricecalc';

Loader::includeModule($module_id);

Loc::loadMessages(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT == 'D') $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$sTableID = Base::getInstance('\ITGaziev\PriceCalc\Table\PriceCalcTable')->getDBTableName();
$oSort = new CAdminSorting($sTableID, 'ID', 'desc');
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter() {
    global $FilterArr, $lAdmin;

    foreach ($FilterArr as $f) global $$f;

    return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = [
        'ID' => ($find != "" && $find_type == 'id' ? $find : $find_id),
        'NAME' => $find_name
    ];

    foreach ($arFilter as $key => $value) if (empty($value)) unset($arFilter[$key]);
}

if ($lAdmin->EditAction() && $POST_RIGHT == 'W') {
    foreach ($FIELDS as $ID => $arFields) {
        if (!$lAdmin->IsUpdate($ID)) continue;

        $ID = IntVal($ID);

        if ($ID > 0) {
            foreach ($arFields as $key => $value) $arData[$key] = $value;

            $result = PriceCalc\Table\PriceCalcTable::update($ID, $arData);

            if (!$result->isSuccess()) {
                $lAdmin->AddGroupError(Loc::getMessage('ITGAZIEV_PRICECALC_SAVE_ERROR'), $ID);
            }
        } else {
            $lAdmin->AddGroupError(Loc::getMessage('ITGAZIEV_PRICECALC_SAVE_ERROR'), $ID);
        }
    }
}

if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == 'W') {
    if ($_REQUEST['action_target'] == 'selected') {
        $rsData = PriceCalc\Table\PriceCalcTable::getList([
            'select' => ['ID', 'NAME', 'PRICE_TYPE', 'BASE_PRICE_TYPE'],
            'filter' => $arFilter,
            'order' => [$by => $order]
        ]);

        while ($arRes = $rsData->Fetch()) $arID[] = $arRes['ID'];
    }

    foreach ($arID as $ID) {
        if (strlen($ID) <= 0) continue;

        $ID = IntVal($ID);

        switch ($_REQUEST['action']) {
            case 'delete':
                $result = PriceCalc\Table\PriceCalcTable::delete($ID);
                if (!$result->isSuccess()) $lAdmin->AddGroupError(Loc::getMessage('ITGAZIEV_PRICECALC_DELETE_ERROR'), $ID);

                break;
        }
    }
}

$rsData = PriceCalc\Table\PriceCalcTable::getList([
    'select' => ['ID', 'NAME', 'PRICE_TYPE', 'BASE_PRICE_TYPE'],
    'filter' => $arFilter,
    'order' => [$by => $order]
]);

$rsData = new CAdminResult($rsData, $sTableID);

$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage('rub_nav')));

$lAdmin->AddHeaders(array(
    array(
        'id'        => 'ID',
        'content'   => 'ID',
        'sort'      => 'ID',
        'align'     => 'right',
        'default'   => true
    ),
    array(
        'id'        => 'ACTIVE',
        'content'   => Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_ACTIVE'),
        'sort'      => 'ACTIVE',
        'default'   => true,
    ),
    array(
        'id'        => 'NAME',
        'content'   => Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_NAME'),
        'sort'      => 'NAME',
        'default'   => true,
    ),
    array(
        'id'        => 'PRICE_TYPE',
        'content'   => Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_PRICE_TYPE'),
        'sort'      => 'PRICE_TYPE',
        'default'   => true
    ),
    array(
        'id'        => 'BASE_PRICE_TYPE',
        'content'   => Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_BASE_PRICE_TYPE'),
        'sort'      => '',
        'default'   => true
    ),
    array(
        'id'        => 'RUN_PROCESS',
        'content'   => Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_RUN_PROCESS'),
        'sort'      => '',
        'default'   => true
    )
));

while ($arRes = $rsData->NavNext(true, 'f_')) {
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $row->AddViewField('NAME', '<a href="itgaziev.pricecalc_detail.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_NAME . '</a>');
    $row->AddViewField('RUN_PROCESS', '<a class="adm-btn adm-btn-save" href="itgaziev.pricecalc_process.php?ID=' . $f_ID . '&lang=' . LANG . '">' . Loc::getMessage('ITGAZIEV_PRICECALC_TABLE_RUN_PROCESS') . '</a>');

    $arActions = [];

    $arActions[] = [
        'ICON' => 'edit',
        'DEFAULT' => true,
        'TEXT' => Loc::getMessage('ITGAZIEV_PRICECALC_EDIT_BTN'),
        'ACTION' => $lAdmin->ActionRedirect('itgaziev.pricecalc_detail.php>ID=' . $f_ID . '&lang=' . LANG)
    ];

    if ($POST_RIGHT >= 'W') {
        $arActions[] = [
            'ICON' => 'delete',
            'TEXT' => Loc::getMessage("ITGAZIEV_PRICECALC_DELETE_BTN"),
            'ACTION' => "if(confirm('Вы уверены что хотите удалить?')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
        ];
    }

    $arActions[] = ['SEPERATOR' => true];

    if (is_set($arActions[count($arActions) - 1], 'SEPARATOR')) unset($arActions[count($arActions) - 1]);

    $row->AddActions($arActions);
}

$lAdmin->AddFooter([
    ['title' => Loc::getMessage('ITGAZIEV_PRICECALC_LIST_SELECTED'), 'value' => $rsData->SelectedRowsCount],
    ['counter' => true, 'title' => Loc::getMessage('ITGAZIEV_PRICECALC_LIST_CHECKED'), 'value' => 0]
]);

$aContext = [[
    'TEXT' => Loc::getMessage('ITGAZIEV_PRICECALC_ADD_TEXT'),
    'LINK' => 'itgaziev.pricecalc_detail.php?lang='.LANG,
    'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_ADD_TITLE'),
    'ICON' => 'btn_new'
]];

$lAdmin->AddAminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('ITGAZIEV_PRICECALC_TITLE'));

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

$oFilter = new CAdminFilter(
    $sTableID . '_filter',
    array(
        'ID',
        Loc::getMessage("ITGAZIEV_PRICECALC_FILTER_FIND_NAME")
    )
);

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");