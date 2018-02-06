<?php
namespace Validate;

/**
 * Class Rule
 * @package Validate
 * This is a base class for validated rule , You can increase as needed.
 * 这是validate的基础类,你可以随意增加自己所需要的规则
 */
class Rule
{
    public $error;
    //正则
    public static $regex = array(
        'time'=>'/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/', //检查时间
        'tel'=>'/^1((3[0-9])|(4[5|7])|(5[0|1|2|3|5|6|7|8|9])|(7[0-9])|(8[0-9]))\\d{8}$/',
        'qq'=>'/^[1-9][0-9]{4,9}$/',
        'email'=>'/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}\b/'
    );

    public static function isTel($var,$allowEmpty=false){
        if($allowEmpty){
            $var = trim($var);
            $ret = (!empty($var))?(preg_match(self::$regex['tel'], $var)?$var:false):true;
        }else{
            $ret = preg_match(self::$regex['tel'], $var)?$var:false;
        }
        return $ret;
    }

    public static function isPrice($var,$allowEmpty=false){

        if($allowEmpty){
            $var = trim($var);
            $ret = (!empty($var))?(preg_match(self::$regex['price'], $var)?$var:false):true;
        }else{
            $ret = preg_match(self::$regex['price'], $var)?$var:false;
        }
        return $ret;
    }

    public static function isQQ($var,$allowEmpty=false){
        if($allowEmpty){
            $var = trim($var);
            $ret = (!empty($var))?(preg_match(self::$regex['qq'], $var)?$var:false):true;
        }else{
            $ret = preg_match(self::$regex['qq'], $var)?$var:false;
        }
        return $ret;
    }

    public static function isEmail($var,$allowEmpty=false){
        if($allowEmpty){
            $var = trim($var);
            $ret = (!empty($var))?(preg_match(self::$regex['email'], $var)?$var:false):true;
        }else{
            $ret = preg_match(self::$regex['email'], $var)?$var:false;
        }
        return $ret;
    }

    //提取数字
    public static function isNumber($var){
        return $var = isset($var) && $var!=='' ? intval($var) :false;
    }

    //提取浮点
    public static function isDouble($var){
        return $var = isset($var) ? floatval($var) :false;
    }

    //验证字符串
    public static function isString($var){
        return $var = isset($var) ? is_string($var) ? $var :false :false;
    }

    //检查长度
    public static function checkLength($var,$length='0',$condition='gt'){
        switch ($condition){
            case 'gt':
                $var = mb_strlen($var,'UTF8')>$length ?$var:false;
                break;
            case 'lt':
                $var = mb_strlen($var,'UTF8')<$length ?$var:false;
                break;
            case 'eq':
                $var = mb_strlen($var,'UTF8')==$length ?$var:false;
                break;
            case 'between':
                $conarr = explode('|',$length);
                if(count($conarr)==2&&is_numeric($conarr['0'])&&is_numeric($conarr['1']))
                    $var = mb_strlen($var,'UTF8')>$conarr['0']&&mb_strlen($var,'UTF8')<$conarr['1'] ?$var:false;
                break;
        }
        return $var;
    }

    //判断数字大小
    public static function numBetween($var,$start,$end){
        $var = (is_numeric($var)&&$var >$start && $var<$end) ?$var :false;
        return $var;
    }

    // 用于批量处理时候的验证，是否符合一个id或者一组id(mysql)
    public static function isIds($var){
        if (is_string($var)&&strpos($var, ',')){
            $var = explode(',', $var);
        }
        if (is_array($var)) {
            foreach ($var as &$v){
                if (!self::isNumber($v))
                    return false;
                else{
                    $v = strval(self::isNumber($v));
                }
            }
        }else { // 单个id
            return array(strval(self::isNumber($var)));
        }
        return $var;
    }

    //数据库like条件(mysql)
    public static function likeString($var){
        $var = self::isString($var);
        $var && $var = array('like','%'.trim($var).'%');
        return $var;
    }

    /**
     * 表单搜索进行拼接数据库的where条件公共设置
     * @param array $rules key为字段 value为回调函数 如array('id'=>'trueInt|错误')
     * @param array $data 需要处理的数据，一般为$_GET 或者$_POST
     * @param string $pre 表前缀，这个是为了下面查询是链接查询，两个表中有相同字段需要别名增加的，默认可不指定
     * @return array $map 模型需要的$where数组
     */
    public function setValue($rules,$data,$pre=''){
        foreach ($data as $k =>$v){
            if(in_array($k,array_keys($rules))){
                $rule = $rules[$k];
                if (is_array($rule)){ //如果数组，第一个是回调函数，第二个是参数(如果有多个参数,传数组),最后一个是错误信息
                    is_array($rule['1']) ? $call_arr = $rule['1'] : $call_arr = array($rule['1']);
                    array_unshift($call_arr, $v);
                    $info = call_user_func_array(array($this,$rule['0']), $call_arr);
                    $info or $errormessage = $rule['2'];
                }else{
                    if (strpos($rule, '|')) { //如果是字符串用|分割前面是回调函数，后面是错误信息，该方法用于没有参数的回调
                        $rule = array_shift( $tmp= explode('|', $rule));
                        $errormessage = array_pop($tmp);
                    }
                    $info = self::$rule($v);
                }
                ($info !==false)? $map[$pre.$k] = $info:$this->error[$k] = $errormessage;
            }
        }
        return $map;
    }


    //用于执行PHP自带函数
    public static function __callStatic($funcname,$args){
        $funcarr = get_defined_functions(); //返回所有函数
        if (in_array($funcname, $funcarr['internal'])) {
            $ret = call_user_func_array($funcname, $args);
            if ($ret) {
                if (is_bool($ret)) {
                    return array_shift($args);
                }else{
                    return $ret;
                }
            }else {
                return $ret;
            }
        }

    }

    //错误信息
    public function getError(){
        return array_shift($this->error);
    }

}