<?php
// DB接続設定
	$dsn = 'データベース名';
	$user = 'ユーザー名';
    $password = 'パスワード';
    //下　PDOクラスのインスタンス化　インスタンス化するときにデータベース情報も渡している
    $pdo = new PDO($dsn, $user, $password,
     array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
//  テーブルをデータベースに作成
     $sql = "CREATE TABLE IF NOT EXISTS category "
	."("
	."id INT AUTO_INCREMENT PRIMARY KEY,"
	."name char(32),"
    ."comment TEXT,"
    ."date DATETIME,"//投稿日時　日時
    ."pass TEXT" //パスワード　TEXT型
	.");";
	$stmt = $pdo->query($sql);//
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板サイト</title>
</head>
<body>
<?php
    //変数定義
    /*
    $name=$_POST["myname"];
    $document=$_POST["comment"];
    $show=$_POST["show_edit"];
    $del=$_POST["del"];
    $pass=$_POST["pass"];
    $edit_number=$_POST["edit"];
    */
if(isset($_POST["submit"])){
    if(!empty($_POST["myname"]&&$_POST["comment"])&&empty($_POST["del"])){//新規投稿 or 編集書き換え
        if(empty($_POST["show_edit"])&&!empty($_POST["pass"])){//単純な追加
            $name=$_POST["myname"];
            $document=$_POST["comment"];
            $pass=$_POST["pass"];
            $date = date("Y/m/d H:i:s");
            //レコードの追加
            //prepare()の()がプリペアドステイトメント　sql文をセット
            $sql = $pdo -> prepare("INSERT INTO category (name, comment, date ,pass) VALUES (:name, :comment, :date, :pass)");
            //$pdoはインスタンス　プレースホルダーにbindparam関数で実際の値をいれる
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $document, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            /*同じ中身の変数をまた定義しているからいらないのかもしれない
            $name = '$name';
            $comment = '$document'; 
            $date = '$date';
            $pass = '$pass';
            */
            $sql -> execute();//sql文を実行する
        }
        elseif(!empty($_POST["show_edit"]&&$_POST["pass"])){//編集書き換え $showが入っているとき
            $show=$_POST["show_edit"];
            $pass=$_POST["pass"];
            $name=$_POST["myname"];
            $document=$_POST["comment"];
            $date = date("Y/m/d H:i:s");
            
            //全部のデータの取り出し
            $sql = 'SELECT * FROM category';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                if($row["pass"]==$pass && $row["id"]==$show){
                    //　編集　
                    $id = $show; //変更する投稿番号
                    //エラー出るかも
                    /*
                    $name = "$name";
                    $comment = "$document"; 
                    */
                    $sql = 'UPDATE category SET name=:name,comment=:comment WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $document, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }   
        } 
    }
}
    elseif(!empty($_POST["edit"])&&empty($_POST["del"])){//編集選択モード
        $edit_number=$_POST["edit"];
        $sql = 'SELECT * FROM category';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            if($edit_number==$row["id"]){
            $edit_name =$row["name"];
            $edit_document=$row["comment"];
            }
        }
    }
    elseif(!empty($_POST["pass"])){//削除 
        $del=$_POST["del"];
        $pass=$_POST["pass"];
        
        //全データを取り出す
        $sql = 'SELECT * FROM category';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            if($row["id"]==$del && $row["pass"]==$pass){
               //$delのデータレコードを削除する
                $id = $del;
                $sql = 'delete from category where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute(); //実行
            } 
        }
        
    }
?>
    <form action="" method="post">
        <p><label>追加</label></p>
        <input type="text" name="myname"placeholder="名前"
         value="<?php if(!empty($edit_name)){ echo $edit_name;}?>">
        <input type="text" name="comment"placeholder="コメント"
        value="<?php if(!empty($edit_document)){echo $edit_document;}?>">
        <input type="text" name="pass" placeholder="パスワード">
        <input type="hidden" name="show_edit" 
        value="<?php if(!empty($edit_number)){ echo $edit_number;}?>">
        <input type="submit" name="submit"value="送信">
    </form>
    
    <form action="" method="post">
        <p><label>削除</label></p>
        <input type="delete" name="del" placeholder="投稿番号">
        <input type="text" name="pass" placeholder="パスワード">
        <input type="submit" name="delete" value="削除">
    </form>
    
    <form action="" method="post">
        <p><label>編集</label></p>
        <input type="text" name="edit" placeholder="投稿番号">
        <input type="submit" name="submit_edit" value="送信">
    </form>
    
    <?php
    //表示の処理
    
    //登録したレコードの表示
        $sql = 'SELECT * FROM category';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            echo $row['id'].'<>'.$row['name']
            .'<>'.$row['comment'].'<>'. $row['date'].'<br>';//pass以外   
    }
    ?>   
</body>
</html>