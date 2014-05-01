<?php

/**
 * 处理字符串的常用方法
 * modified from StringUtil.java
 * 
 * @author yuyong.li
 * 2012-2-20 14:01:44
 */
class StringUtil {

    
    function __construct() {
        ;
    }

    /**
     * 生成特定长度的随机字符串
     * @param int $length 生成的字符串的长度
     * @return string 随机字符串 
     */
    function generate_random_string($len) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz"; 
        $string = "";
        for (; $len >= 1; $len--) {
            $position = rand() % strlen($chars);
            $string.=substr($chars, $position, 1);
        }
        return $string;
    }
    
    

    /**
     * 将长字符串进行格式化为指定长度的字符串，超出的部分用"…"代替
     * @author GGCoke
     * @param String $string 原字符串
     * @param int $length 保留的字数
     * @param String $default 如果$string为空时显示的内容
     * @return String 格式化之后的字符串
     */
    static function format_long_string($string, $length, $default){
        if ($string == null || strlen($string) == 0){
            return $default;
        } else {
            if (strlen($string) <= $length)
                return $string;
            /** 此处如果使用substr会出现乱码，因此需要使用mb_substr,同时指定编码格式 */
            $string = mb_substr($string, 0, $length, 'utf-8');
            $string .= '…';
            return $string;
        }
    }
}
//end of script