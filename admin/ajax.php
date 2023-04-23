<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
use ITGaziev\PriceCalc;
use Bitrix\Main;

Main\Loader::includeModule('itgaziev.pricecalc');

PriceCalc\Main::process($_POST['options']);