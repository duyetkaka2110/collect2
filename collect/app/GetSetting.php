<?php

namespace App;

use File;
use Config;

 class GetSetting
{
    /**
     *  ファイルからIDで設定を取る
     * @access public
     * @param string $Txt
     * @return テキスト
     */
     public static function getSettingByID(string $Txt)
    {
        // $filename = storage_path(env('FILE_SETTING'));
        // $filename = storage_path("setting.txt");
        $filename = storage_path(\config("getfile.FILE_SETTING"));
        $contents = File::get($filename);
        $listST = array();
        $contents = explode("\n", $contents);
        foreach ($contents as $line) {
            $set = explode(":", $line);
            //clear comment
            $set[1] = explode("//",$set[1]);
            $set[1] = trim($set[1][0]);
            if($Txt == $set[0]){
                return $set[1];
            }
        }
    }
}
