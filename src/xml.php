<?php
namespace Phelp;

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

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xmlObj->addChild($key);
                    self::obj2xml($value, $subnode);
                } else {
                    self::obj2xml($value, $xmlObj);
                }
            } else {
                $xmlObj->addChild($key, $value);
            }
        }

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

        return json_decode(json_encode($xml));
    }
}
?>