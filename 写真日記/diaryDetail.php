<?
require('function.php');

debug('-----------------------');
debug('日記の内容ページ');
debug('--------------------------');
debugLogStart();

// 日記IDのGETパラメータを取得
$d_id = (!empty($_GET['d_id'])) ? $_GET['d_id'] : '';
// DBからデータを取得
$viewData = getDiaryOne($d_id);
  debug('取得した日記データ：'.print_r($viewData,true));

?>


<?
$siteTitle = '日記の内容';
require('head.php');
?>

  <body class="page-diaryDetail page-1colum">


<?
  require('header.php')
?>

<div id="contents" class="site-width">

  <section id="main">

    <div class="title">
      <span class="badge"><? echo sanitize($viewData['category']); ?></span>
      <? echo sanitize($viewData['name']); ?>
   </div>

    <div class="diary-img-container">
      <div class="img-main">
       <img src="<? echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像:<? echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
       </div>
      <div class="img-sub">
        <img src="<? echo showImg(sanitize($viewData['pic1']));  ?>" alt="画像１:<? echo sanitize($viewData['name']);  ?>" class="js-switch-img-sub">
        <img src="<? echo showImg(sanitize($viewData['pic2']));  ?>" alt="画像２:<? echo sanitize($viewData['name']);  ?>" class="js-switch-img-sub">
        <img src="<? echo showImg(sanitize($viewData['pic3']));  ?>" alt="画像３:<? echo sanitize($viewData['name']);  ?>" class="js-switch-img-sub">
      </div>
    </div>
    <div class="diary-detail">
      <p><? echo sanitize($viewData['detail']); ?></p>
    </div>
    <div class="regist">
      <div class="item-left">
      <a href="index.php<? echo appendGetParam(array('d_id')); ?>">&lt; 日記一覧に戻る</a>
      </div>
      <div class="item-right">
       <form action="" method="post">
             <a href="diaryRegist.php<? echo appendGetParam(array('d_id')); ?>">
            <input type="submit" name="submit" value='' style="display:none;">編集する
         </a>
       </form>
      </div>
      </div>
  </section>
</div>

<? require('footer.php'); ?>
