<?php
    require_once("./classes.php");
    if($_POST["type"]=="editFile"){
        if($_POST["buttonAction"]=="senv"){
            $file=@fopen(__DIR__."/".Path::$rootPath.$_POST["path"]."/".$_POST["fileName"],"w");
                if($file){
                    fwrite($file,$_POST["fileContent"]);
                }
            fclose($file);
        }
        header("Location:./file_Server.php?path={$_POST["path"]}");
        exit(0);
    }
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>編輯檔案</title>
</head>
<body>
    <form action="./edit_file.php" method="post">
        <h2>檔名:<?=$_POST["fileName"]?></h2>
        <input type="hidden" name="type" value="editFile"/>
        <input type="hidden" name="buttonAction"/>
        <input type="hidden" name="path" value="<?=$_POST["path"]?>"/>
        <input type="hidden" name="fileName" value="<?=$_POST["fileName"]?>"/>
        <textarea name="fileContent" style="width:100%;height:90%"><?php
                $file=@fopen(__DIR__."/".Path::$rootPath.$_POST["path"]."/".$_POST["fileName"],"r");
                if($file){
                    $text;
                    while($text=fgets($file,4096)){
                        echo $text;
                    }
                }
                fclose($file);
            ?></textarea>
        <br/>
        <center>
            <input type="submit" onclick="this.form.buttonAction.value='senv';" value="儲存"/>
            <input type="submit" onclick="this.form.buttonAction.value='cancel';" value="取消"/>
        </center>
    </form>
</body>
</html>