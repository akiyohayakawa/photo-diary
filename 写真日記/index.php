<?
require('function.php');

debug('----------');
debug('トップページ');
debug('----------');
debugLogStart();

// GETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['d'] : 1;
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

if(!is_int($currentPageNum)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}

// リスト表示
$listSpan = 20;
$currentMinNum = (($currentPageNum-1)*$listSpan);
$dbDiaryData = getDiaryList($currentMinNum, $category, $sort);
$dbCategoryData = getCategory();


debug('画面表示処理終了------------');
?>



<?
$siteTitle = 'HOME';
require('head.php');
?>

<body class="page-home page-2colum">


<?
  require('header.php');
?>
<div id="contents" class="site-width">

 <section id="sidebar">
  <form method="get">
    <h1 class="title">カテゴリ</h1>
      <div class="select_box">
        <span class="icn_select"></span>
        <select name="c_id">
        <? foreach($dbCategoryData as $key => $val){ ?>
          <option value="<? echo $val['id'] ?>" <? if(getFormData('c_id',true) == $val['id'] ){ echo 'selected'; } ?> >
           <? echo $val['name']; ?>
          </option>
        <? } ?>

        </select>
      </div>
    <h1 class="title">表示順</h1>
      <div class="select_box">
        <span class="icn_select"></span>
        <select name="sort">
          <option value="1" <? if(getFormData('sort',true) == 1){ echo 'selected'; } ?> >今から順</option>
          <option value="2" <? if(getFormData('sort',true) == 2){ echo 'selected'; } ?> >昔から順</option>
        </select>
      </div>
      <input type="submit" value="さがす">
  </form>

</section>

<section id="main">
  <div class="search-title">
    <div class="search-left">
      <span class="total-num"><? echo sanitize($dbDiaryData['total']); ?></span>日分の日記
    </div>
    <div class="search-right">
      <span class="num"><? echo (!empty($dbDiaryData['data'])) ? $currentMinNum+1 : 0; ?></span>
       - <span class="num"><? echo $currentMinNum+count($dbDiaryData['data']); ?></span>
       日分 / <span class="num"><? echo sanitize($dbDiaryData['total']); ?></span>日分中
    </div>
  </div>
  <div class="panel-list">

    <? foreach($dbDiaryData['data'] as $key => $val): ?>

     <a href="diaryDetail.php<? echo (!empty(appendGetParam())) ? appendGetParam().'&d_id='.$val['id']
                                 : '?d_id='.$val['id']; ?>" class="panel">
      <div class="panel-head">
        <img src="<? echo sanitize($val['pic1']); ?>" alt="<? echo sanitize($val['name']); ?>">
      </div>
      <div class="panel-body">
        <p class="panel-title"><? echo sanitize($val['name']); ?></p>
        <p class="panel-date"><? echo sanitize($val['create_date']); ?></p>
      </div>
    </a>
      <?
         endforeach;
          ?>
  </div>

  <? pagination($currentPageNum, $dbDiaryData['total_page']); ?>

</section>

</div>

<? require('footer.php'); ?>
