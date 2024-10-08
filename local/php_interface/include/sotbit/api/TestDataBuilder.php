<?php
namespace Sotbit\Custom\Api;

use CCatalogContractor;
use CCatalogProduct;
use CCatalogStoreProduct;
use CFile;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CPrice;
use CUser;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::includeModule('sale');
Loader::includeModule("highloadblock");

class TestDataBuilder
{
    protected $users_count;                        // users amount
    protected $stores_count;                       // stores amount
    protected $contractors_count;                  // contractors amount
    protected $hlblock_contractors_count;          // highload block contractors amount

    protected $block_name;                         // highload block name
    protected $properties_count;                   // infoblock properties amount
    protected $info_block_id;                       // infoblock id

    protected $product_count;                       // infoblock products amount

    protected $product_settings_array;              // array of product settings
    public function __construct(
        int $users_count,
        int $stores_count,
        int $contractors_count,
        int $hlblock_contractors_count,
        string $block_name,
        int $properties_count,
        int $info_block_id,
        int $product_count,
        array $product_settings_array
    )
    {
        $this->users_count = $users_count;
        $this->stores_count  = $stores_count;
        $this->contractors_count = $contractors_count;
        $this->hlblock_contractors_count  = $hlblock_contractors_count;
        $this->block_name = $block_name;
        $this->properties_count = $properties_count;
        $this->info_block_id = $info_block_id;
        $this->product_count = $product_count;
        $this->product_settings_array = $product_settings_array;
    }

    // сапуск агента в bitrix
    public static function startAgentTestDataBuilding()
    {
        $user_builder = new self();
        // cod...
        return "Sotbit\Custom\Api\TestDataBuilder::startAgentTestDataBuilding();";
    }

    // получение случайных чисел разного ранжирования
    public function randomNumber($range): int
    {
        for ($i = 0; $i < $range; $i++) {
            $number .= rand(0, 9);
        }
        return $number;
    }

    // Создание новых пользователей
    public function testUsersDataBuilder(): bool
    {
        $limit = $this->users_count;

        for ($i = 0; $i < $limit; $i++) {

            $random_password = $this->randomNumber(6);

            $arFields = array(
                "NAME" => "User " . $i,
                "EMAIL" => "mail+" . $i . "@mail.ru",
                "LOGIN" => "mail+" . $i . "@mail.ru",
                "LID" => "ru",
                "ACTIVE" => "Y",
                "GROUP_ID" => array(3, 4),
                "PASSWORD" => $random_password,
                "CONFIRM_PASSWORD" => $random_password,
            );

            $rsUser = CUser::GetByLogin($arFields["EMAIL"]);

            if ($rsUser->Fetch()) // если пользователь уже существует вновь не создавать
            {
                continue;
            }
            else
            {
                $USER = new CUser;
                //$USER->Add($arFields);

                $USER->Register(
                    $arFields["LOGIN"],
                    $arFields["NAME"],
                    "",
                    $arFields["PASSWORD"],
                    $arFields["CONFIRM_PASSWORD"],
                    $arFields["EMAIL"]
                );
                CUser::SetUserGroup($USER->GetID(), $arFields["GROUP_ID"]);

            }
        }
        return true;
    }

    // Создание новых складов
    public function testStoreDataBuilder(): bool
    {
        $limit = $this->stores_count;

        for ($i = 0; $i < $limit; $i++)
        {
            $arFields = Array(
                "TITLE" => "stock+" . $i ,
                "ACTIVE" => "Y",
                "ADDRESS" => "default st.".$i,
                "DESCRIPTION" => "Testing Store N".$i,
                "IMAGE_ID" => "",
                "GPS_N" => "",
                "GPS_S" => "",
                "PHONE" => "+375 (222) 22-22-22",
                "SCHEDULE" => "9:00 - 18:00",
                "XML_ID" => "xml-".$i,
            );

            $resStores = \CCatalogStore::GetList(
                [],
                [ 'TITLE' => $arFields['TITLE'] ],
                false,
                false,
                []
            );

            if($resStores->Fetch()) {
                continue;
            }
            else
            {
                $ID = \CCatalogStore::Add($arFields);
            }
        }
        return true;
    }

    // Создание новых поставщиков
    public function testContractorDataBuilder(): bool
    {
        $limit = $this->contractors_count;

        for ($i = 0; $i < $limit; $i++)
        {

            $users_email = "mail+" . $i . "@mail.ru";

            $rsUser = CUser::GetByLogin($users_email);
            $res = $rsUser->Fetch();
            $users_id = $res['ID'];

            $arFields = array(

                "PERSON_TYPE" => 2,
                "PERSON_NAME" =>"Test name",
                "COMPANY" => "Contractor N" . $i,
                "EMAIL" => "test@sotbit.ru",
                'PHONE' => "+375 (222) 22-22-22",
                'USER_ID' => $users_id,
                'XML_ID' => "XML+". $i,
                'SHIPPING_CENTER'=> "stock+" . $i ,
                "INN" => $this->randomNumber(10),
            );

            $resContractors = \CCatalogContractor::GetList(
                [],
                ['COMPANY' => $arFields['COMPANY']],
                false,
                false,
                []
            );

            if ($resContractors->Fetch()) {
                continue;
            }
            else
            {
                CCatalogContractor::add($arFields);
            }
        }

        return true;
    }

    // Создание новых подрядчиков и запись в hl блок
    public function testHLblockDataBuilder(): bool
    {
        //прочитать блок в файл
        //self::getHLblockToFile('SotbitMarketplacePartner');

        $limit = $this->hlblock_contractors_count;
        $block_name = $this->block_name;

        //Получаем содержимое Highbloadlock
        $hlBlock = HL\HighloadBlockTable::getList([
            'select' => ["*"],
            'filter' => ['=NAME' => $block_name],
            'cache' => [
                "ttl" => 360000
            ]
        ])->fetch();

        $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
        $entity_data_class = $entity->getDataClass();

        for ($i = 0; $i < $limit; $i++) {

            $users_email = "mail+" . $i . "@mail.ru";

            $rsUser = CUser::GetByLogin($users_email);
            $res = $rsUser->Fetch();
            $users_id = $res['ID'];

            $arFields = array (
                'UF_USER' => $users_id,
                'UF_DATE_REGISTER' => '',
                'UF_ACTIVE' => 1,
                'UF_XML_ID' => "XML+". $i,
                'UF_NAME' => "Company ".$i,
                'UF_TYPE' => 5,
                'UF_CODE' => $this->randomNumber(10),
                'UF_TEL' => '+7(495) 000-00-00',
                'UF_EMAIL' => 'test@test.ru',
            );

            // проверка на сущестование данного поля hl блока
            $result = $entity_data_class::getList(array(
                "select" => array("*"),
                "filter" => Array("UF_NAME"=>$arFields['UF_NAME']),
            ));

            if($result->fetch())
            {
                continue;
            }
            else
            {
                $entity_data_class::add($arFields);
            }
        }
        return true;
    }

    // Создание новых свойств в конкретном инфоблоке
    public function testInfoBlockDataBuilder(): bool
    {
        $limit = $this->properties_count;
        $info_block_id = $this->info_block_id;

        for ($i = 0; $i < $limit; $i++)
        {
            $arFields = Array(
                "NAME" => "property N".$i,
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => "n".$i,
                "PROPERTY_TYPE" => "T",
               // "ROW_COUNT" => 2,
               // "COL_COUNT" => 30,
                //"LIST_TYPE" => 'L',
                "MULTIPLE" => 'N',
                "XML_ID" => '',
                "MULTIPLE_CNT" => 1,
                "LINK_IBLOCK_ID" => 0,
                "WITH_DESCRIPTION" => 'Y',
                "SEARCHABLE" => 'Y',
                "FILTRABLE" => 'N',
                "IS_REQUIRED" => 'N',
                "VERSION" => 1,
                "PROPERTY_XML_ID" => '',
                "IBLOCK_ID" => $info_block_id,
           );

            // проверка наличия данного свойства
            $res = CIBlock::GetProperties($info_block_id, Array(), Array( 'CODE' => $arFields['CODE']));

            if($res->Fetch())
            {
                continue;
            }
            else
            {
                $ibp = new \CIBlockProperty;
                $property_id = $ibp->Add($arFields);

                $this->addFeatureToPropertyIblock($property_id); // Adding a feature ("DETAIL_PAGE_SHOW" = Y) to a property

                $ar_all_values[1] = Array('SORT'=>500, 'VALUE'=>"yes", "DEF"=>"Y", "XML_ID"=>"xml_1");
                $ar_all_values[2] = Array('SORT'=>500, 'VALUE'=>"no", "DEF"=>"N", "XML_ID"=>"xml_2");

                $ibp ->UpdateEnum( $property_id, $ar_all_values); // Adding a feature ("XML_ID") to a property
            }
        }
        return true;
    }

    // Создание новых продуктов в конкретном инфоблоке
    public function testInfoBlockProductBuilder(): bool
    {
        $limit = $this->product_count;
        $info_block_id = $this->info_block_id;

        $reserve = $this->product_settings_array['reserve'];
        $price = $this->product_settings_array['price'];
        $max_reserve = $this->product_settings_array['max_reserve'];

        for ($i = 0; $i < $limit; $i++) {

            $arFields = [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $info_block_id,
                "CREATED_BY" => 1,
                "SEARCHABLE_CONTENT" => 1,
                "WF_STATUS_ID" => 1,
                "XML_ID" => 670,
                "EXTERNAL_ID" => 670,
                "USER_NAME" => '(admin)',
                "CREATED_USER_NAME" => '(admin)',
                "LID" => 's1',
                "BP_PUBLISHED" => 'Y',
                "LOCK_STATUS" => 'green',
                "NAME" => "name_p_".$i,
                "CODE" => "name_p_".$i,
                "DETAIL_TEXT" => "detail_text_p_".$i,
                'DETAIL_TEXT_TYPE' => 'html',
                "PREVIEW_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/local/pic/no_photo.jpg"),
                "PREVIEW_TEXT" => "preview_text_p_".$i,
                'PREVIEW_TEXT_TYPE' => 'html',
                "DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/local/pic/phone_2-1-min.png"),
            ];

            $res = CIBlockElement::GetList(array(), array("NAME"=>$arFields["NAME"], "SHOW_HISTORY"=>"Y"));

            if($res -> Fetch()) {
                continue;
            }
            else
            {
                $oElement = new CIBlockElement();
                $idElement = $oElement->Add($arFields, false, false, true);

                // превращаем в товар. Для примера зададим товару общее количество и вес.
                $productID = CCatalogProduct::add(array("ID" => $idElement, "QUANTITY" => "100", "WEIGHT" =>"200"));

                // установка цены
                $arFields = Array(
                    "CURRENCY"         => "RUB",       // валюта
                    "PRICE"            => $price,      // значение цены
                    "CATALOG_GROUP_ID" => 1,           // ID типа цены
                    "PRODUCT_ID"       => $idElement,  // ID товара (элемента инфоблока)
                );
                CPrice::Add( $arFields );

                //Установка партнёра маркет плейса (по 100 товаров на одного партнёра)
                $PROPERTY_CODE = 'SOTBIT_MARKETPLACE_PARTNER';
                switch ($i) {
                    case $i<$max_reserve: $store_pointer = 0; break;
                    case $i>$max_reserve: $store_pointer = intval($i / $max_reserve); break;
                }
                $Partner = 'XML+'.$store_pointer;
                CIBlockElement::SetPropertyValues($idElement, $info_block_id, $Partner, $PROPERTY_CODE);

                //Установка артикля
                $PROPERTY_CODE = 'article';
                $article =  "n (n".$i.")";
                CIBlockElement::SetPropertyValues($idElement, $info_block_id, $article, $PROPERTY_CODE);

                //Установка остальных свойств в "yes"
                $res = CIBlockElement::GetProperty($info_block_id, $idElement, array("sort" => "asc"), Array());
                $counter = 1;
                while( $r = $res -> Fetch() )
                {
                    $PROPERTY_CODE = $r["CODE"];
                    if($PROPERTY_CODE == "article" || $PROPERTY_CODE == "SOTBIT_MARKETPLACE_PARTNER")
                    {
                        continue;
                    }
                    CIBlockElement::SetPropertyValues($idElement, $info_block_id, "value ".$counter++, $PROPERTY_CODE);
                };

                // добавим на склад
                $resStores = \CCatalogStore::GetList(
                    [],
                    [ 'TITLE' => "stock+".$store_pointer ],
                    false,
                    false,
                    []
                );
                $res = $resStores->Fetch();

                $arFields = Array(
                    "PRODUCT_ID" => $idElement,
                    "STORE_ID"   => $res['ID'],
                    "AMOUNT"     => $reserve,
                );
                CCatalogStoreProduct::Add($arFields);

            }
        }
        return true;
    }


    //установка характеристики ("DETAIL_PAGE_SHOW" = Y) конкретного свойства
    public function addFeatureToPropertyIblock(int $property_id)
    {
        if(!$property_id)
            return false;
        \Bitrix\Iblock\Model\PropertyFeature::setFeatures(
            $property_id,
            [
                [
                    "FEATURE_ID"=>"DETAIL_PAGE_SHOW",
                    "IS_ENABLED" => "Y",
                    "MODULE_ID" => "iblock"
                ]
            ]
        );
    }

    // запись содержимого highload блока в файл
    public static function getHLblockToFile($hlblock_name)
    {
        $user_builder = new self();

        $block_data = $user_builder->getHLblockData($hlblock_name);
        while($data = $block_data->Fetch())
        {
            //debug($data);
            file_put_contents(__DIR__.'/'.__LINE__.'.txt', print_r($data, true), FILE_APPEND);
        }
        //die();
    }

    // получаем содержимое highload блока
    public function getHLblockData(string $block_name): \Bitrix\Main\ORM\Query\Result
    {
        $hlBlock = HL\HighloadBlockTable::getList([
            'select' => ["*"],
            'filter' => ['NAME' => $block_name],
            'cache' => [
                "ttl" => 360000
            ]
        ])->fetch();

        $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
        $entity_data_class = $entity->getDataClass();

        $rsData = $entity_data_class::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array("") // Задаем параметры фильтра выборки
        ));

        return $rsData;
    }

}