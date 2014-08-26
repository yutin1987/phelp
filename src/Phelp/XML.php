<?php
namespace Phelp;

use \SimpleXMLElement;

/**
 * Simple XML Class
 * 
 * @category Simple
 * @package  XML
 * Easy-to-use library for XML
 */
class XML
{

    /**
     * 加入XML資料
     * 
     * @param object           $data   資料
     * @param SimpleXMLElement $xmlObj XML
     * 
     * @return SimpleXMLElement
     */
    private static function appendXml($data, $xmlObj, $parent = 'root')
    {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                if (is_int($key)) {
                    self::appendXml($item, $xmlObj, $parent);
                } else {
                    self::appendXml($item, $sub, $key);
                }
            }
        } else {
            $xmlObj->addChild($parent, $data);
        }

        return $xmlObj;
    }

    /**
     * object 2 xml
     * 
     * @param object $data 資料
     * @param string $root 根目錄
     * 
     * @return XML String
     */
    public static function obj2xml($data, $root = 'root')
    {
        $xmlObj = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>'
        );

        $data = json_decode(json_encode($data), true);

        self::appendXml($data, $xmlObj, $root);

        return $xmlObj->asXML();
    }
    
    /**
     * xml 2 object
     * 
     * @param object $data 資料
     * 
     * @return Object
     */
    public static function xml2obj($data)
    {
        if (is_string($data)) {
            $data = new SimpleXMLElement($data);
        }

        return json_decode(json_encode($data));
    }
}
?>