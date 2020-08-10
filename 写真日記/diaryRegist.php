<?
require('function.php');

debug('---------------');
debug('日記を書くページ');
debug('---------------');
debugLogStart();


// 画面表示用データ取得
$d_id = (!empty($_GET['d_id'])) ? $_GET['d_id'] : '';
$dbFormData = (!empty($d_id)) ? getDiary($d_id) : '';
$edit_flg = (empty($dbFormData)) ? false : true;
$dbCategoryData = getCategory();
debug('日記ID:'.$d_id);
debug('フォーム用DBデータ'.print_r($dbFormData,true));
debug('カテゴリーデータ:'.print_r($dbCategoryData,true));

// パラメータ改ざんチェック
if(!empty($d_id) && empty($dbFormData)){
  debug('GETパラメータのIDが違います');
  header("Location:index.php");
}

// POST送信時処理
if(!empty($_POST)){
  debug('POST送信あります');
  debug('POST情報:'.print_r($_POST,true));
  debug('FILE情報:'.print_r($_FILES,true));

  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $detail = $_POST['detail'];

  $pic1 = (!empty($_FILES['pic1']['name']) ) ? uploadImg($_FILES['pic1'],'pic1') : '';
  $pic1 = ( empty($pic1) && !empty($dbFormData['pic1']) ) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name']) ) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = ( empty($pic2) && !empty($dbFormData['pic2']) ) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name']) ) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = ( empty($pic3) && !empty($dbFormData['pic3']) ) ? $dbFormData['pic3'] : $pic3;

  if(empty($dbFormData)){
    validRequired($name, 'name');
    validMaxLen($name, 'name');
    validMaxLen($detail, 'detail', 1000);
  }else{
    if($dbFormData['name'] !== $name){
      validRequired($name, 'name');
      validMaxLen($name, 'name');
    }
    if($dbFormData['detail'] !== $detail){
      validMaxLen($detail, 'detail', 1000);
    }
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');

    try {
      $dbh = dbConnect();
      if($edit_flg){
        debug('DB更新です');
        $sql = 'UPDATE contents SET name = :name, category_id = :category, detail = :detail,
                pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE id = :d_id';
        $data = array('name' => $name , ':category' => $category, ':detail' => $detail,
               ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':d_id' => $d_id);
      }else{
        debug('DB新規登録です');
        $sql = 'INSERT INTO contents (name, category_id, detail, pic1, pic2, pic3, create_date )
                VALUES (:name, :category, :detail, :pic1, :pic2, :pic3, :date)';
        $data = array(':name' => $name , ':category' => $category, ':detail' => $detail,
                ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':date' => date('Y年m月d日'));
      }
      debug('SQL:'.$sql);
      debug('流し込みデータ:'.print_r($data,true));
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('TOPページへ遷移');
        header("Location:index.php");
      }

    }catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG04;
    }
  }
}
?>



<?
$siteTitle = (!$edit_flg) ? '新しい日記' : '日記の編集';
require('head.php');
?>

<body class="page-diaryRegist page-1colum">

<? require('header.php'); ?>

  <div id="contents" class="site-width">
    <h1 class="page-title"> <? echo (!$edit_flg) ? '新しい日記' : '日記の編集'; ?> </h1>

    <section id="main">
     <div class="form-container">
      <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%,box-sizing:border-box;">
        <div class="area-msg">
         <? if(!empty($err_bsg['common'])) echo $err_msg['common']; ?>
        </div>

        <label class="<? if(!empty($err_msg['name'])) echo 'err'; ?>">
          タイトル<span class="label-require"></span>
          <input type="text" name="name" value="<? echo getFormData('name'); ?>" >
        </label>
        <div class="area-msg">
        <? if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
        </div>

        <label class="<? if(!empty($err_msg['category_id'])) echo 'err'; ?>">
          カテゴリ
          <select name="category_id" id="">
            <option value="0" <? if(getFormData('category_id') == 0 ){ echo 'selected';} ?>>選択</option>
            <? foreach($dbCategoryData as $key => $val){
              ?>
            <option value="<? echo $val['id'] ?>" <? if(getFormData('category_id') == $val['id'] ){ echo 'selected'; } ?> >
              <? echo $val['name']; ?>
            </option>
            <? } ?>
          </select>
        </label>
        <div class="area-msg">
          <? if(!empty($err_msg['category_id'])) echo $err_msg['category_id']; ?>
        </div>

        <label class="<? if(!empty($err_msg['detail'])) echo 'err'; ?>">
          この日の出来事
          <textarea name="detail" id="js-count" cols="30" rows="30" style="height:300px;">
          <? echo getFormData('detail'); ?></textarea>
        </label>
        <p class="counter-text"><span id="js-count-view">0</span>/1000文字</p>
        <div class="area-msg">
        <? if(!empty($err_msg['detail'])) echo $err_msg['detail']; ?>
        </div>

        <div style="overflow:hidden;">
          <div class="imgDrop-container">
            画像１
              <label class="area-drop" <? if(!empty($err_msg['pic1'])) echo 'err'; ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file">
                <img src="<? echo getFormData('pic1'); ?>" alt="" class="prev-img"
                 style="<? if(empty(getFormData('pic1'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
              <? if(!empty($err_msg['pic1'])) echo $err_msg['pic1']; ?>
              </div>
          </div>
          <div class="imgDrop-container">
            画像２
              <label class="area-drop" <? if(!empty($err_msg['pic2'])) echo 'err'; ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file">
                <img src="<? echo getFormData('pic2'); ?>" alt="" class="prev-img"
                 style="<? if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
              <? if(!empty($err_msg['pic1'])) echo $err_msg['pic2']; ?>
              </div>
          </div>
           <div class="imgDrop-container">
            画像３
              <label class="area-drop" <? if(!empty($err_msg['pic2'])) echo 'err'; ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic3" class="input-file">
                <img src="<? echo getFormData('pic3'); ?>" alt="" class="prev-img"
                 style="<? if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
              <? if(!empty($err_msg['pic1'])) echo $err_msg['pic3']; ?>
              </div>
          </div>
          </div>
        </div>

        <div class="btn-container">
          <input type="submit" class="btn btn-mid" value="<? echo (!$edit_flg) ? '日記つける' : '日記更新する'; ?>">
        </div>
      </form>
     </div>
    </section>

  </div>

  <? require('footer.php'); ?>
</body>
