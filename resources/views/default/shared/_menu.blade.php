<div class="menu">
  <div class="container">
    <div class="home_menu">
      <div class="dt"><a href="javascript:void(0);"><i class="iconfont">&#xe642;</i>商品分类</a></div>
      <div class="dd @if(!isset($page_index_ident)) none @endif">
        @foreach($menuData as $value)
        <div class="item">
          <div class="item_home">
            <div class="title">
              <a href="/product/list/{{$value->id}}" target="_blank">{{$value->name}}<i>&gt;</i></a>
            </div>
          </div>
          <!-- 展开层 start -->
          @if(isset($value->items) && !empty($value->items))
          <div class="item_layer none">
            <div class="subitems">
              @foreach($value->items as $value1)
              <dl>
                <dt><a href="/product/list/{{$value1->id}}" target="_blank"><em>{{$value1->name}}</em><i>&gt;</i></a></dt>
                <dd>
                  @foreach($value1->items as $value2)
                  <a href="/product/list/{{$value2->id}}" target="_blank">{{$value2->name}}</a>
                  @endforeach
                </dd>
              </dl>
              @endforeach
            </div>
          </div>
          @endif
          <!-- 展开层 end -->
          <!-- 预留右侧广告位 start -->
          <!-- ............ -->
          <!-- 预留右侧广告位 end -->
        </div>
        @endforeach
      </div>
    </div>
    <ul class="custom_menu">
      <li><a href="/product/list" target="_blank">全部商品</a></li>
      <li><a href="/help/show/100000" target="_blank">帮助中心</a></li>
    </ul>
  </div>
</div>
