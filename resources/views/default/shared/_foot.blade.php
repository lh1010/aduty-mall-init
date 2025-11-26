<div class="foot">
  <div class="container">
    <div class="foot_menu">
      @foreach($footData['helps'] as $value)
      <dl>
        <dt>{{$value->name}}</dt>
        @foreach($value->articles as $value_article)
        <dd><a href="/help/show/{{$value_article->id}}" target="_blank">{{$value_article->title}}</a></dd>
        @endforeach
       </dl>
      @endforeach
      <div class="foot_contact">
        <div class="item wxapp_qrcode">
          <img src="{{Config('common.wxmp.qrcode')}}">
          <p class="txt">微信公众号</p>
        </div>
      </div>
    </div>
    <div class="foot_link">
      <span>友情链接</span>
      <ul class="clearfix">
        <li><a href="https://www.linghao100.com" target="_blank">领浩科技</a></li>
        <li><a href="http://home.adutymall.lh909.com" target="_blank">开源商城</a></li>
        <li><a href="https://www.linghao100.com/s/swb" target="_blank">商务邦系统</a></li>
        <li><a href="https://www.linghao100.com/s/tg" target="_blank">红人通告系统</a></li>
        <li><a href="https://www.linghao100.com/s/ttb" target="_blank">APP拉新系统</a></li>
        <li><a href="https://www.linghao100.com/s/qun" target="_blank">社群人脉系统</a></li>
        <li><a href="https://www.aliyun.com" target="_blank">阿里云</a></li>
      </ul>
    </div>
    <p class="copyright">
      ©{{date('Y')}} {{Config('common.pc.app_name')}} <a href="https://beian.miit.gov.cn" target="_blank">{{Config('common.beian')}}</a>
    </p>
  </div>
</div>
<!-- 百度统计 satrt -->
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?b0970230ef6ad6bcda4e3a4540f253fd";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<!-- 百度统计 end -->
