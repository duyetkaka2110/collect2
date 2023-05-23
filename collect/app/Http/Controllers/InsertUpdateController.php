<?php

namespace App\Http\Controllers;

use Carbon;

class InsertUpdateController extends Controller
{
    /**
     * 追加データ
     * string $mysystemname システム管理名
     * @return array
     */
    public static function datainsert(string $mysystemname)
    {
        $data = array(
            "created_at" =>  Carbon\Carbon::now()->format("Y/m/d H:i:s"), // ・作成日時：現在日時をセット	
            "created_system" => $mysystemname, // ・作成機能
            "created_user" => Auth()->user()->user_id // ・作成者：ログインユーザIDの値をセット	
        );
        return array_merge(self::dataupdate($mysystemname), $data);
    }

    /**
     * 更新データ
     * string $mysystemname システム管理名
     * @return array 
     */
    public static function dataupdate(string $mysystemname)
    {
        return array(
            "updated_at" =>  Carbon\Carbon::now()->format("Y/m/d H:i:s"), // ・更新日時：現在日時をセット	
            "updated_system" => $mysystemname, // ・更新機能	
            "updated_user" => Auth()->user()->user_id // ・更新者：ログインユーザIDの値をセット	
        );
    }

    /**
     * SQL分のWHEREのLIKEを取得
     * @param  $list 一覧表示 
     * @param  $str_input 画面入力 
     * @param  $column 検索
     * @return 一覧
     */
    public static function getWhereLike($list, string $str_input, string $column)
    {
        // 半角スペースまたは全角スペースがある場合
        $pattern = '/( )/';
        $str = str_replace("　", " ", trim($str_input));
        if (preg_match($pattern, $str)) {
            $strArr = explode(" ", $str);
            foreach ($strArr as $s) {
                if ($s) {
                    $list = $list->where($column, "LIKE",  "%" . $s . "%");
                }
            }
        } else {
            $list = $list->where($column, "LIKE",  "%" . $str . "%");
        }
        return $list;
    }
    /**
     * SQL分のWHEREのLIKEを取得
     * @param  $list 一覧表示 
     * @param  $str_input 画面入力 
     * @param  $column 検索
     * @return 一覧
     */
    public static  function getWhereLikeConcat($list, string $str_input)
    {
        // 半角スペースまたは全角スペースがある場合
        $pattern = '/( )/';
        $str = str_replace("　", " ", trim($str_input));
        if (preg_match($pattern, $str)) {
            $strArr = explode(" ", $str);
            foreach ($strArr as $s) {
                if ($s) {
                    $list = $list->whereRaw("CONCAT(IFNULL(u.first_name, ''), IFNULL(u.family_name, '')) LIKE ?", ["%" . $s . "%"]);
                }
            }
        } else {
            $list = $list->whereRaw("CONCAT(IFNULL(u.first_name, ''), IFNULL(u.family_name, '')) LIKE ?", ["%" . $str . "%"]);
        }
        return $list;
    }
}
