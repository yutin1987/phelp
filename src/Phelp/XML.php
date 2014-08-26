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
    private static function appendXml($data, $xmlObj)
    {
        foreach ($data as $parent => $child) {
            if (is_array($child)) {
                if (array_keys($child) !== range(0, (count($child) - 1))) {
                    foreach ($child as $key => $item) {
                        $sub = $xmlObj->addChild($parent);
                        self::appendXml($item, $sub);
                    }
                } else {
                    $sub = $xmlObj->addChild($parent);
                    self::appendXml($child, $sub);
                }
            } else {
                $xmlObj->addChild($parent, $child);
            }
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
            '<?xml version="1.0" encoding="UTF-8" ?><'.$root.'/>'
        );

        $data = json_decode(json_encode($data), true);

        self::appendXml($data, $xmlObj);

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