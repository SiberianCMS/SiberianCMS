<?php

class Place_Model_Place extends Core_Model_Default {

    const LABEL_PICTURES_PATH = "/images/application/place";

    protected static $_statuses = array(
        1 => array(
            "status_id" => 1,
            "name" => "Adhérent"
        ),
        2 => array(
            "status_id" => 2,
            "name" => "Auberge De Village"
        ),
        3 => array(
            "status_id" => 3,
            "name" => "HDF"
        )
    );

    protected static $_types = array(
        1 => "Hôtel",
        2 => "Restaurant",
        3 => "Hôtel-Restaurant",
    );

    protected static $_labels = array(
        1 => array(
            "label_id" => 1,
            "name" => "Table de Prestige",
            "picto" => "table_de_prestige.png"
        ),
        2 => array(
            "label_id" => 2,
            "name" => "Table Gastronomique",
            "picto" => "table_gastronomique.png"
        ),
        3 => array(
            "label_id" => 3,
            "name" => "Table de Terroir",
            "picto" => "table_de_terroir.png"
        ),
        4 => array(
            "label_id" => 4,
            "name" => "Bistrot Gourmand",
            "picto" => "bistrot_gourmand.png"
        )
    );

    protected static $_label_pictures = array(
        1 => "table_de_prestige.png",
        2 => "table_gastronomique.png",
        3 => "table_de_terroir.png",
        4 => "bistrot_gourmand.png"
    );

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Place_Model_Db_Table_Place';
        return $this;
    }

    public static function getStatuses() {
        $statuses = array();
        foreach(self::$_statuses as $status) {
            $statuses[] = new Core_Model_Default($status);
        }
        return $statuses;
    }

    public static function getTypes() {
        return self::$_types;
    }

    public static function getLabels() {
        $labels = array();
        foreach(self::$_labels as $label) {
            $label['picto'] = self::LABEL_PICTURES_PATH."/{$label['picto']}";
            $labels[] = new Core_Model_Default($label);
        }
        return $labels;
    }

    public function getStatus() {
        if($this->getStatusId()) {
            return !empty(self::$_statuses[$this->getStatusId()]) ? self::$_statuses[$this->getStatusId()]["name"] : "";
        }

        return "";
    }

    public function getType() {
        if($this->getTypeId()) {
            return !empty(self::$_types[$this->getTypeId()]) ? self::$_types[$this->getTypeId()] : "";
        }

        return "";
    }

    public function getLabel() {
        if($this->getLabelId()) {
            return !empty(self::$_labels[$this->getLabelId()]) ? self::$_labels[$this->getLabelId()]["name"] : "";
        }

        return "";
    }

    public function getLabelPicture() {
        if($this->getLabelId() AND !empty(self::$_label_pictures[$this->getLabelId()])) {
            $image = self::$_label_pictures[$this->getLabelId()];
            return self::LABEL_PICTURES_PATH."/$image";
        }

        return "";
    }

    public function getMinPriceLabel() {
        if($this->getStatusId() == 3) {
            return $this->_("Tarif individuel minimum");
        } else {
            return $this->_("Tarif minimum");
        }
    }

    public function getMaxPriceLabel() {
        if($this->getStatusId() == 3) {
            return $this->_("Tarif double maximum");
        } else {
            return $this->_("Tarif maximum");
        }
    }

    public function findCoordinates() {

        $address = array(
            "street" => $this->getStreet(),
            "postcode" => $this->getPostcode(),
            "city" => $this->getCity()
        );

        try {
            list($latitude, $longitude) = Siberian_Google_Geocoding::getLatLng($address);

            if(empty($latitude) OR empty($longitude)) {
                $address["street"] = $this->getName();
                list($latitude, $longitude) = Siberian_Google_Geocoding::getLatLng($address);
            }

            if(!empty($latitude) AND !empty($longitude)) {
                $this->setLatitude($latitude)
                    ->setLongitude($longitude)
                ;
            }

        } catch(Exception $e) {
            $this->setLatitude(null)
                ->setLongitude(null)
            ;
        }

        return $this;

    }

    public function getPictureUrl() {
        return Core_Model_Directory::getPathTo(Customer_Model_Customer::IMAGE_PATH.'/placeholder/no-image.png');
    }

}
