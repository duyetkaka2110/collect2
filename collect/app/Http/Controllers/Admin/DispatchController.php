<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InsertUpdateController;
use Illuminate\Http\Request;
use Carbon;
use App\GetMessage;
use App\Models\pldtpddrvmst;
use App\Models\Status;
use App\Models\t_dispatches;
use App\Models\t_dispatchesAsD;
use App\Models\t_dispatch_details;
use App\Models\t_dispatch_detailsAsDD;
use App\Models\RequestsDetail;
use DB;
use Session;
use App\Http\Controllers\Admin\SearchController;

class DispatchController extends Controller
{
    public $_mysystemname = "admin";
    protected $rmst = "pldtpdcarmst as rmst"; //車両マスタ 
    protected $t_dispatches = "t_dispatches As D"; //手配詳細
    protected $t_dispatch_details = "t_dispatch_details As DD"; //手配詳細
    protected $m_statuses = "m_statuses As S"; //状態マスタ
    protected $t_request_details = "t_request_details As RD"; //依頼内容

    public function __construct()
    {
        Carbon\Carbon::setLocale('ja');
    }

    /**
     * 引取管理／手配カレンダ
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function calender(Request $rq)
    {
        $success = null;
        $now =  \Carbon\Carbon::now();
        // 初期表示時に表示範囲（To）にセットされる値を定数ファイルの「デフォルト表示期間」から取得する
        if (!$rq->datestart) {
            $datestart =  $now;
            $rq->datestart = $now->format("Y/m/d");
            $dateend = $this->_getDayEnd($datestart, "CalendarLimit");
            $rq->dateend = $dateend->format("Y/m/d");
        }
        $datestart = \Carbon\Carbon::parse($rq->datestart);
        $dateend = \Carbon\Carbon::parse($rq->dateend);
        //・表示範囲に「表示可能な最大期間」より大きな期間が指定された場合、表示範囲（To）に「表示可能な最大期間」の日付を セットし、検索を行う。検索実施後はフラッシュメッセージで「表示範囲（To）を表示可能な期間に変更しました。」と 表示するように変更して下さい。（追加仕様）
        // 　※「表示可能な最大期間」には期間と単位（日、週、月のいずれかを指定） 
        //   表示範囲（To）は表示範囲（From）に「表示可能な最大期間」を加算した日付で判定し
        // 、表示範囲（To）の方が未来の場合 表示可能な最大期間」で算出した日付をセット
        $dateendMax = $this->_getDayEnd($datestart, "CalendarMax");
        if ($dateend > $dateendMax) {
            $rq->dateend = $dateendMax->format("Y/m/d");
            // 検索実施後はフラッシュメッセージで「表示範囲（To）を表示可能な期間に変更しました。
            $success = GetMessage::getMessageByID("error011");
        }

        if ($rq->btnSearch) {
            // 他画面から展開した時に展開前の検索条件で手配カレンダ画面を表示する
            Session::put("datestart", $rq->datestart);
            Session::put("dateend", $rq->dateend);
        }

        if (Session::has("datestart")) {
            $rq->datestart = Session::get("datestart");
            $rq->dateend = Session::get("dateend");
        }
        // カレンダー一覧：横軸（車両）
        $resources = $this->_getResource($rq);

        // 未確定の手配データを取得
        $notcomfirm = $this->_getListDayNotConfirm($rq);

        // 手配詳細に紐づいた依頼内容に状態が完了のデータが存在する手配データを取得
        $DayDone = $this->_getListDayDone($rq);

        // カレンダー一覧：手配詳細
        $listevent = $this->_getListEvent($rq);

        // 状態マスタ一覧
        $status = Status::where("is_deleted", 0)->orderBy("sort_no")->get();
        // 日一覧
        $listdays = $this->_getDate(str_replace("/", "-", $rq->datestart), str_replace("/", "-", $rq->dateend));
        // 表示範囲
        $datestart  = $rq->datestart;
        $dateend  = $rq->dateend;

        $listholidays = $this->_getHolidays($datestart, $dateend);
        $th = $this->_renderResource($resources);
        $html = $this->_renderHtml($listdays, $resources, $notcomfirm, $this->_getDayAndResFromEvents($listevent, $listdays, $resources), $DayDone, $listholidays);
        $titleheader = "手配カレンダ";
        return view("admin.collect.dispatch.calender", compact("status", "html", "th",  "datestart", "dateend", "success", "resources","titleheader"));
    }

    /**
     * 日本のお休み日を取得
     * @param  $datestart,$dateend
     * @return array 休み日一覧
     */
    private function _getHolidays($datestart, $dateend)
    {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/vi.japanese%23holiday%40group.v.calendar.google.com/events?key=AIzaSyDNg5-iEZkvfQVjSUitDXz4k68lCRZ5nao&timeMin=' . str_replace("/", "-", $datestart) . 'T13%3A00%3A00%2B09%3A00&timeMax=' . str_replace("/", "-", $dateend) . 'T23%3A59%3A59%2B09%3A00&singleEvents=false&maxResults=9999';
        $obj = json_decode(file_get_contents($url), true);
        $listholidays = [];
        if ($obj && isset($obj["items"])) {
            foreach ($obj["items"] as $it) {
                $listholidays[$it["start"]["date"]] = $it["start"]["date"];
            }
        }
        return $listholidays;
    }

    /**
     * カレンダー一覧：横軸　（車両）を取得
     * @param  $resources　
     * @return テキスト
     */
    private function _renderResource($resources)
    {
        $html = '<th class="col1 ">日付</th>';
        $residkey = [];
        foreach ($resources as $key => $value) {
            $residkey[$value["id"]] = 1;
            $html .= '<th class="th  w-17em ' . $value["id"] . '">' . $value["title"] . '</th>';
        };
        return $html;
    }

    /**
     * 日一覧を取得
     * @param  $resources　
     * @return array 日一覧
     */
    private function _getDate($start, $end)
    {
        $period = Carbon\CarbonPeriod::create($start, $end)->toArray();
        $dates = [];
        foreach ($period as $date) {
            $dates[] = [$date->isoFormat('M/D (ddd)'), $date->isoFormat('dd'), $date->format('Y-m-d')];
        }
        return $dates;
    }

    /**
     * 設定から表示範囲（To）取得
     * @param $date  表示範囲（From）
     * @param string $constkey 
     * @return date
     */
    private function _getDayEnd($date, string $constkey)
    {
        $unitcheck = config("const.$constkey.unit");
        $lengthcheck = config("const.$constkey.length");
        if ($unitcheck == "月") {
            $date = $date->addMonthsNoOverflow($lengthcheck);
        }
        if ($unitcheck == "週") {
            $date = $date->addWeek($lengthcheck);
        }
        if ($unitcheck == "日") {
            $date = $date->addDay($lengthcheck);
        }
        return $date;
    }

    /**
     * カレンダー一覧：セルを取得
     * @param  $listdays, $resources, $notcomfirm, $eventsdayResOneday, $dayDone,$listholidays
     * @return　テキスト
     */
    private function _renderHtml($listdays, $resources, $notcomfirm, $eventsdayResOneday, $dayDone, $listholidays)
    {
        // ログインユーザーの権限種別
        $authority_type = UsersController::authority_type();
        $html = "";
        foreach ($listdays as $key => $day) {
            $holiday = "";
            if ($day[1] === '土' || $day[1] === '日') {
                if ($day[1] === '土') $day[1] = 'Sa';
                if ($day[1] === '日') $day[1] = 'Su';
                $holiday = "tr-holiday-" . $day[1];
            }
            if (array_key_exists($day[2], $listholidays)) {
                $holiday = "tr-holiday-Su";
            }
            $html .= '<tr class="' . $holiday . '">';
            $html .= '    <th class="headcol td-day text-nowrap text-center td-day-' . $day[2] . '">' . $day[0] . '</th>';

            // render all column in the calendar
            foreach ($resources as $k => $res) {
                $keycheck = $day[2] . "-" . $res["id"];
                $keycheck2 = $res["id"] . "-" . $day[2];
                $td_background = "";
                $td_dispatch_id = "";
                $td_sales = "<div class='sales-all' data-id= '" . $keycheck2 . "'></div>";
                $td_done = " no-drag ";
                // ・ログインユーザに“受付担当”または“配車担当”の権限（管理権限マスタから取得）が存在しない場合、処理を中止する
                if (array_key_exists(1, $authority_type) || array_key_exists(2, $authority_type)) {
                    $td_done = "connectedSortable";
                }
                $td_rightclick = "";
                if (array_key_exists($keycheck, $notcomfirm)) {
                    $td_background = $eventsdayResOneday[$keycheck][0]["td_background"];
                    $td_sales = "<div class='sales-all sales' data-id= '" . $keycheck2 . "' ></div>";
                }
                if (array_key_exists($keycheck, $dayDone)) {
                    $td_done = " no-drag ";
                } else {
                    if (array_key_exists($keycheck, $eventsdayResOneday)) {
                        // 　・ログインユーザに“受付担当”または“配車担当”の権限（管理権限マスタから取得）が存在しない
                        if (array_key_exists(1, $authority_type) || array_key_exists(2, $authority_type)) {
                            $td_rightclick = " td_rightclick ";
                        }
                    }
                }
                $html .= '<td class="' . $td_done . $td_rightclick . ' table-drag ' . $td_background . '"  data-day="' . $day[2] . '" data-resource="' . $res["id"] . '">';
                if (array_key_exists($keycheck, $eventsdayResOneday)) {
                    // render events of $day and resource
                    foreach ($eventsdayResOneday[$keycheck] as $ke => $e) {
                        $html .= '<p request_detail_id="' . $e["request_detail_id"] . '" data-id= "' . $keycheck2 .
                            ' " style="background: ' . $e["background_color"] .
                            '; color: ' . $e["font_color"] . ';" data-event="' . $e["eventid"] . '" data-url="' . $e["url"] .
                            '" data-sort="' . $e["sort"] . '" data-status="' . $e["status_id"] . '"  class=" noselect ' . $e["td_background"] . ' event-' . $e["id"] . ' event ' .
                            $e["dragclass"] . '" title="' . $e["title"] . '">' . $e["title"] . '</p> ';
                    };
                }
                $html .= '<p  class="p-hide" style="margin:0"></p> ';
                $html .= $td_sales;
                $html .= '</td>';
            };
            $html .= '</tr>';
        };
        return $html;
    }

    /**
     * カレンダー一覧：get key events day and events resource id for render
     * @param  $events, $listdays, $resources
     * @return　array $eventsDayResOneDay
     */
    private function _getDayAndResFromEvents($events, $listdays, $resources)
    {
        $eventsDayResOneDay = []; // all events of one day
        $residkey = [];
        foreach ($resources as $r) {
            $residkey[$r["id"]] = $r;
        }
        $dayskey = [];
        foreach ($listdays as $r) {
            $dayskey[$r[2]] = $r;
        }
        foreach ($events as $key => $value) {

            $eventsDay[$value["day"]] = 1;
            $eventsResID[$value["resourceID"]] = 1;
            //group all events of day and resource into one array
            if (array_key_exists($value["day"], $dayskey) && array_key_exists($value["resourceID"], $residkey)) {
                // append
                $keycheck = $value["day"] . '-' . $value["resourceID"];
                if (array_key_exists($keycheck, $eventsDayResOneDay)) {
                    // append
                    $eventsDayResOneDay[$keycheck][] = $value;
                } else {
                    // new
                    $eventsDayResOneDay[$keycheck] = [];
                    $eventsDayResOneDay[$keycheck][0] = $value;
                }
            }
        };
        return $eventsDayResOneDay;
    }

    /**
     * 手配編集画面
     * @param string $id 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」
     * @return \Illuminate\View\View
     */
    public function edit(Request $rq)
    {
        // ログインユーザーの権限種別
        $authority_type = UsersController::authority_type();
        $ErrMsg = "";
        $idrq = $rq->id;
        $id = $this->_getDayAndResource($idrq);
        $dispatch = $this->_getDispatches($id);
        $RDdone = $this->_checkRDDone($id) ? "form-disabled" : '';
        $dispatch_detail = $this->_getDispatchesDetail($id);
        if ($rq->btnOk) {
            if ($RDdone) {
                $ErrMsg = "表示されているデータに状態が完了のものが存在します。";
            } else {
                try {
                    DB::beginTransaction();
                    $datainsert = [];
                    $is_confirmed = $rq->is_confirmed ? $rq->is_confirmed : 0;
                    if ($dispatch->dispatch_id) {
                        // 手配データが既に存在する場合、手配データを更新 
                        $data = array(
                            "is_confirmed" => $is_confirmed
                        );
                        t_dispatches::where("dispatch_id", $dispatch->dispatch_id)
                            ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
                        $editid = [];
                        if ($rq->instruct) {
                            foreach ($rq->instruct as $k => $in) {
                                if ($in) {
                                    $tmp = array(
                                        "dispatch_id" => $dispatch->dispatch_id,
                                        "sort_no" => $rq->sort_no[$k],
                                        "instruct" => $in
                                    );
                                    if ($rq->dispatch_detail_id[$k]) {
                                        // 画面から取得した手配詳細IDが0以外の場合、手配詳細データを更新
                                        $editid[] = $rq->dispatch_detail_id[$k];
                                        t_dispatch_details::where("dispatch_detail_id", $rq->dispatch_detail_id[$k])
                                            ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $tmp));
                                    } else {
                                        // 画面から取得した手配詳細IDが0の場合、新規に手配詳細データを作成
                                        $datainsert[] = array_merge(InsertUpdateController::datainsert($this->_mysystemname), $tmp);
                                    }
                                }
                            }
                        }
                        // 「手配ID」に合致する手配詳細データの「手配詳細ID」が画面上に存在しない場合、手配詳細データを削除
                        t_dispatch_details::where("dispatch_id", $dispatch->dispatch_id)->whereNotIn('dispatch_detail_id', $editid)->delete();
                    } else {
                        // 手配データが存在しない場合、新規に手配データを作成
                        $dispatch_id = $this->_insertDispatches($id, $id[4], $is_confirmed);
                        // 新規に手配詳細データを作成
                        foreach ($rq->instruct as $k => $in) {
                            if ($in) {
                                $tmp = array(
                                    "dispatch_id" => $dispatch_id,
                                    "sort_no" => $rq->sort_no[$k],
                                    "instruct" => $in
                                );
                                $datainsert[] = array_merge(InsertUpdateController::datainsert($this->_mysystemname), $tmp);
                            }
                        }
                    }
                    // 手配詳細データ新規に作成
                    if ($datainsert) {
                        t_dispatch_details::insert($datainsert);
                    }

                    DB::commit();
                } catch (Throwable $e) {
                    DB::rollBack();
                    $Errmsg = GetMessage::getMessageByID("error009");
                }
            }
            // 手配カレンダ画面に展開
            return redirect()->route("a.dcalender")->withSuccess(str_replace("{p}", "手配", GetMessage::getMessageByID("error010")));
        }
        $title_header = "手配編集";
        return view("admin.collect.dispatch.edit", compact("dispatch", "dispatch_detail", "RDdone", "ErrMsg", "idrq", "authority_type","title_header"));
    }

    /**
     * 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」:取る
     * @param  string $id 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」
     * @return array
     */
    private function _getDayAndResource(string $id)
    {
        // 「手配日」+「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
        $data = explode("-",  $id);
        if ($data && count($data) == 7) {
            // 「手配日」
            $data[4] = $data[4] . "/" . $data[5] . "/" . $data[6];
        }
        return $data;
    }

    /**
     * 手配に紐づいた手配詳細に、依頼内容の状態が“完了”のデータ存在するかチェック
     * @param  array $id 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」
     * @return array
     */
    private function _checkRDDone(array $id)
    {
        return t_dispatchesAsD::join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
            ->join($this->t_request_details, "DD.request_detail_id", "RD.request_detail_id")
            ->where("RD.status_id", config("const.RDStatus.COMP"))
            ->where("D.PCARNO", $id[0])
            ->where("D.CIDXNO", $id[1])
            ->where("D.PDCPCD", $id[2])
            ->where("D.PDRVCD", $id[3])
            ->where("D.dispatched_on", $id[4])
            ->count();
    }

    /**
     * 手配データの取得
     * @param  array $id 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」
     * @return array
     */
    private function _getDispatches(array $id)
    {
        // 車両マスタのキー項目の値が全て“0”
        if (!$id[0] && !$id[1] && !$id[2] && !$id[3]) {
            $data = t_dispatchesAsD::select('D.is_confirmed', 'D.dispatch_id')
                ->selectRaw("'" . $id[4] . "' AS dispatched_on")
                ->selectRaw("'未定' AS title")
                ->where("D.PCARNO", $id[0])
                ->where("D.CIDXNO", $id[1])
                ->where("D.PDCPCD", $id[2])
                ->where("D.PDRVCD", $id[3])
                ->where("D.dispatched_on", $id[4])
                ->get()->first();
            if (!$data) {
                // 未定の手配データが取得できなかった場合、手配データを新規に作成
                $datainsert = [
                    "PCARNO" => 0,
                    "CIDXNO" => 0,
                    "PDCPCD" => 0,
                    "PDRVCD" => 0,
                    "dispatched_on" =>  $id[4],
                    "is_confirmed" => 0
                ];
                t_dispatches::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $datainsert));
                $data = t_dispatchesAsD::select('D.is_confirmed', 'D.dispatch_id')
                    ->selectRaw("'" . $id[4] . "' AS dispatched_on")
                    ->selectRaw("'未定' AS title")
                    ->where("D.PCARNO", $id[0])
                    ->where("D.CIDXNO", $id[1])
                    ->where("D.PDCPCD", $id[2])
                    ->where("D.PDRVCD", $id[3])
                    ->where("D.dispatched_on", $id[4])
                    ->get()->first();
            }
        } else {
            $data =  pldtpddrvmst::select('D.is_confirmed', 'D.dispatch_id')
                ->selectRaw("'" . $id[4] . "' AS dispatched_on")
                ->selectRaw("CONCAT(rmst.PCARNO ,'：' ,IFNULL(vmst.DVSIKJ, ''),'（', IFNULL(vmst.KTELNO, ''),'）') AS title")
                ->join($this->rmst, function ($join) {
                    $join->on("vmst.PDCPCD", "rmst.PDCPCD");
                    $join->on("vmst.PDRVCD", "rmst.PDRVCD");
                })
                ->leftJoin($this->t_dispatches, function ($join) use ($id) {
                    $join->on("D.PCARNO", "rmst.PCARNO");
                    $join->on("D.CIDXNO", "rmst.CIDXNO");
                    $join->on("D.PDCPCD", "rmst.PDCPCD");
                    $join->on("D.PDRVCD", "rmst.PDRVCD");
                    $join->where("D.dispatched_on", $id[4]);
                })
                ->where("rmst.PCARNO", $id[0])
                ->where("rmst.CIDXNO", $id[1])
                ->where("rmst.PDCPCD", $id[2])
                ->where("rmst.PDRVCD", $id[3])
                ->get()->first();
        }
        return $data;
    }

    /**
     * 手配詳細データの取得
     * @param  array $id 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」＋「手配日」
     * @return array
     */
    private function _getDispatchesDetail(array $id)
    {
        return t_dispatchesAsD::select("DD.*")
            ->join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
            ->where("D.PCARNO", $id[0])
            ->where("D.CIDXNO", $id[1])
            ->where("D.PDCPCD", $id[2])
            ->where("D.PDRVCD", $id[3])
            ->where("D.dispatched_on", $id[4])
            ->orderBy("DD.sort_no")
            ->get();
    }

    /**
     * カレンダー一覧：手配詳細 - ドラッグ＆ドロップ
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function update(Request $rq)
    {
        $tdsale = "";
        try {
            DB::beginTransaction();
            // セル変更
            if ($rq->dataupdate) {
                // ドロップ先：「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
                $resource = explode("-", $rq->dataupdate["resourceID"]);

                $status_id = t_dispatch_detailsAsDD::select("RD.status_id")
                    ->join($this->t_request_details, "DD.request_detail_id", "RD.request_detail_id")
                    ->where("DD.dispatch_detail_id", $rq->dataupdate["id"])->value("status_id");
                if ($status_id == config("const.RDStatus.COMP")) {
                    // 手配カレンダを表示した後で、依頼詳細の「状態」が“完了”となった場合
                    return GetMessage::getMessageByID("error017");
                }
                if (!$this->_checkStartEndDay($resource, $rq->dataupdate["day"])) {
                    // ・ドラッグ先の日付が、ドラッグ先の車両の車両マスタと運転手マスタの「有効期間(開始日)」～「有効期間(終了日)」の範囲外の場合、
                    return GetMessage::getMessageByID("error019");
                }

                // ドラッグ元：手配データの「確定フラグ」を 0（未確定）に変更
                $this->_updateDispatches(explode("-", $rq->dataupdate["oldresourceID"]), $rq->dataupdate["oldday"], ["is_confirmed" => 0]);

                // ドロップ先：手配IDを取得
                $dispatch_id = $this->_checkDispatches($resource, $rq->dataupdate["day"]);
                if (!$dispatch_id) {
                    // 手配IDが取得できない場合、新規追加
                    $dispatch_id = $this->_insertDispatches($resource, $rq->dataupdate["day"]);
                    $tdsale = "sales";
                } else {
                    // ドロップ先：「確定フラグ」を 0（未確定）に変更
                    $this->_updateDispatches($resource, $rq->dataupdate["day"], ["is_confirmed" => 0]);

                    //未確定変更に伴い、背景色に黄色をセット
                    $tdsale =  "sales";
                }
                // 手配詳細テーブルのデータ更新
                $data_detail = array(
                    "dispatch_id" => $dispatch_id,
                    "sort_no" => 1
                );
                t_dispatch_details::where("dispatch_detail_id", $rq->dataupdate["id"])->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_detail));

                // 依頼内容データを取得
                $RD = t_dispatch_details::select("RD.request_detail_id", "RD.request_id")
                    ->leftJoin($this->t_request_details, "RD.request_detail_id", "t_dispatch_details.request_detail_id")
                    ->where("t_dispatch_details.dispatch_detail_id", $rq->dataupdate["id"])
                    ->first();

                // 日付を変更した場合、紐づく依頼内容データを更新	
                if (!$this->_checkRecovered($rq->dataupdate["id"], $rq->dataupdate["day"])) {
                    $data_rd = array(
                        "recovered_on" => $rq->dataupdate["day"], // 回収日：ドロップしたセルの「日付」の値をセット	
                        "PCARNO" => $resource[0],
                        "CIDXNO" => $resource[1],
                        "PDRVCD" => $resource[3]
                    );
                    RequestsDetail::where("request_detail_id", $RD["request_detail_id"])->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_rd));
                }

                // ・ドラッグされた手配詳細に紐づいた依頼内容の「状態」が“顧客”または“確定”で異なる日付のセルにドラッグされた場合、メールを送信
                if ($rq->dataupdate["day"] != $rq->dataupdate["oldday"]) {
                    if ($status_id == config("const.RDStatus.CUSTOMER") || $status_id == config("const.RDStatus.CONFIRM")) {
                        // メールを送信
                        $mail = new SearchController();
                        $mail->sendMail($RD["request_id"], $RD["request_detail_id"], "change");
                    }
                }
            }

            $dd_id = null;
            // 表示順変更
            if ($rq->sortupdate) {
                foreach ($rq->sortupdate as $s) {
                    if ($s) {
                        // 手配詳細テーブルのデータ更新
                        $data_detail = array(
                            "sort_no" => $s["sort"],
                        );
                        t_dispatch_details::where("dispatch_detail_id", $s["id"])->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_detail));
                        $dd_id = $s["id"];
                    }
                }
            }
            if ($dd_id && !$rq->dataupdate) {
                //データ変更に伴い、確定フラグを未確定にし背景色に黄色をセット
                t_dispatches::where("dispatch_id", t_dispatch_details::where("dispatch_detail_id", $dd_id)->value("dispatch_id"))
                    ->update(["is_confirmed" => 0]);
                $tdsale =  "sales";
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            return GetMessage::getMessageByID("error009");
        }
        return $tdsale;
    }

    /**
     * カレンダー一覧：手配詳細 -手配移動|手配入替
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function updateMoveReplace(Request $rq)
    {
        $tdsale = "";
        $ErrMsg = $this->validateMoveReplace($rq);
        // データ確認
        if ($ErrMsg) {
            return redirect()->back()->withErrors($ErrMsg);
        }
        try {
            DB::beginTransaction();
            // 移動元／入替元：「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」+ 「手配日」
            $oldresouce = $this->_getDayAndResource($rq->select_data_id);
            // 移動先／入替先「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
            $newresource = explode("-", $rq->new_resource);

            // 移動先／入替先の手配IDを取得
            $dispatch_id_new = $this->_checkDispatches($newresource, $oldresouce[4]);
            if (!$dispatch_id_new) {
                //手配IDが取得できない場合、新規追加
                $dispatch_id_new = $this->_insertDispatches($newresource, $oldresouce[4]);
            }

            // 移動元の手配詳細一覧を取得
            $listdd_old = $this->_getDispatchesDetail($this->_getDayAndResource($rq->select_data_id));

            $totaldd = 0;
            // 手配移動
            if ($rq->btnPopupOk == "move") {
                // 移動先の表示順の最大値を取得
                $totaldd = t_dispatch_details::where("dispatch_id", $dispatch_id_new)->max("sort_no");
                if (!$totaldd) $totaldd = 0;
            }

            // 手配入替
            if ($rq->btnPopupOk == "replace") {
                //入替元の手配IDを取得
                $dispatch_id_old = $this->_checkDispatches($oldresouce, $oldresouce[4]);
                // ・手配詳細ID IN （入替先の手配詳細ID）
                $data_detail = array(
                    "dispatch_id" => $dispatch_id_old,
                );
                t_dispatch_details::where("dispatch_id", $dispatch_id_new)
                    ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_detail));

                // 　－入替元の手配詳細に紐づいた依頼内容データを更新
                $data_rd_detail = array(
                    "PCARNO" => $oldresouce[0],
                    "CIDXNO" => $oldresouce[1],
                    "PDRVCD" => $oldresouce[3]
                );
                // RequestsDetail::join($this->t_dispatch_details, "DD.request_detail_id", "t_request_details.request_detail_id")
                //     ->where("DD.dispatch_id", $dispatch_id_new)
                //     ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_rd_detail));
                RequestsDetail::where("request_detail_id", DB::table($this->t_dispatch_details)->select("request_detail_id")
                    ->where("dispatch_id", $dispatch_id_new)->get()->toArray())
                    ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_rd_detail));
            }
            // 移動元の手配詳細テーブルに表示順,手配ID更新
            if ($listdd_old) {
                foreach ($listdd_old as $s) {
                    if ($s) {
                        // 手配詳細テーブルにデータ更新
                        $data_detail = array(
                            "dispatch_id" => $dispatch_id_new,
                            "sort_no" => $s->sort_no + $totaldd,
                        );
                        t_dispatch_details::where("dispatch_detail_id", $s->dispatch_detail_id)
                            ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_detail));

                        // 　－入替先の手配詳細に紐づいた依頼内容データを更新
                        $data_rd_detail = array(
                            "PCARNO" => $newresource[0],
                            "CIDXNO" => $newresource[1],
                            "PDRVCD" => $newresource[3]
                        );

                        RequestsDetail::where("request_detail_id", t_dispatch_details::where("dispatch_detail_id", $s->dispatch_detail_id)->value("request_detail_id"))
                            ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data_rd_detail));
                    }
                }
            }
            // 移動元の手配データを更新
            $this->_updateDispatches($oldresouce, $oldresouce[4], array_merge(InsertUpdateController::dataupdate($this->_mysystemname), ["is_confirmed" => 0]));
            // 移動先の手配データを更新
            $this->_updateDispatches($newresource, $oldresouce[4], array_merge(InsertUpdateController::dataupdate($this->_mysystemname), ["is_confirmed" => 0]));

            DB::commit();
            return redirect()->route("a.dcalender");
        } catch (Throwable $e) {
            DB::rollBack();
            return GetMessage::getMessageByID("error009");
        }
        return $tdsale;
    }

    /**
     * カレンダー一覧：手配詳細 -手配移動|手配入替: データ確認
     * @param  Request $rq
     * @return エラーメッセージ
     */
    public function validateMoveReplace(Request $rq)
    {
        $ErrMsg = "";
        // 移動先／入替先：未選択エラー
        if ($rq->btnPopupOk == "move") {
            $prefix = "移動";
        } else {
            $prefix = "入替";
        }
        if (!$rq->new_resource) {
            $ErrMsg = str_replace("{p}",  $prefix, GetMessage::getMessageByID("error012"));
        }
        // 移動先／入替先：移動元／入替元と同一値エラー
        if ($rq->new_resource == $rq->select_resource) {
            $ErrMsg = str_replace("{p}",  $prefix, GetMessage::getMessageByID("error013"));
        }
        // 移動元／入替元：完了エラー（依頼内容の状態が完了のデータあり）
        $oldresouce_day = $this->_getDayAndResource($rq->select_data_id);
        if ($this->_checkRDDone($oldresouce_day)) {
            $ErrMsg = str_replace("{p}",  $prefix, GetMessage::getMessageByID("error015"));
        }
        if (!$ErrMsg) {
            // 移動先／入替先：完了エラー（依頼内容の状態が完了のデータあり）
            $newresource = explode("-", $rq->new_resource);
            $newresource[4] =  $oldresouce_day[4];
            if ($this->_checkRDDone($newresource)) {
                $ErrMsg = str_replace("{p}",  $prefix, GetMessage::getMessageByID("error014"));
            }
        }
        return $ErrMsg;
    }

    /**
     * 手配テーブルに新規データを追加
     * @param  array  $resource     「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
     * @param  string $day          「手配日」
     * @param  int    $is_confirmed 「確定フラグ」
     * @return $id 手配ID
     */
    private function _insertDispatches(array $resource, string $day, int $is_confirmed = 0)
    {
        $data = array(
            "PCARNO" => $resource[0], // ・車両番号：ドロップしたセルの「車両番号」をセット	
            "CIDXNO" => $resource[1], // ・内訳番号：ドロップしたセルの「内訳番号」をセット	
            "PDCPCD" => $resource[2], // ・物流会社コード：ドロップしたセルの「取引会社コード」をセット	
            "PDRVCD" => $resource[3], // ・運転手コード：ドロップしたセルの「運転手コード」をセット	
            "dispatched_on" =>  $day, // ・手配日：ドロップしたセルの「日付」の値をセット	
            "is_confirmed" => $is_confirmed, // ・確定フラグ：0（未確定）をセット
        );
        return t_dispatches::insertGetId(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
    }

    /**
     * 手配テーブルのデータを更新
     * @param  array  $resource 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
     * @param  string $day      「手配日」
     * @param  int    $data      更新データ
     * @return $id 手配ID
     */
    private function _updateDispatches(array $resource, string $day, array $data)
    {
        return t_dispatches::where("PCARNO", $resource[0])
            ->where("CIDXNO", $resource[1])
            ->where("PDCPCD", $resource[2])
            ->where("PDRVCD", $resource[3])
            ->whereDate("dispatched_on", $day)
            ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
    }

    /**
     * 依頼内容データの「回収日」がドロップセルの「日付」確認
     * @param  int    $id  手配詳細ID	
     * @param  string $day ドロップセルの「日付」
     * @return int
     */
    private function _checkRecovered(int $id, string $day)
    {
        t_dispatch_detailsAsDD::selectRaw("RD.recovered_on")
            ->join($this->t_request_details, "DD.request_detail_id", "RD.request_detail_id")
            ->whereDate("RD.recovered_on", $day)
            ->where("DD.dispatch_detail_id", $id)
            ->count();
    }

    /**
     * 手配確認：ドラッグ先の車両の車両マスタと運転手マスタの「有効期間(開始日)」～「有効期間(終了日)」の範囲外
     * @param  array  $resource 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
     * @param  string $day      「手配日」
     * @return int
     */
    private function _checkStartEndDay(array $resource, string $day)
    {
        return pldtpddrvmst::join($this->rmst, function ($join) {
            $join->on("rmst.PDCPCD", "vmst.PDCPCD");
            $join->on("rmst.PDRVCD", "vmst.PDRVCD");
        })
            ->where("vmst.PDCPCD", $resource[2])
            ->where("vmst.PDRVCD", $resource[3])
            // ・車両マスタ．有効期間(開始日) <= 表示行の「回収日」（yyyymmdd形式）
            // ・車両マスタ．有効期間(終了日) >= 表示行の「回収日」（yyyymmdd形式）
            // ・運転手マスタ．有効期間(開始日) <= 表示行の「回収日」（yyyymmdd形式）
            // ・運転手マスタ．有効期間(終了日) >= 表示行の「回収日」（yyyymmdd形式）
            ->where("vmst.SRTDAY", "<=", $day)
            ->where("vmst.ENDDAY", ">=", $day)
            ->where("rmst.SRTDAY", "<=", $day)
            ->where("rmst.ENDDAY", ">=", $day)
            ->count();
    }
    /**
     * 手配確認：以下パラメータに合致した手配データを取得
     * @param  array  $resource 「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」
     * @param  string $day      「手配日」
     * @return int
     */
    private function _checkDispatches(array $resource, string $day)
    {
        return t_dispatchesAsD::where("D.PCARNO", $resource[0])
            ->where("D.CIDXNO", $resource[1])
            ->where("D.PDCPCD", $resource[2])
            ->where("D.PDRVCD", $resource[3])
            ->where("D.dispatched_on", $day)
            ->value("dispatch_id");
    }

    /**
     * 引取管理／手配カレンダ：手配詳細
     * @param  Request $rq
     * @return json
     */
    private function _getListEvent(Request $rq)
    {
        //・以下条件の全てに合致するデータを手配＋手配詳細テーブルから取得		
        // 	－手配.手配日：表示範囲の範囲内	
        // 	－手配詳細テーブルの「手配ID」で内部結合	
        // 	※手配.手配日、手配.手配ID、手配詳細.表示順 でソート	
        // ・取得したデータの「手配日」がカレンダー上の該当日付でかつ車両マスタの「車両番号」＋「内訳番号」＋「物流会社コード」＋「運転手コード」と合致するセルに取得したデータの「手配内容」を出力	
        return t_dispatchesAsD::select("D.dispatch_id as id", "DD.sort_no as sort", "DD.instruct as title", "DD.request_detail_id", "DD.dispatch_detail_id as eventid", "S.status_id")
            ->selectRaw("CONCAT(D.PCARNO,'-',D.CIDXNO,'-',D.PDCPCD,'-',D.PDRVCD) as resourceID")
            ->selectRaw("DATE_FORMAT(D.dispatched_on, '%Y-%m-%d') AS day")
            ->selectRaw("COALESCE(S.background_color,'#ff95c8') as background_color")
            ->selectRaw("COALESCE(S.font_color,'#000') as font_color")
            ->selectRaw("IF(RD.status_id = '" . config("const.RDStatus.COMP") . "', 'stop2', '') as dragclass")
            ->selectRaw("IF(D.is_confirmed = 0, 'td-sale', '') as td_background")
            ->selectRaw("'#' AS url")
            ->join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
            ->leftJoin($this->t_request_details, "RD.request_detail_id", "DD.request_detail_id")
            ->leftJoin($this->m_statuses, "RD.status_id", "S.status_id")
            ->whereBetween("D.dispatched_on", [$rq->datestart, $rq->dateend])
            ->orderBy("D.dispatched_on")
            ->orderBy("D.dispatch_id")
            ->orderBy("DD.sort_no")
            ->get()->toArray();
    }

    /**
     * 引取管理／手配カレンダ：横軸（車両）
     * @param  Request $rq
     * @return json
     */
    private function _getResource(Request $rq)
    {
        // ・以下条件に合致するデータを車両マスタ＋運転手マスタから取得		
        // ・取得した車両マスタ＋運転手マスタのデータを以下形式で表示	
        // 「運転手マスタ.連絡先(携帯電話)」+改行＋「車両マスタ.車両番号」＋“：”＋「運転手マスタ.運転手氏名(姓)」
        // －車両マスタ.有効期限（開始日）：表示範囲(To)をyyyymmdd形式にした値と同じか小さい	
        // －車両マスタ.有効期限（終了日）：表示範囲(From)をyyyymmdd形式にした値と同じか大きい	
        // －運転手マスタに車両マスタの「取引会社コード」+「運転手コード」と合致したデータが存在する(内部結合)	
        // －運転手マスタ.有効期限（開始日）：表示範囲(To)をyyyymmdd形式にした値と同じか小さい	
        // －運転手マスタ.有効期限（終了日）：表示範囲(From)をyyyymmdd形式にした値と同じか大きい	
        // ※車両マスタ.表示順位、車両マスタ.車両番号、車両マスタ.内訳番号、運転手マスタ.表示順位 、運転手マスタ.取引会社コード 、運転手マスタ.運転手コードでソート	
        $datestart = \Carbon\Carbon::parse($rq->datestart)->format("Ymd");
        $dateend = \Carbon\Carbon::parse($rq->dateend)->format("Ymd");
        $list = pldtpddrvmst::selectRaw("CONCAT(rmst.PCARNO,'-',rmst.CIDXNO,'-',rmst.PDCPCD,'-',rmst.PDRVCD) as id")
            ->selectRaw("CONCAT(IFNULL(vmst.KTELNO, '') , IF(IFNULL(vmst.KTELNO, '') = '','','<br>') , rmst.PCARNO , '：' , IFNULL(vmst.DVSIKJ, '')) AS title")
            ->selectRaw("CONCAT(rmst.PCARNO , '：' , IFNULL(vmst.DVSIKJ, '')) AS title2")
            ->join($this->rmst, function ($join) {
                $join->on("vmst.PDCPCD", "rmst.PDCPCD");
                $join->on("vmst.PDRVCD", "rmst.PDRVCD");
            })
            ->whereIn("rmst.PDCPCD", config('const.CarWherePDCPCDList'))
            ->where("vmst.SRTDAY", "<=", $dateend)
            ->where("vmst.ENDDAY", ">=", $datestart)
            ->where("rmst.SRTDAY", "<=", $dateend)
            ->where("rmst.ENDDAY", ">=", $datestart)
            ->orderBy("rmst.OUTPOS")
            ->orderByRaw("CONVERT(rmst.PCARNO, SIGNED) ")
            ->orderBy("rmst.CIDXNO")
            ->orderBy("vmst.OUTPOS")
            ->orderBy("vmst.PDCPCD")
            ->orderBy("vmst.PDRVCD")
            ->get()->toArray();

        $undecided = array(
            array(
                "id" => "0-0-0-0",
                "title" => "未定",
                "title2" => "未定"
            )
        );
        return (array_merge($undecided, $list));
    }

    /**
     * 「手配.確定フラグ」が0（未確定）の手配データのキー値を取得
     * @param  Request $rq
     * @return json
     */
    private function _getListDayNotConfirm(Request $rq)
    {
        $newdata = array();
        $data =  t_dispatchesAsD::selectRaw("DATE_FORMAT(D.dispatched_on, '%Y-%m-%d') AS dispatched_on")
            ->selectRaw("CONCAT(D.PCARNO,'-',D.CIDXNO,'-',D.PDCPCD,'-',D.PDRVCD) as resourceID")
            ->join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
            ->where("D.is_confirmed", 0)
            ->whereBetween("D.dispatched_on", [$rq->datestart, $rq->dateend])
            ->groupBy("D.dispatched_on", "D.PCARNO", "D.CIDXNO", "D.PDCPCD", "D.PDRVCD")
            ->get()->toArray();
        if ($data) {
            foreach ($data as $k => $d) {
                $newdata[$d["dispatched_on"] . "-" . $d["resourceID"]] = $d;
            }
        }
        return ($newdata);
    }

    /**
     * 手配詳細に紐づいた「依頼内容.状態」が完了のデータが存在する手配データのキー値を取得
     * @param  Request $rq
     * @return json
     */
    private function _getListDayDone(Request $rq)
    {
        $newdata = array();
        $data =  t_dispatchesAsD::selectRaw("DATE_FORMAT(D.dispatched_on, '%Y-%m-%d') AS dispatched_on")
            ->selectRaw("CONCAT(D.PCARNO,'-',D.CIDXNO,'-',D.PDCPCD,'-',D.PDRVCD) as resourceID")
            ->join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
            ->join($this->t_request_details, "DD.request_detail_id", "RD.request_detail_id")
            ->where("RD.status_id", config("const.RDStatus.COMP"))
            ->whereBetween("D.dispatched_on", [$rq->datestart, $rq->dateend])
            ->groupBy("D.dispatched_on", "D.PCARNO", "D.CIDXNO", "D.PDCPCD", "D.PDRVCD")
            ->get()->toArray();
        if ($data) {
            foreach ($data as $k => $d) {
                $newdata[$d["dispatched_on"] . "-" . $d["resourceID"]] = $d;
            }
        }
        return ($newdata);
    }
}
