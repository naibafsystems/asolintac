<?php

/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	JoomDOC
 * @author      ARTIO s.r.o., info@artio.net, http:://www.artio.net
 * @copyright	Copyright (C) 2011 Artio s.r.o.. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// register class JFilterOutput in autoload
jimport('joomla.filter.filteroutput');

class JoomDOCString extends JString {

    /**
     * Crop string into given length.
     * Method strip HTML tags and crop string to given length after last space.
     * From croped string strip all characters which are not letter or number.
     * At the end of string add tail according to language constant JOOMDOC_CROP.
     *
     * @param string $text string to crop
     * @param int $length crop length
     * @return string
     */
    public static function crop ($text, $length) {
        $chars = '~;!?.,@#$%^&*_-=+{}[]()<>:|"\'´`//\\';
        $text = strip_tags($text);
        $text = parent::trim($text);
        if (parent::strlen($text) <= $length) {
            return $text;
        }
        $text = parent::substr($text, 0, $length);
        $lastSpace = parent::strrpos($text, ' ');
        $text = parent::substr($text, 0, $lastSpace);
        $text = parent::trim($text);
        while (($length = parent::strlen($text))) {
            $lastChar = parent::substr($text, $length - 2, 1);
            if (parent::strpos($chars, $lastChar) !== false) {
                $text = parent::substr($text, 0, $length - 1);
            } else {
                break;
            }
        }
        return JText::sprintf('JOOMDOC_CROP', $text);
    }

    public static function urlencode ($string) {
        return urlencode($string);
    }

    public static function urldecode ($string) {
        return urldecode($string);
    }

    /**
     * Safe String. Remove no letter's, no number's and other's no allowed Signs.
     *
     * @param string $string String to safe
     * @return string
     */
    public static function safe ($string) {
        static $items;
        if (is_null($items)) {
            $letters[] = 'abcdefghijklmnopqrstuvwxyz';
            $letters[] = 'áćéǵíḱĺḿńóṕŕśúǘẃýź';
            $letters[] = 'äëḧïöẗüẅẍÿ';
            $letters[] = 'åůẘẙ';
            $letters[] = 'ǎčďěǧȟǐǰǩľňǒřšťǔǚž';
            $letters[] = '.-_ ';
            $items = array();
            foreach ($letters as $item)
                $items = array_merge($items, parent::str_split($item), parent::str_split(parent::strtoupper($item)));
            $items = array_merge($items, parent::str_split('0123456789'));
        }
        $string = parent::str_split($string);
        $safe = '';
        foreach ($string as $item)
            if (in_array($item, $items))
                $safe .= $item;
        return parent::trim($safe);
    }

    /**
     * Get string URL safe.
     *
     * @param string $string
     * @return string
     */
    public static function stringURLSafe ($string) {
    	$separator = '/';
        $cleanString = JPath::clean($string, $separator);
        $segments = explode($separator, $cleanString);
        if (is_array($segments)) {
            $segmentsSafe = array_map('JApplication::stringURLSafe', $segments);
            $urlSafe = implode($separator, $segmentsSafe);
            return $urlSafe;
        }
        $string = JApplication::stringURLSafe($string);
        return $string;
    }
    
    /**
     * Replace directory separator (value of constant DIRECTORY_SEPARATOR) with mark {ds}.
     * To decode use JoomDOCString::dsDecode.
     * @param string $string
     * @return string
     */
    public static function dsEncode($string) {
    	return str_replace(DIRECTORY_SEPARATOR, '{ds}', $string);
    }
    
    /**
     * Restore directory separator encoded with JoomDOCString::dsEncode.
     * @param string $string
     * @return string
     */
    public static function dsDecode($string) {
    	return str_replace('{ds}', DIRECTORY_SEPARATOR, $string);
    }
}
?>