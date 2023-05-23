<?php

namespace App\Http\Controllers\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon;
use App\GetMessage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserAsU;
use App\Models\LoginLog;
use Auth;
use DB;

class UsersController extends Controller
{

    public $_timetoday;
    public $_mysystemname = "request";
    public $_loginlogtb = "t_login_logs";
    public $_sessionurl = 'login_afterurl';
    public function __construct()
    {
        $this->_timetoday = Carbon\Carbon::now()->format("Y/m/d H:i:s");
    }
    // ログイン
    public function login(Request $request)
    {
        //ログインした時ホームページに展開する
        if (Auth::check()) {
            return redirect()->route("r.history");
        }

        //ボタンクリック時以外で依頼申込画面のURLで展開した場合、展開元URLを保存
        if (!$request->has('BtnLogin')) {
            $cur_url = url()->current();    //現在のURL
            $url = url()->previous();       //直前のURL
            if (strpos($cur_url, '/request/login') && strpos($url, '/request/detail/')){
                $request->session()->put($this->_sessionurl , $url);
            }
        }

        //「ログイン」ボタンをクリックする時
        if ($request->has('BtnLogin')) {

            //エラーフォーマット
            $rulus = [
                'UserID' => 'required',
                'PassWord' => 'required',
            ];

            //エラーメッセージの取得
            $message = [
                'UserID.required' => "ユーザーIDを入力してください。",
                'PassWord.required' => "パスワードを入力してください。",
            ];

            // return $message;
            if ($request->has('BtnLogin')) {
                $validator = Validator::make($request->all(), $rulus, $message);


                //エラー検知
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator);
                } else {
                    $UserID = $request->UserID;
                    $flag = true;
                    //ユーザー確認
                    // －アカウントロックエラー：以下条件の全てに合致したデータがログインログテーブルに３件以上存在する	
                    // ・作成機能：“'request”
                    // ・ユーザID：入力したユーザIDと合致
                    // ・ログインステータス：“0”(ログイン失敗)
                    // ・カウント対象外：“0”(カウント対象)
                    $countLog = LoginLog::where("created_system", $this->_mysystemname)
                        ->where("user_id", $UserID)
                        ->where("is_status", 0)
                        ->where("is_nocount", 0)->count();
                    if ($countLog > 2) {
                        $flag = false;
                        $ErrorCode = "ACCOUNT LOCK";
                    }

                    // －ユーザIDエラー：入力したユーザIDと合致するユーザIDがユーザマスタに存在しない	
                    // ＜検索条件＞
                    // ・ユーザマスタ.ユーザID：入力したユーザIDと合致
                    // ・ユーザマスタ.ユーザ種別：“1”（取引先担当者）
                    // ・取引先マスタ.取引先コード：ユーザマスタ.取引先コード
                    if ($flag) {
                        $count = UserAsU::join("pldtcustmmst", function ($join) {
                            $join->on("pldtcustmmst.CUSTCD", "u.CUSTCD");
                        })
                            ->where("u.user_id", $UserID)
                            ->where("u.user_type", 1)
                            ->count();
                        if (!$count) {
                            $ErrorCode = "UserID ERR";
                            $flag = false;
                        }
                    }
                    // 　－パスワードエラー：入力したユーザIDのユーザマスタデータのパスワードと入力したパスワードの値が合致しない
                    if ($flag) {
                        $password = User::select("password")
                            ->where("user_id", $UserID)
                            ->value("password");
                        if ($password != $request->PassWord) {
                            $ErrorCode = "Password ERR";
                            $flag = false;
                        }
                    }

                    // 　－削除済みエラー：入力したユーザIDのユーザマスタデータが削除済み（is_deletedが0以外）
                    // 　　またはユーザマスタに紐づいた取引先マスタ、取引先担当者マスタのデータが期限切れ
                    if ($flag) {
                        $yyyymmdd = Carbon\Carbon::now()->format("Ymd");
                        $count = UserAsU::leftjoin("pldtcustmmst", function ($join) {
                            $join->on("pldtcustmmst.CUSTCD", "u.CUSTCD");
                        })
                            ->leftjoin("pldtcusttmst", function ($join) {
                            $join->on("pldtcusttmst.CUSTCD", "u.CUSTCD");
                            $join->on("pldtcusttmst.TCSTNO", "u.TCSTNO");
                        })
                            ->where("u.user_id", $UserID)
                            ->where("u.is_deleted", 0)
                            ->whereRaw("IFNULL(`pldtcustmmst`.`ENDDAY`, '99999999') >= ?", [$yyyymmdd])
                            ->whereRaw("IFNULL(`pldtcusttmst`.`ENDDAY`, '99999999') >= ?",  [$yyyymmdd])
                            ->count();
                        if (!$count) {
                            $ErrorCode = "UserID DELETE";
                            $flag = false;
                        }
                    }

                    if ($flag) {
                        $user = User::select("*")
                            ->where("user_id", $UserID)
                            ->where("password", $request->PassWord)
                            ->where("is_deleted", 0)
                            ->get()->first();
                        //  ・ログインユーザチェックでエラーが存在しなかった場合、以下を実施	
                        // 　－ログイン認証をセット	

                        Auth::login($user);

                        // 　－ログインログテーブルの以下条件に合致したデータの「カウント対象外」に“1”（カウント対象外）をセット	
                        // 	・作成機能：“'request”
                        // 	・ユーザID：入力したユーザIDと合致
                        // 	・カウント対象外：“0”（カウント対象）
                        DB::table($this->_loginlogtb)->where("created_system", $this->_mysystemname)
                            ->where("user_id", $UserID)
                            ->where("is_nocount", 0)
                            ->update(["is_nocount" => 1]);

                        // 　－ログインログテーブルに以下データを追加	
                        // 	・ユーザID：入力したユーザIDをセット
                        // 	・ログインステータス：“1”（ログイン成功）をセット
                        // 	・エラー種別：空欄
                        // 	・カウント対象外：“1”（カウント対象外）をセット
                        // 	・作成機能：“'request”をセット
                        // 	・作成日時：現在日時をセット
                        $data = array(
                            "user_id" => $UserID,
                            "is_status" => 1,
                            "err_type" => "",
                            "is_nocount" => 1,
                            "created_system" => $this->_mysystemname,
                            "created_at" => $this->_timetoday
                        );
                        LoginLog::insert($data);


                        if ($request->session()->has($this->_sessionurl)) {
                            //展開元画面に展開する
                            $url =  $request->session()->get($this->_sessionurl);
                            $request->session()->forget($this->_sessionurl);
                            return redirect($url);
                        } else {
                            //一覧画面に展開する
                            return redirect()->route("r.history");
                        }
                    } else {
                        // ・ログインユーザチェックでエラーが存在した場合、以下を実施		
                        // 　－ログインログテーブルに以下データを追加		
                        // 	・ユーザID：入力したユーザIDをセット	
                        // 	・ログインステータス：“0”（ログイン失敗）をセット	
                        // 	・エラー種別：以下のいずれかをセット	
                        // 	　アカウントロックエラーの場合：ACCOUNT LOCK／ユーザIDエラーの場合：UserID ERR／パスワードエラーの場合：Password ERR／削除済みエラーの場合：UserID DELETE	
                        // 	・カウント対象外：“0”（カウント対象）をセット	
                        // 	・作成機能：“'request”をセット	
                        // 	・作成日時：現在日時をセット	
                        $data = array(
                            "user_id" => $UserID,
                            "is_status" => 0,
                            "err_type" => $ErrorCode,
                            "is_nocount" => 0,
                            "created_system" => $this->_mysystemname,
                            "created_at" => $this->_timetoday
                        );
                        LoginLog::insert($data);


                        //   －以下エラーメッセージを表示して処理を中止する	
                        $ErrMsg = GetMessage::getMessageByID("error003");
                        return redirect()->back()->withInput()->withErrors(["ErrMsg" =>  $ErrMsg]);
                    }
                }
            }
        }
        //お知らせ
        $datasend = array();
        return view("request.users.login")->with($datasend);
    }


    //ログアウト
    public function logout()
    {
        Auth::logout();
        //ログイン画面に展開する
        return redirect()->route("r.home");
    }
}
