<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;
use App\Models\t_carry_ins;
use App\Models\RequestsDetailAsRD;
use Mail;
use App\GetMessage;

class CarryController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //　取引先マスタ
    protected $mmst2 = "pldtcustmmst as mmst2"; //　取引先マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //　商品マスタ
    protected $enums = "m_enums as E"; //　汎用マスタ
    protected $t_requests = "t_requests as R"; //依頼テーブル
    protected $t_request_details = "t_request_details"; //依頼内容テーブル
    protected $t_carry_ins = "t_carry_ins"; // 搬入予定テーブル
    protected $ErrRECVID = [];

    /**
     * 搬入予定データ出力機能
     * @return メッセージ
     */
    public function export()
    {
        $ErrMsg = null;
        try {
            DB::beginTransaction();
            $this->_CarryUpdate();
            $folder = config("const.CarryInOutputFolder");
            if (!$folder) {
                $ErrMsg = GetMessage::getMessageByID("error025");
            } else {
                // ・搬入予定テーブルから出力日時がNULLのデータを検索し、以下を実施
                $data = t_carry_ins::select("RECVID", "SCTNCD", "DATNND", "RCPDAY", "RCPTIM", "SRKBCD", "SRKBNM", "CUSTCD", "CUSTNM", "PCARNO", "PDCPCD", "PDCPNM", "BRNDCD", "BRNDNM")
                    ->selectRaw("DATE_FORMAT(FUPTIM, '%Y%m%d%H%i%s') AS FUPTIM")
                    ->selectRaw("DATE_FORMAT(NOW(), '%Y/%m/%d %H:%i:%s') AS output_at")
                    ->whereNotNull("RECVID")
                    ->whereNull("output_at")
                    ->get()->toArray();
                if ($data) {
                    // タイトル行
                    $csvkey = [
                        "SCTNCD", "DATNND", "RCPLNO", "RPDTNO", "RCPDAY", "RCPTIM", "SRKBCD", "SRKBNM", "TRNSKB",
                        "CUSTCD", "CUSTNM", "RCARNO", "UIDXNO", "PDCPCD", "PDCPNM", "BRNDCD", "BRNDNM", "CATECD",
                        "CATENM", "RCPVAL", "RPUTCD", "RPUTNM", "ZNSCKB", "DESDKB", "DDTNND", "DSLPNO", "COMPKB",
                        "DELTKB", "BIKONM", "FUSRCD", "FUPTIM", "LUSRCD", "LUPTIM", "RECVID"
                    ];
                    $filename = "PLDTRCVPLDAT_" . \Carbon\Carbon::now()->format("Ymd_His") . ".csv";
                    $lastchar = substr($folder, -1) == "/" ? "" : "/";
                    $file = $folder . $lastchar . $filename;
                    // 　－取得したデータからCSVファイルを作成
                    $this->_exportCSV($file, $csvkey, $data);

                    // 　－取得したデータの受付IDに合致した搬入予定テーブルの「出力日時」に現在日時をセット
                    DB::table($this->t_carry_ins)->upsert($data, ["RECVID"], ["output_at"]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $ErrMsg = $e->getMessage();
        }
        if ($this->ErrRECVID) {
            $ErrMsg = GetMessage::getMessageByID("error023");
        }
        if ($ErrMsg) {
            // ※エラー発生時は管理者にメールを送信（（４）エラーメール送信を参照）
            // メール送信設定
            $mailsetting = array(
                "to" => config("const.Mail.MAIL_ADMIN"),
                "from" => config("const.Mail.MAIL_FROM_ADDRESS"),
                "fromname" => config("const.Mail.MAIL_FROM_NAME"),
            );
            // メール送信データ
            $data = [
                "RECVID" => implode(",", $this->ErrRECVID),
                "err_msg" => $ErrMsg
            ];
            // メールテンプレートのデータ
            $dataMail = [
                'data' => $data,
                "mailsetting" => $mailsetting,
            ];

            // メール送信
            Mail::send(new \App\Mail\AppMailCarryError($dataMail));
        }
    }

    /**
     * 取得したデータからCSVファイルを作成
     * param $file ファイル名
     * param $csvkey タイトル行
     * param $data 取得したデータ
     * @return true
     */
    private function _exportCSV($file, $csvkey, $data)
    {
        $fp = fopen($file, 'w');
        fputs($fp, (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($fp, $csvkey, config("const.CSV.delimiter"), config("const.CSV.enclosure"), config("const.CSV.escape"));
        foreach ($data as $d) {
            $row = [];
            foreach ($csvkey as $v) {
                if ($v == "RCARNO") {
                    $v = "PCARNO";
                }
                $row[] =  isset($d[$v]) ? $d[$v] : NULL;
            }
            fputcsv($fp, $row, config("const.CSV.delimiter"), config("const.CSV.enclosure"), config("const.CSV.escape"));
        }
        fclose($fp);
    }

    /**
     * 依頼内容データから搬入予定テーブルに保存する
     * @return エラーメッセージ
     */
    private function _CarryUpdate()
    {
        $data = RequestsDetailAsRD::select(
            "request_detail_id as RECVID",
            "R.CUSTCD",
            "E.enum_value as SRKBNM",
            "RD.material_division as SRKBCD",
            "mmst.CUSTNM",
            "RD.PCARNO",
            "RD.PDCPCD",
            "mmst2.CUSTNM as PDCPNM",
            "RD.BRNDCD",
            "fmst.BRNDNM",
        )
            ->selectRaw("'0101' as SCTNCD")
            ->selectRaw("IF(MONTH(RD.recovered_on) >= 4,YEAR(RD.recovered_on),YEAR(RD.recovered_on)-1) as DATNND")
            ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y%m%d') AS RCPDAY")
            ->selectRaw("DATE_FORMAT(RD.created_at, '%Y/%m/%d %H:%i:%s') AS FUPTIM")
            ->selectRaw("'0' as RCPTIM")
            ->selectRaw("NULL as output_at")
            ->join($this->t_requests, "R.request_id", "RD.request_id")
            ->leftJoin($this->mmst, "R.CUSTCD", "mmst.CUSTCD")
            ->leftJoin($this->mmst2, "RD.PDCPCD", "mmst2.CUSTCD")
            ->leftJoin($this->fmst, "RD.BRNDCD", "fmst.BRNDCD")
            ->leftJoin($this->enums, function ($join) {
                $join->whereRaw("E.enum_id = 'material_division'");
                $join->on("E.enum_key", "RD.material_division");
            });
        // ・依頼内容データから状態が“確定”（※）のデータを検索する
        if (config("const.CarryInOutputStatus")) {
            $data = $data->whereIn("RD.status_id", $this->_getCarryInOutputStatus());
        }
        $data = $data
            //->whereNotNull(["RD.recovered_on", "RD.PCARNO", "RD.PDCPCD", "RD.BRNDCD", "RD.created_at"])
            ->get()->toArray();
        if ($data) {
            foreach ($data as $d) {
                $dwhere = $d;
                unset($dwhere["output_at"]);
                if (in_array(null, $dwhere)) {
                    // エラーが発生した依頼内容データの「依頼内容ID」
                    $this->ErrRECVID[] = $d["RECVID"];
                } else {
                    if (DB::table($this->t_carry_ins)->where("RECVID", $d["RECVID"])->count()) {
                        // 更新
                        // 依頼内容IDに合致する受付IDのデータが搬入予定テーブルに存在し、かつ部門コード～登録日時のいずれかで値が異なっていた場合、データを更新
                        if (!DB::table($this->t_carry_ins)->where($dwhere)->count()) {
                            unset($d["RECVID"]);
                            DB::table($this->t_carry_ins)->where("RECVID", $dwhere["RECVID"])->update($d);
                        }
                    } else {
                        // 新規
                        DB::table($this->t_carry_ins)->insert($d);
                    }
                }
            }
        }
    }

    /**
     *　状態の取得
     * @return 状態配列
     */
    private function _getCarryInOutputStatus()
    {
        $data = [];
        foreach (config("const.CarryInOutputStatus") as $s) {
            $data[] = config("const.RDStatus." . $s);
        }
        return $data;
    }

    public function Final()
    {
        $ErrMsg = null;
        $ErrLine = null;
        $ErrFile = null;
        $path = config("const.FinalStatusInputFolder");
        $path_backup = config("const.FinalStatusBackupFolder");
        // フォルダ確認
        if (!file_exists($path) || !is_readable($path)) {
            $ErrMsg =  str_replace("{p}",  $path, GetMessage::getMessageByID("error025"));
        } else {
            $filesInFolder =  collect(\File::allFiles($path))
                ->filter(function ($file) {
                    return in_array($file->getExtension(), ['csv']);
                })
                // 　※ファイル名を昇順で取得（古い日時のファイルから取得）
                ->sortBy(function ($file) {
                    return $file->getCTime();
                })
                ->map(function ($file) {
                    return $file->getBaseName();
                })
                ->toArray();
            if ($filesInFolder) {
                // タイトル行確認
                $csvkey = ["DATNND", "RCPLNO", "RPDTNO", "RCPDAY", "RCPTIM", "MTRVAL", "BIKONM", "FUSRCD", "FUPTIM", "LUSRCD", "LUPTIM", "RECVID"];
                // 　※ファイル名を昇順で取得（古い日時のファイルから取得）
                $filename = explode("_", str_replace(".csv", "", $filesInFolder[0]));
                $ErrFile = $filesInFolder[0];
                if ($filename && $filename[0] == "PLDTRCVEDDAT") {
                    try {
                        DB::beginTransaction();
                        $read = new \App\Http\Controllers\Admin\DataController();
                        $data = $read->csvToArray($path . "/" . $filesInFolder[0]);
                        if ($data && !$data["messenger"] && $data["data"]) {
                            $compare = array_intersect($csvkey, array_flip($data["header"]));
                            if (count($csvkey) == count($data["header"]) && count($csvkey) == count($compare)) {
                                // －CSVファイルの２行目以降からデータを取得して依頼内容テーブルを更新
                                foreach ($data["data"] as $line => $d) {
                                    $dataupdate = [
                                        "status_id" => config("const.RDStatus.COMP"),
                                        "acceptanced_at" => \Carbon\Carbon::parse($d["RCPDAY"] . " " . $d["RCPTIM"])->format("Y-m-d H:i:s"),
                                        "acceptanced_value" => isset($d["MTRVAL"]) ? $d["MTRVAL"] : 0,
                                        "acceptanced_unit" => "kg",
                                    ];
                                    if ($d["RECVID"]) {
                                        // 受付IDの空欄外
                                        DB::table($this->t_request_details)->where("request_detail_id", $d["RECVID"])->update($dataupdate);
                                    } else {
                                        $ErrLine = $line;
                                        $ErrMsg = GetMessage::getMessageByID("error027");
                                    }
                                }
                                if(!$ErrMsg){
                                    if (!file_exists($path_backup) || !is_readable($path_backup)) {
                                        // 存在しない場合は作成する
                                        mkdir($path_backup);
                                    }
                                    // －CSVファイルをバックアップフォルダに移動
                                    \File::move($path . "/" . $filesInFolder[0],$path_backup . "/" . $filesInFolder[0]);
                                }
                            } else {
                                // －取得したIDに取込対象のIDが存在しない場合エラーとして処理を終了
                                $ErrMsg = GetMessage::getMessageByID("error024");
                            }
                        }
                        if ($data && $data["messenger"]) {
                            $ErrMsg = $data["messenger"];
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $ErrMsg = $e->getMessage();
                    }
                } else {
                    $ErrMsg = GetMessage::getMessageByID("error026");
                }
            }
        }

        if ($ErrMsg) {
            // ※エラー発生時は管理者にメールを送信（（４）エラーメール送信を参照）
            // メール送信設定
            $mailsetting = array(
                "to" => config("const.Mail.MAIL_ADMIN"),
                "from" => config("const.Mail.MAIL_FROM_ADDRESS"),
                "fromname" => config("const.Mail.MAIL_FROM_NAME"),
            );
            // メール送信データ
            $data = [
                "read_file" => $ErrFile,
                "line_no" => $ErrLine,
                "err_msg" => $ErrMsg
            ];
            // メールテンプレートのデータ
            $dataMail = [
                'data' => $data,
                "mailsetting" => $mailsetting,
            ];

            // メール送信
            Mail::send(new \App\Mail\AppMailCarryFinalError($dataMail));
        }
    }
}
