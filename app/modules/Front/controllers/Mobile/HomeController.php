<?php

class Front_Mobile_HomeController extends Application_Controller_Mobile_Default {

    public function overviewAction() {
        $this->getRequest()->setParam('overview', 1);
        $this->getSession()->isOverview = true;
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function colorsAction() {
        $this->loadPartials("front_index_index");
        $this->getLayout()->addPartial("style", "core_view_mobile_default", "application/customization/css.phtml");
    }

    public function indexAction() {
        $this->getSession()->isOverview = false;
        parent::indexAction();
    }

    public function viewAction() {
        $this->loadPartials('home_mobile_view_l'.$this->_layout_id, false);
    }

    public function listAction() {
        $html = $this->getLayout()->addPartial("homepage_scrollbar", "core_view_mobile_default", "home/l1/list.phtml")->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction() {

        if($retina = $this->getRequest()->getParam('retina')) {
            $url = $this->getApplication()->getBackgroundImageUrl("retina4");
        } else {
            $url = $this->getApplication()->getBackgroundImageUrl();
        }

        $this->getLayout()->setHtml($url);
//        $this->_sendHtml(array('url' => $url));

    }

    public function findallAction() {

        $option_values = $this->getApplication()->getPages(10);
        $data = array(array('pages'));
        $color = $this->getApplication()->getBlock('tabbar')->getImageColor();

        foreach($option_values as $option_value) {
            $data['pages'][] = array(
                'name' => $option_value->getTabbarName(),
                'is_active' => $option_value->isActive(),
                'url' => $option_value->getUrl(null, array('value_id' => $option_value->getId()), false),
                'icon_url' => $this->_getColorizedImage($option_value->getIconId(), $color),
                'icon_is_colorable' => $option_value->getImage()->getCanBeColorized(),
                'position' => $option_value->getPosition()
            );
        }

        $option = new Application_Model_Option();
        $option->findTabbarMore();
        $data['more_items'] = array(
            'name' => $option->getTabbarName(),
            'is_active' => $option->isActive(),
            'url' => "",
            'icon_url' => $this->_getColorizedImage($option->getIconUrl(), $color),
            'icon_is_colorable' => 1,
        );

        $option = new Application_Model_Option();
        $option->findTabbarAccount();
        $data['customer_account'] = array(
            'name' => $option->getTabbarName(),
            'is_active' => $option->isActive(),
            'url' => $this->getUrl("customer/mobile_account_login"),
            'login_url' => $this->getUrl("customer/mobile_account_login"),
            'edit_url' => $this->getUrl("customer/mobile_account_edit"),
            'icon_url' => $this->_getColorizedImage($option->getIconUrl(), $color),
            'icon_is_colorable' => 1,
        );

        $data['limit_to'] = $this->getApplication()->getLayout()->getNumberOfDisplayedIcons();
        $data['layout_id'] = 'l'.$this->getApplication()->getLayoutId();

        $this->_sendHtml($data);

    }

}