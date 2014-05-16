<?php

class Siberian_Layout extends Zend_Layout
{

    const DEFAULT_CLASS_VIEW = 'Siberian_View';

    protected $_base_render = null;

    protected $_pluginClass = 'Siberian_Layout_Controller_Plugin_Layout';

    protected $_action = null;

    protected $_baseActionLayout = null;
    protected $_actionLayout = null;
    protected $_baseDefaultLayout = null;
    protected $_defaultLayout = null;
    protected $_otherLayout = array();
    public $_xml = null;
    public $_html = null;

    protected $_partials = array();

    protected $_partialshtml = array();

    protected $_is_loaded = false;

    public function __construct($options = null, $initMvc = false)
    {
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');
        Siberian_View::setLayout($this);
        parent::__construct($options, $initMvc);
    }

    public static function startMvc($options = null)
    {
        if (null === self::$_mvcInstance) {
            self::$_mvcInstance = new self($options, true);
        }

        if (is_string($options)) {
            self::$_mvcInstance->setLayoutPath($options);
        } elseif (is_array($options) || $options instanceof Zend_Config) {
            self::$_mvcInstance->setOptions($options);
        }

        return self::$_mvcInstance;
    }

    public function setAction($action) {
        $this->_action = $action;
        return $this;
    }

    public function getAction() {
        return $this->_action;
    }

    public function load($use_base) {

        if(!$this->isEnabled()) return $this;

        $this->_createXml($use_base);
        $base = $this->_xml->base;

        // Récupère la vue de base et lui affecte les données (template, title, etc...)
        if($base AND isset($base->class)) {
            $baseView = $this->_getView($base->class);
            $this->setView($baseView);
        }
        else {
            $baseView = $this->getView();
        }

        if($use_base) {
            $baseView->setTitle($base->title);
            $baseView->default_class_name = $this->_action;

            $scripts_path = Core_Model_Directory::getDesignPath();

            $scripts = $base->scripts;
            $metas = $base->metas;

            // Scripts JS
            $js = '';
            $time = time();
            foreach($scripts->js as $files) {
                foreach($files as $file) {

                    $link = '';
                    if($file->attributes()->link) {
                        $link = strpos($file->attributes()->link, '/') === 0  ? '' : $scripts_path.'/';
                        $link .= $file->attributes()->link;
                        $link.= "?$time";
                    }
                    else $link = $file->attributes()->href;

                    if($file->attributes()->if) {
                        $baseView->headScript()->appendFile($link, 'text/javascript', array('conditional' => (string) $file->attributes()->if));
                    }
                    else {
                        $baseView->headScript()->appendFile($link);
                    }
                }
            }

            // Scripts CSS
            foreach($scripts->css as $files) {
                foreach($files as $file) {
                    $src = '';
                    if($file->attributes()->link) $src = $scripts_path.'/'.$file->attributes()->link.'?time='.time();
                    else $src = $file->attributes()->href;
                    $baseView->headLink()->appendStylesheet($src, 'screen', $file->attributes()->if ? (string) $file->attributes()->if : null);
                }
            }

            // Balises meta
            foreach($base->metas as $metas) {
                foreach($metas as $key => $meta) {
                    $type = $meta->attributes()->type;
                    $baseView->addMeta($type, $key, $meta->attributes()->value);
//                    switch($type) {
//                        case 'http-equiv':
//                            $baseView->headMeta()->appendHttpEquiv($key, $meta->attributes()->value);
//                        break;
//
//                        case 'name':
//                        default:
//                            $baseView->headMeta()->appendName($key, $meta->attributes()->value);
//                        break;
//                    }
                }
            }

            // Layout du template de base
            foreach($this->_xml->{$base->template} as $partials) {
                foreach($partials as $key => $partial) {
                    $class = (string) $partial->attributes()->class;
                    $template = (string) $partial->attributes()->template;
                    if(!empty($class) AND !empty($template)) $this->addPartial($key, $class, $template);
                }
            }
        }

        // Layout
        foreach($this->_xml->views as $partials) {
            foreach($partials as $key => $partial) {
                if($use_base OR (!$use_base AND empty($partial->attributes()->no_ajax))) {
                    $class = (string) $partial->attributes()->class;
                    $template = (string) $partial->attributes()->template;
                    if(!empty($class) AND !empty($template)) $this->addPartial($key, $class, $template);
                }
            }
        }

        // Actions
        if(isset($this->_xml->actions)) {
            foreach($this->_xml->actions->children() as $partial => $values) {

                if($partial = $this->getPartial($partial)) {
                    $method = (string) $values->attributes()->name;
                    if(is_callable(array($partial, $method))) {
                        $params = array();
                        if(count($values) == 1) {
                            foreach($values as $key => $value) {
                                $params = (string) $value;
                            }
                        }
                        else {
                            foreach($values as $key => $value) {
                                $params[$key] = (string) $value;
                            }
                        }

                        $partial->$method($params);
                    }
                }
            }
        }

        // Classes dans le body
        if(isset($this->_xml->classes)) {
            $classes = array($baseView->default_class_name);
            foreach($this->_xml->classes->children() as $class) {
                $classes[] = $class->attributes()->name;
            }
            $baseView->default_class_name = implode(' ', $classes);
        }

        if($use_base) $this->setBaseRender("base", (string) $base->template, null);
        elseif(isset($this->_partials['base'])) $this->setBaseRender($this->_partials['base']);
        else $this->setBaseRender($this->getFirstPartial());

        return $this;

    }

    public function unload() {
        $this->_base_render = null;
        $this->_partials = array();
        $this->_partialshtml = array();

        $this->_baseActionLayout = null;
        $this->_actionLayout = null;
        $this->_baseDefaultLayout = null;
        $this->_defaultLayout = null;
        $this->_otherLayout = array();
        $this->_xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><xml/>');

        $this->_is_loaded = false;

        Siberian_View::setLayout($this);

        return $this;

    }

    public function loadEmail($filename, $nodename) {
        $layout = new Siberian_Layout_Email($filename, $nodename);
        $layout->load();
        return $layout;
    }

//    public function update($node) {
//
//        $nodes = array();
//        $path = '/layout/'.$node;
//        $datas = $this->_actionLayout->xpath($path);
//
//        foreach($datas as $data) {
//
//        }
//
//        $path = '/layout/'.$node;
//        $datas = $this->_baseDefaultLayout->xpath($path);
//        foreach($datas as $data) {
//            $name = (string) $data->attributes()->name;
//            if(isset($this->_baseDefaultLayout->$name)) {
//                $nodes[$name] = $this->_baseDefaultLayout->$name;
//            }
//        }
//        Zend_Debug::dump($nodes); die;
//        foreach($nodes as $node) {
//            if(empty($node)) continue;
//            $children = $node->children();
//            foreach($children as $key => $child) {
//                if(!in_array($key, $keys)) $keys[] = $key;
//            }
//        }
//
//        // Fusion des différents XML
//        foreach($keys as $key) {
//
//            switch($key) {
//
//                case "base" :
//                    if($use_base) $this->_process($key);
//                break;
//                case "views" :
//                case "layout" :
//                case "layout_col-left" :
//                case "layout_col-right" :
//                    $this->_process($key);
//                break;
//
//                case "actions" :
//                case "classes" :
//                    $this->_process($key, true);
//                break;
//
//                case "addLayout" :
//                default :
//                break;
//            }
//
//        }
//    }

    public function setBaseRender($key, $template = null, $view = null) {

        if($key instanceof Zend_View) {
            $template = $key->getTemplate();
            $view = $key;
        }
        else {
            $view = is_null($view) ? $this->getView() : $this->_getView($view);
            $view->setTemplate($template);
        }
        if(preg_match('/.phtml$/', $template)) $this->disableInflector();

        parent::setLayout($template);
        $this->_base_render = $view;
        $this->_is_loaded = true;

        return $view;
    }

    public function getBaseRender() {
        return $this->_base_render;
    }

    public function addPartial($key, $view, $template) {
        $this->_partials[$key] = $this->_getView($view)->setTemplate($template);// array('view' => $view, 'template' => $template);
        return $this->_partials[$key];
    }

    public function getPartial($key) {
        return isset($this->_partials[$key]) ? $this->_partials[$key] : null;
    }

    public function getFirstPartial() {
        return reset($this->_partials);
    }

    public function getPartialHtml($key) {
        if(!isset($this->_partialshtml[$key]) AND $this->getPartial($key)) {
            $this->_partialshtml[$key] = $this->getPartial($key)->render($this->getPartial($key)->getTemplate());
        }

        return isset($this->_partialshtml[$key]) ? $this->_partialshtml[$key] : null;
    }

    public function setPartialHtml($key, $html) {
        $this->_partialshtml[$key] = $html;
        return $this;
    }

    public function getHtml() {
        return implode(' ', $this->_partialshtml);
    }

    public function setHtml($html) {
        $this->_html = $html;
        $this->_is_loaded = true;
        return $this;
    }

    public function render($name = null) {

        if(is_null($this->_html)) {

            $this->renderPartials();

            if(!$this->_base_render) {
                debugbacktrace();
            }
            $name = $this->_base_render->getTemplate();

            if ($this->inflectorEnabled() && (null !== ($inflector = $this->getInflector()))) {
                $name = $this->_inflector->filter(array('script' => $name));
            }

            $view = $this->_base_render;

            if (null !== ($path = $this->getViewScriptPath())) {
                if (method_exists($view, 'addScriptPath')) {
                    $view->addScriptPath($path);
                } else {
                    $view->setScriptPath($path);
                }
            }
            elseif (null !== ($path = $this->getViewBasePath())) {
                $view->addBasePath($path, $this->_viewBasePrefix);
            }

            $this->_html = $view->render($name);

        }

        return $this->_html;

    }

    public function isLoaded() {
        return $this->_is_loaded;
    }

    /**
     * @todo Ajouter le addLayout
     *
     * @return type
     */
    protected function _createXml($use_base) {

        // Définition des variables
        $action = $this->_action;
        $module = current(explode('_', $action));
        $filename = $module.'.xml';
        $this->_otherLayout = array();

        $keys = array();
        if($use_base) {
            $this->_baseDefaultLayout = simplexml_load_file(Core_Model_Directory::getDesignPath(true) . '/layout/front.xml', null, LIBXML_COMPACT);
            $this->_defaultLayout = $this->_baseDefaultLayout->default;
        }

        if($use_base AND isset($this->_baseDefaultLayout->$action)) {
            $this->_actionLayout = $this->_baseDefaultLayout->$action;
        }
        elseif(file_exists(Core_Model_Directory::getDesignPath(true) . '/layout/'.$filename)) {
            $this->_baseActionLayout = simplexml_load_file(Core_Model_Directory::getDesignPath(true) . '/layout/'.$filename);
            $this->_actionLayout = $this->_baseActionLayout->$action;
        }
        else {
            return $this->_defaultLayout;
        }

//        // Récupère tous les keepOnly
//        $allowedKeys = array();
//        $path = '/layout/'.$action.'/keepOnly';
//        $datas = $this->_actionLayout->xpath($path);
//        foreach($datas as $key => $data) {
//            $allowedKeys[] = (string) $data->attributes()->name;
//        }
//        $datas = $this->_defaultLayout->xpath($path);
//        foreach($datas as $key => $data) {
//            $allowedKeys[] = (string) $data->attributes()->name;
//        }

        // Récupération des noms des balises
        $nodes = array(
            $action => $this->_actionLayout,
            'default' => $this->_defaultLayout
        );

        $path = '/layout/'.$action.'/addLayout';
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $name = (string) $data->attributes()->name;
            if(isset($this->_baseActionLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseActionLayout->$name;
            }
            elseif(isset($this->_baseDefaultLayout->$name)) {
                $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
            }
        }

        if($use_base) {
            $path = '/layout/default/addLayout';
            $datas = $this->_baseDefaultLayout->xpath($path);
            foreach($datas as $data) {
                $name = (string) $data->attributes()->name;
                if(isset($this->_baseDefaultLayout->$name)) {
                    $nodes[$name] = $this->_otherLayout[$name] = $this->_baseDefaultLayout->$name;
                }
            }
        }

        $path = '/layout/'.$action.'/removeLayout';
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $name = (string) $data->attributes()->name;
            if(isset($nodes['default']->views->$name)) {
                unset($nodes['default']->views->$name);
            }
        }

        if($use_base) {
            $path = '/layout/default/removeLayout';
            $datas = $this->_baseDefaultLayout->xpath($path);
            foreach($datas as $data) {
                $name = (string) $data->attributes()->name;
                if(isset($nodes['default']->views->$name)) {
                    unset($nodes['default']->views->$name);
                }
            }
        }

//        $path = '/layout/'.$action.'/keepOnly';
//        $datas = $this->_actionLayout->xpath($path);
//        foreach($datas as $key => $data) {
//            $name = (string) $data->attributes()->name;
////            Zend_Debug::dump($nodes);
//
//
//            if(isset($nodes['default']->views->$name)) {
//                unset($nodes['default']->views->$name);
//            }
//        }

        foreach($nodes as $node) {
            if(empty($node)) continue;
            $children = $node->children();
            foreach($children as $key => $child) {
                if(!in_array($key, $keys)) $keys[] = $key;
            }
        }

        // Fusion des différents XML
        foreach($keys as $key) {
//            if(!empty($allowedKeys) AND !in_array($key, $allowedKeys)) continue;
            switch($key) {

                case "base" :
                    if($use_base) $this->_process($key, $use_base);
                break;
                case "views" :
                case "layout" :
                case "layout_col-left" :
                case "layout_col-right" :
                    $this->_process($key, $use_base);
                break;

                case "actions" :
                case "classes" :
                    $this->_process($key, $use_base, true);
                break;

                case "addLayout" :
                default :
                break;
            }

        }

        return $this->_xml;

    }

    protected function _process($key, $use_base, $forceAddNode = false) {

        $child = $this->_xml->addChild($key);

        $path = '/layout/'.$this->_action.'/'.$key;
        $datas = $this->_actionLayout->xpath($path);
        foreach($datas as $data) {
            $this->_mergeXml($data, $child, $forceAddNode);
        }

        if($use_base) {
            foreach($this->_otherLayout as $name => $node) {
                $path = '/layout/'.$name.'/'.$key;
                $datas = $node->xpath($path);
                foreach($datas as $data) {
                    $this->_mergeXml($data, $child, $forceAddNode);
                }
            }

            $path = '/layout/default/'.$key;
            $datas = $this->_defaultLayout->xpath($path);
            foreach($datas as $data) {
                $this->_mergeXml($data, $child, $forceAddNode);
            }
        }

    }

    protected function _mergeXml($datas, $node, $forceAddNode = false, $i = 0) {

        $j = 0;
        foreach($datas as $key => $value) {

            if((bool) $value->children()) {

                if(!isset($node->$key) || $forceAddNode) {
                    $child = $node->addChild($key);
                    if((bool) $value->attributes()) {
                        foreach($value->attributes() as $attr_code => $attr_value) {
                            $child->addAttribute($attr_code, $attr_value);
                        }
                    }
                }
                else {
                    $child = $node->$key;
                }

                $this->_mergeXml($value, $child, $forceAddNode, $i+1);

            }
            else {
                if(!isset($node->$key)) $node->addChild($key, $value);
            }
        }

    }

    protected function renderPartials() {
//        try {

            foreach($this->_partials as $key => $_partial) {
                if(!isset($this->_partialshtml[$key])) {
//                    Zend_Debug::dump(get_class($_partial));
                    $this->_partialshtml[$key] = $_partial->render($_partial->getTemplate());
                }
            }

//        }
//        catch(Exception $e) {
//
//        }
//        die('ok1');
        return $this;
    }

    protected function _renderPartial(array $_partial) {
        return $this->_getView($_partial['view'])->render($_partial['template']);
    }

    protected function _getView($class) {

        $classname = self::DEFAULT_CLASS_VIEW;
        if($class) {
            $classname = implode('_', array_map('ucwords', explode('_', $class)));
        }
        try {
            $object = new $classname();
        } catch(Exception $e) {
            Zend_Debug::dump($e);
            die;
        }
        $object->setScriptPath($this->getView()->getScriptPaths());
        return $object;

    }

}
