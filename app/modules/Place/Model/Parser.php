<?php

class Place_Model_Parser extends Core_Model_Default {

    protected static $_raw_headers = array(
        "NumEts"                    => "identifier",
        "Statut"                    => "status",
        "Nom Ets."                  => "name",
        "Adresse"                   => "street",
        "CP"                        => "postcode",
        "Localité"                  => "city",
        "Gpslatitude"               => "latitude",
        "Gpslongitude"              => "longitude",
        "Tel1"                      => "phone",
        "Classe hôtel"              => "rating",
        "Type Ets"                  => "type",
        "Texte TG TP TT Bistrot"    => "label",
        "Menu_px_mini"              => "meal_min_price",
        "Menu_px_max"               => "meal_max_price",
        "Nombre total de chambres"  => "number_of_rooms",
        "Tarif minimum"             => "min_price",
        "Tarif maximum"             => "max_price",
        "Infos français"            => "information",
        "Fermetures"                => "opening_details",

        "NumEts_hotellerie"         => "identifier",
        "Nom_Commercial"            => "name",
        "CodePostal"                => "postcode",
        "Commune"                   => "city",
        "Latitude"                  => "latitude",
        "Longitude"                 => "longitude",
        "Telephone"                 => "phone",
        "Classement"                => "rating",
        "Nbre_Chambres"             => "number_of_rooms",
        "Tarifs_ind_mini"           => "min_price",
        "Tarifs_double_maxi"        => "max_price",
        "Infos"                     => "information",
    );

    protected static $_raw_data = array();


    public static function parse($file) {

        self::$_raw_data = array();

        self::_extract($file);
        self::_sanitize();

        return self::$_raw_data;

    }

    protected static function _extract($file) {

        $headers = array_values(array_unique(self::$_raw_headers));
        $headers = array_fill_keys($headers, "");

        $xml = simplexml_load_file($file);
        $header = array();
        foreach($xml->METADATA->children() as $field) {
            $header[] = trim((string) $field['NAME']);
        }

        foreach($xml->RESULTSET->children() as $row) {
            $data = $headers;
            $key = 0;
            foreach($row->children() as $row_data) {
                $current_key = $header[$key++];
                $data[self::$_raw_headers[$current_key]] = trim((string) $row_data->DATA);
            }

            self::$_raw_data[] = $data;
        }

    }

    protected static function _sanitize() {

        // Sanitize data
        foreach(self::$_raw_data as $pos => $row) {
            if(!empty($row["rating"])) {
                $row["rating"] = preg_replace("/[^1-9]/", "", $row["rating"]);
            }

            if(!empty($row["information"])) {
                $row["information"] = preg_replace('/\s+/', ' ', $row["information"]);
            }
            if(!empty($row["opening_details"])) {
                $row["opening_details"] = preg_replace('/\s+/', ' ', $row["opening_details"]);
            }

            if(!empty($row["latitude"])) {
                $latitude = self::_sanitizeCoordinate($row["latitude"]);
                $row["latitude"] = $latitude <= 85 && $latitude >= -85 ? $latitude : null;
            }

            if(!empty($row["longitude"])) {
                $longitude = self::_sanitizeCoordinate($row["longitude"]);
                $row["longitude"] = $longitude <= 180 && $longitude >= -180 ? $longitude : null;
            }

            if(empty($row["latitude"]) OR empty($row["longitude"])) {
                $row["latitude"] = null;
                $row["longitude"] = null;
            }

            foreach($row as $key => $value) {
                if(stripos($key, "price") !== false AND !empty($value)) {
                    $row[$key] = str_replace(",", ".", $value);
                }
            }

            self::$_raw_data[$pos] = $row;
        }

    }

    protected static function _sanitizeCoordinate($coordinate) {

        $coordinate = trim(str_replace(" ", "", $coordinate));
        $coordinate = str_replace(",", ".", $coordinate);
        return is_numeric($coordinate) ? floatval($coordinate) : "";

//        $coordinate = trim(preg_replace("/[^-0-9]/", " ", $coordinate));
//        $coordinate = preg_replace('/\s+/', ' ', $coordinate);
//        $parts = explode(" ", $coordinate);
//        $int = $parts[0];
//        unset($parts[0]);
//        $float = implode("", $parts);
//        $coordinate = implode(".", array($int, $float));
        return $coordinate;

    }
}
