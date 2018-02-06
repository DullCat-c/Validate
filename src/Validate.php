<?php

namespace Validate;

class Validate extends Rule
{
    //使用函数
    public function validate($rules, $data, $pre = '')
    {
        if ($rules['require']) { //指定了必要性检查
            foreach ($rules['require'] as $filed) {
                if (strpos($filed, '|')) { //如果是字符串用|分割前面是参数别名，后面是错误返回
                    $tmp = explode('|', $filed);
                    $filed = $tmp[0];
                    $error = $tmp[1] . '没有指定值';
                } else {
                    $error = $filed . "没有指定值";
                }
                if (!isset($data[$filed]))
                    $this->showError($error);
                else //回收垃圾变量
                    unset($error);
            }
        }
        $Rule = new Rule();
        $map = $Rule->setValue($rules, $data, $pre);
        $error = $Rule->getError();
        $this->showError($error);
        return $map;
    }

    /**
     * 根据模型操作的返回情况发送应答消息，
     * $this->send($bool,array(array(w),array(为false需要发送的数组)));该模式为双数组
     * $this->send($bool,'message|errormessage');该模式为双字符串模式 常用
     * $this->send($bool,array(array(为true时要发送的数组),'errormessage'));该模式为交叉模式
     * @param array $data 要发送的数据
     * @param boolean $bool 模型操作值
     */

    public function send($bool, $data)
    {
        if (is_string($data)) {
            $info = explode('|', $data);
            // 如果因为传过来的是数组和字符串结合的杂牌组合,这里就要根据数目判断显示了
            if (count($info) < 2) {
                $this->echojson('', $info['0'], $bool ? 1 : 0);
            } else { // 第一个成功消息，第二个失败消息
                list($message, $errormessage) = $info;
                $bool and $this->echojson('', $message, 1) or $this->echojson('', $errormessage, 0);
            }
        } elseif (is_array($data)) {
            if (!isset($data['0'])) { // 这里主要是递归一次后会没有['0']这个索引直接返回，所以该方法支持ajaxReturn的双重交叉模式
                $this->echojson($data);
                return;
            }
            for ($i = 0; $i < 2; $i++) {
                $bool and $this->send($bool, $data['0']) or $this->send($bool, $data['1']);
            }
        }
        return;
    }


    //发送错误信息
    protected function showError($error)
    {
        if (!empty($error)) {
            $this->send(false, array(array(), array('data' => null, 'info' => $error, 'status' => 0)));
        }
    }


    //json输出,感谢ThinkPHP框架
    protected function echojson($data)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $info = array();
            $info['data'] = $data;
            $info['info'] = array_shift($args);
            $info['status'] = array_shift($args);
            $data = $info;
        }
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}