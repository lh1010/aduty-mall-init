function navLocation() {
  var nav_top = $('.lm_nav').offset().top;
  $(window).on('scroll', function () {
    var current_top = $(window).scrollTop();
    if (current_top > nav_top) {
      $('.lm_nav').addClass('lm_nav1');
      $('.lm_nav .btn').show();
    } else {
      $('.lm_nav').removeClass('lm_nav1');
      $('.lm_nav .btn').hide();
    }
    navLocation1();
  });
}

function navLocation1() {
  var current_top = $(window).scrollTop();
  var nav_top = $('.lm_nav').offset().top;
  var c1_top = $('.c1').offset().top - 55;
  var c2_top = $('.c2').offset().top - 55;
  $(".lm_nav li").removeClass('on');
  if (current_top >= c2_top) {
    $(".lm_nav li").eq(1).addClass('on');
  } else {
    $(".lm_nav li").eq(0).addClass('on');
  }
}

$('.small-images img').hover(function() {
  var img = $(this).data('original');
  $('.large-image img').attr('src', img);
  $(this).addClass('on').siblings().removeClass('on');
});

$('.specs .specs_item').click(function() {
  if ($(this).data('valid') != 1) {
    return false;
  }
  window.location.href = "/product/show/" + $(this).data('sku');
})