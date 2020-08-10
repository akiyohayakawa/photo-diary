<?php

ini_set('log_errors','on');
ini_set('error_log','php.log');

$debug_flg = true;

function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}


session_save_path("C:/xampp/tmp/");
ini_set('session.gc_maxlifetime',60*60*24*30);
ini_set('session.cookie_lifetime',60*60*24*30);
session_start();
session_regenerate_id();

// 画面表示処理開始ログ吐き出し関数
function debugLogStart(){
  debug('-----------画面表示処理開始');
  debug('セッションID:'.session_id());
  debug('セッション変数の中身:'.print_r($_SESSION,true));
  debug('現在日時:'.time());
 }

// 定数
define('MSG01','入力必須です');
define('MSG02','255文字以内で入力してください');
define('MSG03','1000文字以内で入力してください');
define('MSG04','エラーが発生しました。しばらく経ってからやり直してください。');
define('SUC04', '日記を保存しました');

//エラーメッセージ格納用の配列
$err_msg = array();

//バリデーション関数
function validRequired($str, $key){
  if($str === ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
  if(mb_strlen($str) > 1000){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}


//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}

// データベース関数

//DB接続
function dbConnect(){
  $dsn = 'mysql:dbname=写真日記;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $option = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $password, $option);
  return $dbh;
}
//SQL実行
function queryPost($dbh, $sql, $data){
  $stmt = $dbh->prepare($sql);
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました');
    debug('失敗したSQL:'.print_r($stmt,true));
    $err_msg['common'] = MSG04;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}

function getDiary($d_id){
  debug('日記情報を取得します');
  debug('日記ID：'.$d_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM contents WHERE id = :d_id AND delete_flg = 0';
    $data = array(':d_id' => $d_id);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
function getDiaryList($currentMinNum = 1, $category, $sort, $span =20){
  debug('日記情報を取得します');

  try {
    $dbh = dbConnect();
    // 件数用のSQL
    $sql = 'SELECT id FROM contents';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY create_date ASC';
          break;
        case 2:
          $sql .= 'ORDER BY create_date DESC';
        break;
      }
    }
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total']/$span);
    if(!$stmt){
      return false;
    }

    // ページング用のSQL
    $sql = 'SELECT * FROM contents';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY create_date ASC';
        break;
        case 2:
          $sql .= ' ORDER BY create_date DESC';
        break;
      }
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL:'.$sql);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
function getDiaryOne($d_id){
  debug('日記情報を取得します');
  debug('日記ID：'.$d_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT d.id , d.name , d.detail, d.pic1, d.pic2, d.pic3, d.create_date, d.update_date, c.name AS category
            FROM contents AS d LEFT JOIN category AS c ON d.category_id = c.id WHERE d.id = :d_id AND d.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':d_id' => $d_id);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getCategory(){
  debug('カテゴリー情報を取得します');

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}


// その他
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
// フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;

  if(!empty($dbFormData)){
    if(!empty($err_msg[$str])){
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData[$str]);
      }
    }else{
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}
// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報:'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])) {
    try {
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
        break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
        throw new RuntimeException('その他のエラーが発生しました');
      }
        $type = @exif_imagetype($file['tmp_name']);
        if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG,IMAGETYPE_PNG], true)) {
           throw new RuntimeException('画像形式が未対応です');
        }
        $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
        if(!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
        }
        chmod($path,0644);

        debug('ファイルは正常にアップロードされました');
        debug('ファイルパス:'.$path);
        return $path;

      } catch (RuntimeException $e) {

        debug($e->getMessage());
        global $err_msg;
        $err_msg[$key] = $e->getMessage();
      }
    }

  }




//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
//画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

?>
