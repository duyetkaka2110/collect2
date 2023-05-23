<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>引取依頼書</title>
    <style>
        .style0 {
            text-align: general;
            vertical-align: middle;
            white-space: nowrap;
            color: black;
            font-size: 11.0pt;
            font-weight: 400;
            font-style: normal;
            text-decoration: none;
            border: none;
        }

        .style62 {
            text-align: general;
            vertical-align: top;
            white-space: nowrap;
            color: black;
            font-size: 9.0pt;
            font-weight: 400;
            font-style: normal;
            text-decoration: none;
            border: none;
        }

        .font8 {
            color: windowtext;
            font-size: 6.0pt;
            font-weight: 400;
            font-style: normal;
            text-decoration: none;
        }

        td {
            padding-top: 1px;
            padding-right: 1px;
            padding-left: 1px;
            color: black;
            font-size: 11.0pt;
            font-weight: 400;
            font-style: normal;
            text-decoration: none;
            text-align: general;
            vertical-align: middle;
            border: none;
            white-space: nowrap;
        }

        .xl66 {
            font-size: 18.0pt;
            text-align: center;
            vertical-align: top;
        }

        .xl67 {
            font-size: 9.0pt;
            vertical-align: top;
        }

        .xl68 {
            font-size: 9.0pt;
            text-align: right;
            vertical-align: top;
        }

        .xl69 {
            font-size: 12.0pt;
            vertical-align: top;
        }

        .xl70 {
            font-size: 10.0pt;
            text-align: right;
            vertical-align: top;
        }

        .xl71 {
            font-size: 9.0pt;
            text-align: left;
            vertical-align: top;
        }

        .xl72 {
            font-size: 9.0pt;
            text-align: left;
            vertical-align: top;
            border-top: none;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: none;
        }

        .xl73 {
            font-size: 10.0pt;
            text-align: right;
            vertical-align: top;
            border-top: none;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: none;
        }

        .xl74 {
            font-size: 12.0pt;
            text-align: center;
            border: .5pt solid windowtext;
            white-space: normal;
        }

        .xl75 {
            font-size: 18.0pt;
            text-align: center;
            border: .5pt solid windowtext;
        }

        .xl76 {
            text-align: center;
            border: .5pt solid windowtext;
            white-space: normal;
        }

        .xl77 {
            text-align: left;
            border-top: .5pt solid windowtext;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: .5pt solid windowtext;
            white-space: normal;
        }

        .xl78 {
            font-size: 9.0pt;
            text-align: left;
            border-top: .5pt solid windowtext;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: none;
            white-space: normal;
        }

        .xl79 {
            font-size: 9.0pt;
            text-align: left;
            border-top: .5pt solid windowtext;
            border-right: .5pt solid windowtext;
            border-bottom: .5pt solid windowtext;
            border-left: none;
            white-space: normal;
        }

        .xl80 {
            vertical-align: top;
            border: .5pt solid windowtext;
            white-space: normal;
        }

        .xl81 {
            vertical-align: top;
        }

        .xl82 {
            font-size: 10.0pt;
            vertical-align: top;
        }

        .xl83 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: .5pt solid windowtext;
            border-right: none;
            border-bottom: none;
            border-left: .5pt solid windowtext;
        }

        .xl84 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: .5pt solid windowtext;
            border-right: none;
            border-bottom: none;
            border-left: none;
        }

        .xl85 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: .5pt solid windowtext;
            border-right: .5pt solid windowtext;
            border-bottom: none;
            border-left: none;
        }

        .xl86 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: none;
            border-right: none;
            border-bottom: none;
            border-left: .5pt solid windowtext;
        }

        .xl87 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: none;
            border-right: .5pt solid windowtext;
            border-bottom: none;
            border-left: none;
        }

        .xl88 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: none;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: .5pt solid windowtext;
        }

        .xl89 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: none;
            border-right: none;
            border-bottom: .5pt solid windowtext;
            border-left: none;
        }

        .xl90 {
            font-size: 10.0pt;
            vertical-align: top;
            border-top: none;
            border-right: .5pt solid windowtext;
            border-bottom: .5pt solid windowtext;
            border-left: none;
        }

        @font-face {
            font-family: ipag;
            font-style: normal;
            font-weight: normal;
            src:url('{{ storage_path("fonts/ipag.ttf")}}');
        }

        @font-face {
            font-family: ipag;
            font-style: bold;
            font-weight: bold;
            src:url('{{ storage_path("fonts/ipag.ttf")}}');
        }

        body {
            font-family: ipag;
        }

        .w-info {
            width: 400px;
        }

        .mg-errors {
            color: red;
            margin-bottom: 10px;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 98%;
        }
    </style>
</head>

<body>
    @foreach($R as $key => $rq)
    <?php $data_cnt=0; ?>
    <div class="bottom-table mt-1 wrapper w-100">
        <table border=0 cellpadding=0 cellspacing=0>
            <tr height=38 style='height:28.35pt'>
                <td colspan=9 height=38 class=xl66 width=630 style='height:28.35pt;
         width:477pt'>引取依頼書</td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl68>{{ @$today }}</td>
            </tr>
            <tr height=22 style='height:16.2pt'>
                <td height=22 class=xl69 colspan=2 style='height:16.2pt;'>{{ @$PDCPCD->CUSTNM }}</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=22 style='height:16.2pt'>
                <td height=22 class=xl69 colspan=3 style='height:16.2pt;'>{{ @$PDCPCD->CST }}</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=22 style='height:16.2pt'>
                <td height=22 class=xl69 style='height:16.2pt'></td>
                <td class=xl69 colspan=3>{{ @$PDCPCD->TCM }}　様</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl67 style='height:14.4pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67 colspan=2>ガラスリソーシング（株）</td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl67 style='height:14.4pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl71 colspan=2>　TEL　{{ @$CompanyInfo["phone"]}}</td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl67 style='height:14.4pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl71 colspan=2>　FAX　{{ @$CompanyInfo["fax"]}}</td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl67 style='height:14.4pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl72 colspan=2>　担当　{{ @$CompanyInfo["manager"]}}</td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=94 style='height:70.65pt'>
                <td height=94 class=xl74 width=70 style='height:70.65pt;width:53pt'>引<br>
                    取<br>
                    先<br>
                    名
                </td>
                <td colspan=8 class=xl75 style='border-left:none'>{{ @$rq->CUSTNM }}</td>
            </tr>
            <tr height=22 style='height:17.1pt'>
                <td height=22 class=xl76 width=70 style='height:17.1pt;border-top:none; width:53pt'>依頼No</td>
                <td colspan=8 class=xl77 width=560 style='border-right:.5pt solid black; padding-left: 10px; border-left:none;width:424pt'>{{ @$request_id }}</td>
            </tr>
            <tr height=226 style='height:170.1pt'>
                <td height=226 class=xl74 width=70 style='height:170.1pt;border-top:none; width:53pt'>依<br><br>頼<br><br>内<br><br>容</td>
                <td colspan=8 class=xl80 width=560 style='border-left:none;width:424pt; padding-left: 10px'>
                    {{ @$rq->BRNDNM }}の引取をお願いします。希望日などは以下に記載します。<br>
                    @foreach($RD as $d)
                    @if($d->BRNDCD == $rq->BRNDCD)
                    <?php $data_cnt++; ?>
                    ・{{@$d->hoped}}<br>
                    @endif
                    @endforeach
                    <br>
                    以上、合計{{ @$data_cnt }}台で引取りをお願い致します。<br><br>
                    {{ @$rq->request_note }}
                </td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=20 style='height:15.0pt'>
                <td height=20 class=xl81 style='height:15.0pt'>返信FAX</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl67 style='height:14.4pt'></td>
                <td class=xl82 colspan=5>ご依頼の引取は、下記の通り行いますので宜しくお願い申し上げます。</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67 colspan=3>どちらかの番号に〇をお願い致します。</td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=17 style='height:12.6pt'>
                <td height=17 class=xl67 style='height:12.6pt'></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
                <td class=xl67></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl83 style='height:14.4pt'></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl85></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82 colspan=2>１．上記日時でOK</td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82>２．変更
                </td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl70>月</td>
                <td class=xl70>日</td>
                <td class=xl70>曜日</td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl88 style='height:14.4pt'></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl90></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl83 style='height:14.4pt'></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl84></td>
                <td class=xl85></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82>返信FAX</td>
                <td class=xl82></td>
                <td class=xl70>月</td>
                <td class=xl70>日</td>
                <td class=xl70>曜日</td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl86 style='height:14.4pt'></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl82></td>
                <td class=xl87></td>
            </tr>
            <tr height=19 style='height:14.4pt'>
                <td height=19 class=xl88 style='height:14.4pt'></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl89></td>
                <td class=xl90></td>
            </tr>
        </table>
    </div>
    @if($key < (($R->count())-1))
        <div style="page-break-after: always"></div>
        @endif
        @endforeach
</body>

</html>