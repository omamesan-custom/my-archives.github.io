<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-01</title>
</head>
<body>
    
    <h1>
        <p>コメントを投稿しよう！</p>
    </h1>
    
    <?php
        // DB接続設定
        $dsn = 'mysql';
        $user = 'user';
        $password = 'passwaord';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        //テーブル名の定義
        define("TAB", "sometable");
        
        
        
        //テキストファイルの更新
        if(isset($_POST["name"]) && isset($_POST["comment"]) && isset($_POST["pass"])){
            if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass"])){
                $name = $_POST["name"];
                $comment = $_POST["comment"];
                $pass = $_POST["pass"];
                
                if(empty($_POST["nore"])){
                    //新規投稿
                    $sql = "CREATE TABLE IF NOT EXISTS " . TAB
                    ." ("
                    . "id INT AUTO_INCREMENT PRIMARY KEY,"
                    . "name CHAR(32),"
                    . "comment TEXT,"
                    ."pass CHAR(32),"
                    ."dt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                    .");";
                    $stmt = $pdo->query($sql);
                    
                    //$date = date("YYYYmmddHHiiss");
                    //date_default_timezone_set('Asia/Tokyo');
                    
                    $sql = "INSERT INTO " . TAB . " (name, comment, pass) VALUES (:name, :comment, :pass)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->execute();
                    
                }else{
                    $eid = $_POST["nore"];
                    //投稿の編集
                    $sql = "UPDATE " . TAB . " SET name= :name, comment= :comment, pass= :pass WHERE id= :eid";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
                    $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                    $stmt->bindParam(":eid", $eid, PDO::PARAM_INT);
                    $stmt->execute();
                    
                }
                
                $eid = "";
                $name = "";
                $comment = "";
                $pass = "";
                
                
                
            }else{
                echo "正しく入力してください。<br>";
            }
            
        }else if(isset($_POST["did"]) && isset($_POST["dpass"])){
            //削除フォーム
            if(!empty($_POST["did"]) && !empty($_POST["dpass"])){
                $did = $_POST["did"];
                $dpass = $_POST["dpass"];
                
                $checkTable = $pdo->query("SHOW TABLES LIKE '" . TAB . "'");
                if ($checkTable->rowCount() > 0) {
                    
                    $sql = "SELECT * FROM " . TAB . " WHERE id= :did AND pass= :dpass LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":did", $did, PDO::PARAM_INT);
                    $stmt->bindParam(":dpass", $dpass, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch();
                    
                    if($result != NULL){
                        //削除の処理
                        $sql = "DELETE FROM " . TAB . " WHERE id= :did AND pass= :dpass";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":did", $did, PDO::PARAM_INT);
                        $stmt->bindParam(":dpass", $dpass, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        echo "削除が完了しました。<br>";
                        
                    }else{
                        echo "指定したデータが存在しないかパスワードが間違っています。<br>";
                    }
                    
                } else {
                    echo "テーブルが存在しません。";
                }
                
            }else{
                echo "正しく入力してください。<br>";
            }
            
        }else if(isset($_POST["eid"]) && isset($_POST["epass"])){
            //編集フォームの処理
            if(!empty($_POST["eid"]) && !empty($_POST["epass"])){
                $eid = $_POST["eid"];
                $epass = $_POST["epass"];
                
                $checkTable = $pdo->query("SHOW TABLES LIKE '" . TAB . "'");
                if ($checkTable->rowCount() > 0) {
                    
                    $sql = "SELECT * FROM " . TAB . " WHERE id= :eid AND pass= :epass LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":eid", $eid, PDO::PARAM_INT);
                    $stmt->bindParam(":epass", $epass, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch();
                    
                    if($result != NULL){
                        //編集する投稿を表示する処理
                        $eid = $result[0];
                        $name = $result[1];
                        $comment = $result[2];
                        $pass = $result[3];
                        
                    }else{
                        echo "指定したデータが存在しないかパスワードが間違っています。<br>";
                    }
                    
                } else {
                    echo "テーブルが存在しません。";
                }
                
                
                
            }else{
                    echo "正しく入力してください。<br>";
            }
        }
        
        
        //テーブルの内容を出力
        $checkTable = $pdo->query("SHOW TABLES LIKE '" . TAB . "'");
        if ($checkTable->rowCount() > 0) {
            // テーブルが存在する場合は内容を取得して表示
            $sql = "SELECT * FROM " . TAB;
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                // テーブルのカラムを表示
                echo "ID:" . $row['id'] . '__';
                echo "NAME:" . $row['name'] . '__';
                echo "COMMENT:" . $row['comment'] . '__';
                echo "PASSWORD:" . $row['pass'] . '__';
                echo $row['dt'];
                echo "<hr>";
            }
        } else {
            echo "テーブルが存在しません。";
        }
        
        
    ?>
    
    
    <form action="" method="post">
    <p>
        <input type="hidden" name="nore" value="<?php echo isset($eid) ? $eid : ""; ?>">
    </p>
    <p>
        名前:<br>
        <input type="text" name="name" placeholder="名前" value="<?php echo isset($name) ? $name : ""; ?>">
    </p>
    <p>
        コメント:<br>
        <input type="text" name="comment" placeholder="コメント" value="<?php echo isset($comment) ? $comment : ""; ?>">
    </p>
    <p>
        パスワード:<br>
        <input type="password" name="pass" placeholder="パスワード" value="<?php echo isset($pass) ? $pass : ""; ?>">
    </p>
    <input type="submit" name="submit" value="送信">
    </form>
    <form action="" method="post">
        <p>
            削除したい番号:<br>
            <input type="number" name="did" placeholder="削除したい番号">
        </p>
        <p>
            パスワード:<br>
            <input type="password" name="dpass" placeholder="パスワード">
        </p>
        <input type="submit" name="dsubmit" value="削除">
    </form>
    <form action="" method="post">
        <p>
            編集したい番号:<br>
            <input type="number" name="eid" placeholder="編集したい番号">
        </p>
        <p>
            パスワード:<br>
            <input type="password" name="epass" placeholder="パスワード">
        </p>
        <input type="submit" name="esubmit" value="編集">
    </form>
    
</body>
</html>