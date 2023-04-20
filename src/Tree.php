<?php

namespace zhanyichen\Tools;


class Tree
{
    private static $tem_arr=[]; // 树排序时需要的数据结构

    /**
     * 将数组转化成树型结构
     * @param $arr array 待转化的数组
     * @param $top_pid string|int 根级pid 要求顶级父id为0
     * @param $id_str string id字段名
     * @param $pid_str string pid字段名
     * @param $child_name string 将要存放的孩子名称
     * @param $isIdToKey bool 是否将id作为key(默认否)
     * @return array
     */
    static function array2tree(&$arr,$top_pid=0,$level=0,$id_str='id',$pid_str='pid',$child_name='child', $isIdToKey = true){
        if (empty($arr)) return $arr;
        $tem_arr = array();
        foreach ($arr as $key => $value){
            //循环找到pid是当前pid的行
            if( ($key !== $child_name) && ($value[$pid_str] == $top_pid)){
                unset($arr[$key]);
                $child_result = self::array2tree($arr,$value[$id_str],$level+1,$id_str,$pid_str,$child_name, $isIdToKey);
                $value['tree_level'] = $level;
                if ($isIdToKey) {
                    $tem_arr[$child_name][$value[$id_str]] = array_merge($value,$child_result);
                } else {
                    $tem_arr[$child_name][] = array_merge($value,$child_result);
                }
            }
        }
        if($level == 0) return $tem_arr[$child_name];
        return $tem_arr;
    }

    /**
     * 数组转顺序数组
     * @param $arr array 待转化的数组
     * @param $relation_field string 需要加关系缩进的字段
     * @param $id_str string id字段名
     * @param $pid_str string pid字段名
     * @param $child_name string 将要存放的孩子名称
     * @return array
     * @throws
     */
    static function array2orderArr($arr,$relation_field='',$id_str='id',$pid_str='pid',$child_name='child', $topParentIdVal = ''){
        if (empty($arr))  return [];
        // 验证, 如果不满足条件, 则退出
        $first_data = current($arr);
        if (empty($arr))  return [];
        if(!is_array($arr) || !isset($first_data[$id_str]) || !isset($first_data[$pid_str]) ){
            throw new \RuntimeException('数组错误，不存在或未设置正确参数');
        }
        // 进行树关系排序
        self::$tem_arr = array();
        self::_selfJoinArrayOrder($arr,$topParentIdVal,0,$id_str,$pid_str,$child_name);

        // 修饰层级 需要设置relation_field参数
        if( !empty($relation_field) and !empty(self::$tem_arr) and isset(self::$tem_arr[0][$relation_field]) ){
            foreach(self::$tem_arr as $key => &$val){
                $val['_tree_display_pre_str'] = '';
                if($val['_tree_level'] > 0){
                    $tem_val = str_pad('',$val['_tree_level']*6,'　');
                    $tem_val .= (isset(self::$tem_arr[$key+1]) and ($val['_tree_level'])<=self::$tem_arr[$key+1]['_tree_level'])?'├─':'└─';
                    $val['_tree_display_pre_str'] = $tem_val;
                }
            }
        }

        return self::$tem_arr;
    }
    /**
     * 自连接数组顺序化
     * @param $arr array 待转化的数组
     * @param $top_pid_value int 根级pid 要求顶级父id为0
     * @param $level int 自动给
     * @param $id_str string id字段名
     * @param $pid_str string pid字段名
     * @param $child_name string 将要存放的孩子名称
     */
    static private function _selfJoinArrayOrder(&$arr,$top_pid_value=0,$level=0,$id_str='id',$pid_str='pid',$child_name='child'){
        foreach ($arr as $key => &$value){
            $value['_tree_level'] = $level;
            //循环找到pid是当前pid的行
            if( ($key !== $child_name) && ($value[$pid_str] == $top_pid_value)){
                self::$tem_arr[] = $value;
                unset($arr[$key]);
                self::_selfJoinArrayOrder($arr,$value[$id_str],$level+1,$id_str,$pid_str,$child_name);
            }
        }
    }
}