<div class="head">
  <div class="container">
    <div class="head_box">
      <a class="logo" href="/"><img src="{{Config('common.pc.app_logo')}}"/></a>
      <div class="mainbd">
        <div class="search" id="topsearch">
          <form method="get" autocomplete="off">
            <input type="hidden" name="stype" value="{{ Request()->stype ? Request()->stype : 1 }}">
            <ul class="search_menu">
              <li class="{{ !Request()->stype || Request()->stype == 1 ? 'on' : '' }}" data-type="1">商品</li>
            </ul>
            <div class="search_box">
              <input class="k" type="text" name="k" placeholder="请输入关键字" value="{{Request()->k}}">
              <button class="search_btn" type="button"><i class="iconfont">&#xe8d6;</i>搜索</button>
            </div>
          </form>
        </div>
      </div>
    </div> 
  </div>
</div>