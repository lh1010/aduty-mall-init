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
    navLocation100();
  });
}

function navLocation100() {
  var current_top = $(window).scrollTop();
  var nav_top = $('.lm_nav').offset().top;
  var c1_top = 0;
  var $c1 = $('.c1');
  if ($c1.length) {
      c1_top = $c1.offset().top - 55;
  }
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

// 商品详情页 切换规格
var ProductSpec = (function() {
  // 私有变量
  var groups = []; // 规格分组数据（来自 product.specifications）
  var allSkus = []; // 所有 SKU 列表（来自 product.skus）
  var selected = {}; // 当前选中状态：{ 规格组ID: 选中的选项ID }
  var currentSku = null; // 当前匹配到的 SKU 对象

  /**
   * 初始化选中状态
   * - 优先使用后端标记的 selected=1 的选项
   * - 如果某组没有选中项，则默认选第一个 valid=1 的选项
   * - 若都没有，则选该组第一个选项
   */
  function initSelected() {
    groups.forEach(function(group) {
      var gid = group.specification_id;
      var found = false;
      // 1，查找后端预设的选中项 原则上来说，后端肯定会返回选中项的
      group.options.forEach(function(opt) {
        if (opt.selected === 1) {
          selected[gid] = opt.specification_option_id;
          found = true;
        }
      });
      // 2，如果没有预设项，选第一个有效项（有库存组合）
      if (!found && group.options.length > 0) {
        var firstValid = group.options.find(function(opt) {
          return opt.valid === 1;
        });
        // 如果有有效项则选它，否则选第一个（兜底）
        selected[gid] = firstValid ? firstValid.specification_option_id : group.options[0].specification_option_id;
      }
    });
  }

  /**
   * 绑定规格点击事件（事件委托）
   * - 只响应 .specs_item 且不含 .invalid 类的元素
   * - 点击后更新选中状态 → 重新计算有效项 → 匹配SKU并刷新页面
   */
  function bindEvents() {
    $(document).on('click', '.specs_item:not(.invalid)', function() {
      var $item = $(this);
      var groupId = parseInt($item.data('group-id'), 10);
      var optionId = parseInt($item.data('option-id'), 10);
      // 如果点击的是当前已选中的选项，不做任何事
      if (selected[groupId] === optionId) return;
      // 更新选中状态
      selected[groupId] = optionId;
      // 重新计算所有选项的有效性（基于新的组合）
      recalcValidity();
      // 匹配 SKU 并刷新页面展示
      matchAndRender();
    });
  }

  /**
   * 重新计算所有规格选项的 valid 状态
   * 规则：对于某个选项，假设用户点击它（即本组选它，其他组保持当前选中），
   *    - 若存在至少一个 SKU 的 option_ids 包含这个组合，则该选项有效（valid=1），
   *    - 否则无效（valid=0）
   */
  function recalcValidity() {
    groups.forEach(function(group) {
      var gid = group.specification_id;
      group.options.forEach(function(opt) {
        var oid = opt.specification_option_id;
        // 1. 构建“假设选中”的选项ID列表
        //    - 去掉本组已选的选项（因为本组要换成当前选项）
        //    - 加入当前选项 oid
        var assumed = [];
        for (var key in selected) {
          if (parseInt(key, 10) === gid) continue;
          assumed.push(selected[key]);
        }
        assumed.push(oid);
        // 2. 检查是否存在一个 SKU 完全包含 assumed 中的所有ID
        var isValid = allSkus.some(function(sku) {
          return assumed.every(function(id) {
            return sku.option_ids.indexOf(id) !== -1;
          });
        });
        // 3. 更新选项的 valid 和 selected 状态
        opt.valid = isValid ? 1 : 0;
        opt.selected = (selected[gid] === oid) ? 1 : 0;
      });
    });
  }

  /**
   * 根据当前选中的规格组合，匹配对应的 SKU，并更新页面 DOM
   * - 匹配规则：option_ids 与当前选中的 option_id 列表完全相同
   * - 更新：价格、库存、SKU编号、封面图等字段
   * - 同时更新规格选项的样式（on / invalid）
   */
  function matchAndRender() {
    // 1. 获取当前所有选中的 option_id 并排序（便于比较）
    var selectedIds = Object.values(selected).sort();

    // 2. 在 allSkus 中查找匹配的 SKU
    currentSku = allSkus.find(function(sku) {
      var ids = sku.option_ids.slice().sort();
      if (ids.length !== selectedIds.length) return false;
      return ids.every(function(id, index) {
        return id === selectedIds[index];
      });
    }) || null;

    // 3. 更新页面元素
    if (currentSku) {
      // 价格
      $('#product-price').text(currentSku.price);
      // 库存
      $('#product-stock').text(currentSku.stock);
      // SKU 编号
      $('#sku-code').text(currentSku.sku);
      // 封面图（直接替换 src，同时更新 data-original 兼容 lazyload）
      $('#product-cover').attr('src', currentSku.cover).attr('data-original', currentSku.cover);
      // 购买按钮的 onclick 参数
      $('#btn-buy').attr('onclick', "oneKeyBuy('" + currentSku.sku + "')");
      $('#btn-cart').attr('onclick', "addCart('" + currentSku.sku + "')");
      $('#btn-buy-100').attr('onclick', "oneKeyBuy('" + currentSku.sku + "')");
      // 更新浏览器地址栏URL
      var sku = currentSku.sku;
      var baseUrl = window.location.pathname;
      var newUrl = baseUrl + '?sku=' + encodeURIComponent(sku);
      window.history.pushState({ sku: sku }, '', newUrl);
    } else {
      // 理论上不该发生，但若匹配不到，给出提示
      $('#product-stock').text('暂不可售');
    }

    // 4. 更新规格选项的样式（高亮/置灰）
    groups.forEach(function(group) {
      var gid = group.specification_id;

      group.options.forEach(function(opt) {
        var $el = $('.specs_item[data-group-id="' + gid + '"][data-option-id="' + opt.specification_option_id + '"]');
        // 移除所有状态类
        $el.removeClass('on invalid');
        // 选中状态
        if (opt.selected === 1) $el.addClass('on');
        // 无效状态（不可点击）
        if (opt.valid === 0) $el.addClass('invalid');
        // 同步 data-valid 属性（供外部判断）
        $el.data('valid', opt.valid);
      });
    });
  }

  /**
   * 初始化规格模块
   * @param {Object} data - 后端返回的 product 对象（包含 specifications 和 skus）
   * 使用示例：ProductSpec.init(productData);
   */
  function init(data) {
    // 获取数据
    groups = data.specifications || [];
    allSkus = data.skus || [];

    // 如果没有规格或没有sku，则不执行任何操作
    // 单规格商品，不再执行任何操作
    if (groups.length === 0 || allSkus.length === 0) return;

    // 依次执行初始化流程
    initSelected();      // 1. 确定默认选中项
    recalcValidity();    // 2. 计算初始有效性
    matchAndRender();    // 3. 匹配 SKU 并渲染页面
    bindEvents();        // 4. 绑定点击事件
  }

  // 对外暴露的接口
  return {
    init: init
  };
})();