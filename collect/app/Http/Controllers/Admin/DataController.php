<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Carbon;
use App\GetMessage;
use Auth;
use Session;
use DB;

class DataController extends Controller
{

    public $_timetoday;
    public $_mysystemname = "admin";

    /**
     * 引取管理／台貫マスタ取込画面
     * @param  Request $rq
     * @return view
     */
    public function import(Request $rq)
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $titleheader = "台貫マスタ取込";
        // 先頭ファイル名とキー項目一覧
        $listfilename = config("const.ListTable");
        $ErrMsg = null;
        // Get uploaded CSV file
        $file = $rq->file('file');
        if ($file) {
            $table_name = strtoupper(substr($file->getClientOriginalName(), 0, 12));
            if (Arr::exists($listfilename, $table_name)) {
                $file_data = $this->csvToArray($file);
                if ($file_data && !$file_data["messenger"]) {
                    $flag = true;
                    foreach ($listfilename[$table_name]["key"] as $column) {
                        if (!Arr::exists($file_data["header"], $column)) {
                            $flag = false;
                            break;
                        }
                    }
                    if (!$flag) {
                        // 　　－取得された項目IDにキー項目が存在しない場合、以下エラーメッセージを表示して処理を中止
                        $ErrMsg = GetMessage::getMessageByID("error021");
                    } else {
                        if (isset($file_data["data"][0]) && (count($file_data["header"]) == count($file_data["data"][0]))) {
                            try {
                                DB::beginTransaction();
                                foreach ($file_data["data"] as $d) {
                                    if (isset($d["FUPTIM"]))
                                        $d["FUPTIM"] = $this->DateFormat($d["FUPTIM"]);
                                    if (isset($d["LUPTIM"]))
                                        $d["LUPTIM"] = $this->DateFormat($d["LUPTIM"]);
                                    // ""がある場合はNULLに更新する
                                    if (in_array("", $d, true)) {
                                        foreach ($d as $k => $c) {
                                            if ($c == "") {
                                                $d[$k] = NULL;
                                            }
                                        }
                                    }
                                    $tb = DB::table(strtolower($table_name));
                                    foreach ($listfilename[$table_name]["key"] as $column) {
                                        $tb = $tb->where($column, $d[$column]);
                                    }
                                    $count = $tb->count();
                                    if ($count) {
                                        // ・取得したデータのキー項目の値に合致するデータが対象テーブルに存在する場合、データを更新
                                        $tb->update($d);
                                    } else {
                                        // ・取得したデータのキー項目の値に合致するデータが対象テーブルに存在しない場合、新規にデータを作成
                                        $tb->insert($d);
                                    }
                                }
                                DB::commit();
                                \Session::flash('DoneMsg', str_replace("{p}", $listfilename[$table_name]["name"] . "ファイルの取込", GetMessage::getMessageByID("error007")));
                                return redirect()->back();
                            } catch (Throwable $e) {
                                DB::rollBack();
                                $ErrMsg = GetMessage::getMessageByID("error009");
                                return redirect()->back()->withErrors($ErrMsg);
                            }
                        } else {
                            //　　－選択ファイルから２行目以降のデータを取得し、項目数が先頭行と異なっていた場合、以下エラーメッセージを表示して処理を中止
                            $ErrMsg = GetMessage::getMessageByID("error021");
                        }
                    }
                } else {
                    $ErrMsg = GetMessage::getMessageByID("error021");
                }
            } else {
                $ErrMsg = GetMessage::getMessageByID("error020");
            }
        }
        if ($ErrMsg) {
            return redirect()->back()->withErrors($ErrMsg);
        }
        return view("admin.data.import", compact(
            "titleheader",
            "listfilename"
        ));
    }
    /**
     *　時間フォーマット
     * @param  $date　時間
     * @return datetime
     */
    public function DateFormat($date)
    {
        return \Carbon\Carbon::parse($date)->format("Y/m/d H:i:s");
    }

    /**
     *　取込ファイル
     * @param  $filename ファイル情報
     * @param  $delimiter 配列への区切り文字
     * @return array 配列
     */
    public function csvToArray($filename = '')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $messenger = false;
        $headercount = 0;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {

            while (($row = fgetcsv($handle, 1000, config("const.CSV.delimiter"), config("const.CSV.enclosure"), config("const.CSV.escape"))) !== false) {
                if (!$header) {
                    $row = array_map('strtoupper', $row);
                    $row[0] = preg_replace("/[^A-Z]+/", "", $row[0]);
                    $header =  $row;
                    $headercount = count($header);
                } else {
                    if (count($row) == $headercount) {
                        $data[] = array_combine($header, $row);
                    } else {
                        // ・ヘッダー行とデータ行のカラム数が異なる場合、予期せぬエラーとなる
                        $messenger = GetMessage::getMessageByID("error021");
                        break;
                    }
                }
            }
            fclose($handle);
        }
        return [
            "messenger" => $messenger,
            "data" => $data,
            "header" => array_flip($header),
        ];
    }
}
