<h5 class="title-header menu-list d-inline-block">{{ @$titleheader }}</h5>
<nav class="pt-2 menu-list">
    <label class="btn-bars cursor-pointer m-0" title="メニュー"><i class="fa fa-bars" aria-hidden="true"></i></label>
    <label class="btn-close cursor-pointer m-0" title="閉じる"><i class="fa fa-close" aria-hidden="true"></i></label>
    <ul class="">
        <li><a href="{{ route('a.rsearch'  ) }}" title=""><i class="fa fa-search" aria-hidden="true"></i><span>依頼検索</span></a></li>
        <li><a href="{{ route('a.dcalender') }}" class="" title=""><i class="fa fa-calendar" aria-hidden="true"></i><span>手配カレンダ</span></a></li>
        @if(array_key_exists(1, $authority_type ) || array_key_exists(9, $authority_type ) )
        <li><a href="{{ route('a.dimport') }}" title=""><i class="fa fa-download" aria-hidden="true"></i><span>台貫マスタ取込</span></a></li>
        <li><a href="{{ route('a.mmst') }}" title=""><i class="fa fa-university" aria-hidden="true"></i><span>取引先マスタ</span></a></li>
        <li><a href="{{ route('a.tmst') }}" title=""><i class="fa fa-address-card-o" aria-hidden="true"></i><span>取引先担当者マスタ</span></a></li>
        <li><a href="{{ route('a.kmst') }}" title=""><i class="fa fa-cc" aria-hidden="true"></i><span>取引先単価マスタ</span></a></li>
        <li><a href="{{ route('a.divisions') }}" title=""><i class="fa fa-th-large" aria-hidden="true"></i><span>原料区分マスタ</span></a></li>
        <li><a href="{{ route('a.smst') }}" title=""><i class="fa fa-credit-card" aria-hidden="true"></i><span>物流費マスタ</span></a></li>
        <li><a href="{{ route('a.fmst') }}" title=""><i class="fa fa-cubes" aria-hidden="true"></i><span>商品マスタ</span></a></li>
        <li><a href="{{ route('a.rmst') }}" title=""><i class="fa fa-truck" aria-hidden="true"></i><span>車両マスタ</span></a></li>
        <li><a href="{{ route('a.vmst') }}" title=""><i class="fa fa-folder" aria-hidden="true"></i><span>運転手マスタ</span></a></li>
        <li><a href="{{ route('a.ulist') }}" title=""><i class="fa fa-users" aria-hidden="true"></i><span>アカウント管理</span></a></li>
        @endif
    </ul>
</nav>