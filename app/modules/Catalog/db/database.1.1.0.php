<?php

$catalog_option = new Application_Model_Option();
$catalog_option->find('catalog', 'code');
$library = new Media_Model_Library();

if(!$catalog_option->getId()) {

    $library->setName('Set Meal')->save();

    $icon_paths = array(
        '/catalog/catalog1.png',
        '/catalog/catalog2.png',
        '/catalog/catalog3.png',
        '/catalog/catalog4.png',
        '/catalog/catalog5.png',
        '/catalog/catalog6.png',
        '/catalog/catalog7.png',
        '/promotion/discount4.png',
        '/catalog/catalog8.png',
        '/catalog/catalog9.png',
        '/catalog/catalog10.png',
        '/catalog/catalog11.png',
    );

    $icon_id = 0;
    foreach($icon_paths as $key => $icon_path) {
        $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
        $image = new Media_Model_Library_Image();
        $image->setData($datas)->save();

        if($key == 0) $icon_id = $image->getId();
    }

} else {
    $library->find($catalog_option->getLibraryId());
    $icons = $library->getIcons();
    $icons->next();
    $icon_id = $icons->current()->getId();
}

$datas = array(
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'set_meal',
    'name' => 'Set Meal',
    'model' => 'Catalog_Model_Product',
    'desktop_uri' => 'catalog/application_menu/',
    'mobile_uri' => 'catalog/mobile_menu/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 35
);

$option = new Application_Model_Option();
$option->setData($datas)->save();
