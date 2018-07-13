<?php
session_start();
require("lang.php");
ini_set('display_errors', 0);
if($_GET){
    if($_GET["lang"]){
        $_SESSION["lang"]=$_GET["lang"];
        header('Location: ./');
    }else{
        if($_SESSION["lang"]!="en"){
            $_SESSION["lang"]="ja";
        }
    }
}


$lang=$_SESSION["lang"];
if (isset($_GET['code']) && isset($_GET['state'])) {
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
	 $runfile = 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];   
    }else{
	 $runfile = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];   
    }
    $array = explode(' ', $_GET['state']);
    $data = [
        'client_id' => $array[1],
        'client_secret' => $array[2],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $runfile,
        'code' => $_GET['code'],
    ];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://'.$array[0].'/oauth/token');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $buf = curl_exec($curl);
    if (curl_errno($curl)) {
        exit;
    }
    curl_close($curl);
    $json = json_decode($buf);
    $at = $json->access_token;
    $_SESSION['at'] = $at;
    $_SESSION['domain'] = $array[0];
    header('Location: ./');
} else {
    if ($_SESSION['at'] && $_SESSION['domain']) {
        $mode = 'show';
        $html = show();
    } else {
        $mode = 'login';
    }
}
function show()
{
    //Notice エラーを殺してもいいけどなんか嫌なのでとっても冗長に書いてしまっています。
    if ($_GET) {
        if (!empty($_GET['max_id'])) {
            $maxid = $_GET['max_id'];
        } else {
            $maxid = '';
        }
    } else {
        $maxid = '';
    }
    $url = 'https://'.$_SESSION['domain'].'/api/v1/suggestions?max_id='.$maxid;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['at']]);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_HEADER, true);
    $buf = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); // ヘッダサイズ取得
$header = substr($buf, 0, $header_size); // headerだけ切り出し
$body = substr($buf, $header_size); // bodyだけ切り出し
preg_match('/max_id=([0-9]+)/', $header, $matches); // 取得記事要素数
if ($matches) {
    $max = $matches[1];
}
    $obj = json_decode($body);
    $html = '';

    foreach ($obj as $user) {
        $avatar = $user->avatar;
        $name = $user->display_name;
        $note = $user->note;
        $acct = $user->acct;
        $array = explode('@', $acct);
        if (count($array) > 1) {
            $domain = $array[1];
        } else {
            $domain = $_SESSION['domain'];
        }
        $id = $user->id;
        $username = $user->username;
        $html = $html.'<div class="card" style="width: 500px; max-width:100%;">
            <div class="card-block">
               
            <h4 class="card-title"><img class="card-img-top" src="'.$avatar.'" alt="" style="width:45px; height:45px;" >'.htmlspecialchars($name).'</h4>
            <h6 class="card-subtitle mb-2 text-muted">@'.$acct.'</h6>
            <p class="card-text">'.$note.'</p>
             <a href="https://'.$domain.'/@'.$username.'" class="btn btn-primary" target="_blank">このアカウントを見る</a>
            </div>
            </div>';
    }

    if (count($obj) >= 40) {
        $html = $html.'<a href="?max_id='.$max.'">Next Page</a>';
    }
    if (curl_errno($curl)) {
        exit;
    }
    curl_close($curl);

    return $html;
}
if (!empty($_GET['logout'])) {
    session_destroy();
    header('Location: ./');
}
?>
<!doctype html>
<html lang="<?php echo $lang;?>">
	<head>
		<title><?php echo $title[$lang];?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=0.5, maximum-scale=1, user-scalable=yes">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
		<style>
		    body{
			    padding:10px;
		    }
            #result{
                display:flex;
                flex-wrap:wrap;
                justify-content: center;
                align-items: center;
                padding:10px;
            }
			.card{
                margin:10px;
            }
			.invisible{
				display:inline;
				visibility:unset !important;
			}
        </style>
	</head>
<body>
	<h1><?php echo $title[$lang];?></h1>
    <a href="?lang=en">English</a>/<a href="?lang=ja">日本語</a><br>
	<?php if ($mode == 'login'): ?>
	<h4><?php echo $login_txt[$lang];?></h4>
	<div id="first-login">
	<span id="mess"></span>
	<br>
	<label class="sr-only" for="url"><?php echo $url[$lang];?></label>
	<div class="input-group" style="max-width:calc(100% - 10px); width:400px;">
	<div class="input-group-addon">https://
	</div>
	<input type="text" class="form-control" id="url" placeholder="ex)mstdn.jp">
	</div>
	<div id="suggest">
	</div>
	<br>
	<button id="login" class="btn btn-primary"><?php echo $login[$lang];?></button>
	<script src="./js/login.js"></script>
	<?php elseif ($mode == 'show'): ?>
	<h4><?php echo $show_result[$lang];?></h4>
    <?php echo $note[$lang];?>
	<br>
	<div id="result">
	<?php
        echo $html;
    ?>
	</div>
	<a href="?logout=true" class="btn btn-danger"><?php echo $logout[$lang];?></a>
	<?php endif; ?>
	<br>&copy; 
	<a href="htps://kirishima.cloud/@Cutls" target="_blank">Cutls P</a>2018.
	<br><?php echo $before_about[$lang];?>
	<a href="https://ja.mstdn.wiki/%E3%83%95%E3%82%A9%E3%83%AD%E3%83%BC%E3%83%AC%E3%82%B3%E3%83%A1%E3%83%B3%E3%83%87%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3_(%E3%83%9E%E3%82%B9%E3%83%88%E3%83%89%E3%83%B3%E3%81%AE%E6%A9%9F%E8%83%BD)" target="_blank">フォローレコメンデーション (マストドンの機能)</a>
    <?php echo $after_about[$lang];?>
</body>
