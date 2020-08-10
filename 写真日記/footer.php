<footer id="footer">
  Copyright <a href="https://simple.brew.life">  - Simple Brew Life -  </a>. All Rights Reserved.
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
  $(function(){

     // フッターを最下部に固定
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    }

    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsa.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function() { $jsShowMsg.slideToggle('slow'); }, 5000);
    }

    // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      var file = this.files[0],
          $img = $(this).siblings('.prev-img'),
          fileReader = new FileReader();

          fileReader.onload = function(event) {
            $img.attr('src', event.target.result).show();
          };

          fileReader.readAsDataURL(file);

    });

    // テキストエリアカウント
    var $countUp = $('#js-count'),
        $countView = $('#js-count-view');
    $countUp.on('keyup', function(e){
      $countView.html($(this).val().length);
    });

    // 画像切替
    var $switchImgSubs = $('.js-switch-img-sub'),
        $switchImgMain = $('#js-switch-img-main');
    $switchImgSubs.on('click',function(e){
      $switchImgMain.attr('src',$(this).attr('src'));
    });



  });

</script>
