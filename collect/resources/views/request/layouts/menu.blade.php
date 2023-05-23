<nav class="w-10em float-left pt-2">
    <ul>
        <li><a href="{{ route('r.detail',['id'=> 0]) }}" title=""><i class="fa fa-suitcase" aria-hidden="true"></i><span>依頼申込</span></a></li>
        <li><a href="{{ route('r.history') }}" class="route-history" title=""><i class="fa fa-history" aria-hidden="true"></i><span>依頼履歴</span></a></li>
        <li><a href="{{ route('r.client') }}" title=""><i class="fa fa-address-book" aria-hidden="true"></i><span>登録情報</span></a></li>
    </ul>
</nav>