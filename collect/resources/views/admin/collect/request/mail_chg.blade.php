{{ $data['CUSTNM'] }}
{{ $data['manager_name'] }}　様

※本メールは引取管理システムからの自動送信メールです。

お世話になっております。
以下の依頼申込に対して、回収日を変更しましたので、お知らせいたします。
回収日に問題がある場合は弊社連絡先にご連絡下さい。

●依頼No：{{ $data['request_no'] }}

●申込日：{{ $data['applied_at'] }}

●依頼種別：{{ $data['request_type_name'] }}

●依頼内容：
@foreach($dataRD as $key => $rd)
銘柄：{{ $rd->BRNDNM }}
希望日：{{ $rd->hoped_on }}
回収日：{{ $rd->recovered_on }}
連絡事項：{!!  $rd->information !!}

@endforeach
●依頼備考：{!! $data['request_note'] !!}

●ご担当者名：{{ $data['manager_name'] }}

@if( $data['contact_type'] == "0")
●ご連絡方法：電話でご連絡
　電話番号：{{ $data['tel_no'] }}
@else
●ご連絡方法：メールでご連絡
　メールアドレス：{{ $data['mail_address'] }}
　ＣＣメールアドレス：{{ $data['ccmail_address'] }}
@endif

*****************************************************************
  お申込みのキャンセル、お申込み内容の変更などがある場合は、
  下記までご連絡下さい。

  ガラスリソーシング株式会社
  電話　：{{ $company['COMPANY_PHONE'] }}
  メール：{{ $company['COMPANY_MAIL'] }}
  担当　：{{ $company['COMPANY_MANAGER'] }}
*****************************************************************

