<?php

class Core_Model_Lib_String extends Core_Model_Default
{

    public static function camelize($str) {
        $str = trim(preg_replace('/[[:upper:]]/',' \0', $str));
        return strtolower(strtr($str, ' ', '_'));
    }

    public static function formatShortName($name) {
        $shortname = trim($name);

        if(mb_strlen($shortname, 'utf8') > 9) {
            $shortname = trim(mb_substr($name, 0 , 4, 'utf8')).'...';
            $shortname.= trim(mb_substr($name, strlen($name)-4 , strlen($name), 'utf8'));
        }

        return $shortname;
    }

    public static function format($str, $tolower = false) {
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
        $str = preg_replace('/&amp;/', 'AND', $str);
        $str = preg_replace('/&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml|&amp;);/', '\1', $str);
        $str = preg_replace('/&([A-za-z]{2})(?:lig);/', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('/\W/', '', $str);
        if($tolower) $str = strtolower($str);

        return $str;
    }

    public static function truncate($str, $limit, $replacement = '...') {
        if (strlen($str) < $limit) return $str;

        $str = strrev(substr($str, 0, $limit));
        $str = strrev(substr($str, strpos($str, ' ')));
        return trim($str) . $replacement;
    }

    public function stripAccents($str) {
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    public function generate($length = 6) {

        $characts    = 'abcdefghijklmnopqrstuvwxyz';
        $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characts   .= '1234567890';
	$random_code = '';

	for($i=0;$i < $length;$i++) {
            $random_code .= substr($characts,rand()%(strlen($characts)),1);
	}

        return $random_code;
    }

}