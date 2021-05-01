<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>簡易式檔案伺服器</title>
    <style>
        form:not(#messageBox){
            display: inline;
        }
        #messageBox{
            background-color: #FFFFCC;
        }
    </style>
    <?php
        require_once("./classes.php");
        if(!is_dir("./".Path::$rootPath))
            mkdir("./".Path::$rootPath);
        $moveOrCopy=false;
        if(isset($_GET["path"])){
            if(preg_match("/^$|^\\s+$/",$_GET["path"])){
                Path::setFolderPath("/");
            }else if(preg_match("/^.*\\.\\..*$/",$_GET["path"])){
                MessageBox::setMessage("錯誤","路徑中不允許包含'..'",ActionMode::PATH_NOT_EXIST);
                Path::setFolderPath("/");
            }else if(!is_dir(__DIR__."/".Path::$rootPath.$_GET["path"])){
                MessageBox::setMessage("錯誤","路徑'{$_GET["path"]}'不存在",ActionMode::PATH_NOT_EXIST);
                Path::setFolderPath("/");
            }else
                Path::setFolderPath($_GET["path"]);
        }else
            Path::setFolderPath("/");
        if(isset($_POST["type"]))
            switch($_POST["type"]){
                case "upFile":{
                    if(sizeof($_FILES)!=0)
                        foreach($_FILES as $file)
                            if ($file["error"] == 0){
                                move_uploaded_file($file["tmp_name"],__DIR__."/".Path::$rootPath.Path::getFolderPath()."/".$file["name"]);
                            }
                    break;
                }
                case "createFolder":{
                    if(!preg_match("/^$|^\\s+$/",$_POST["folderName"])){
                        mkdir(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/".$_POST["folderName"]);
                    }else
                        MessageBox::setMessage("錯誤","資料夾名稱不可以為空白",ActionMode::OTHER);
                    break;
                }
                case "messageBox":{
                    switch(intval($_POST["actionMode"])){
                        case ActionMode::DELETE_FILE:{
                            if($_POST["buttonAction"]=="ok")
                                if(!@unlink(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_POST["temp"]}"))
                                    MessageBox::setMessage("錯誤","檔案'{$_POST["temp"]}'刪除失敗",ActionMode::OTHER);
                            break;
                        }
                        case ActionMode::DELETE_FOLDER:{
                            if($_POST["buttonAction"]=="ok"){
                                if(!@DirectoryOperating::deleteDirectory(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_POST["temp"]}"))
                                    MessageBox::setMessage("錯誤","資料夾'{$_POST["temp"]}'刪除失敗",ActionMode::OTHER);
                            }
                            break;
                        }
                        case ActionMode::PATH_NOT_EXIST:{
                            header("Location:./file_Server.php?path=/");
                        }
                    }
                    break;
                }
                case "fileList":{
                    switch(intval($_POST["buttonAction"])){
                        case FileListButtonAction::UP_FOLDER:
                            header("Location:./file_Server.php?path=".
                                preg_replace("/&/","%26",
                                    preg_replace("/^(.*?)[\\/\\\\]".
                                        preg_quote(
                                                end(
                                                    explode("/", Path::getFolderPath())
                                                )
                                        )."[\\/\\\\]?$/","$1",Path::getFolderPath()
                                    )
                                )
                            );
                            break;
                        case FileListButtonAction::RENAME:
                            rename(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_POST["oldName"]}",
                                    __DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_POST["newName"]}");
                            break;
                        case FileListButtonAction::DELETE_FILE:
                            MessageBox::setMessage("確認","確定要刪除檔案'{$_POST["fileName"]}'?",ActionMode::DELETE_FILE,$_POST["fileName"]);
                            break;
                        case FileListButtonAction::DELETE_FOLDER:
                            MessageBox::setMessage("確認","確定要刪除資料夾'{$_POST["fileName"]}'?",ActionMode::DELETE_FOLDER,$_POST["fileName"]);
                            break;
                        case FileListButtonAction::MOVE_TO_HERE:{
                            switch($_COOKIE["moveOrCopyActionType"]){
                                case FileListButtonAction::MOVE_FOLDER:{
                                    if(!@rename(__DIR__."/{$_COOKIE["fileOrFloderPath"]}/{$_COOKIE["fileOrFloderName"]}", __DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_COOKIE["fileOrFloderName"]}")){
                                        MessageBox::setMessage("錯誤","資料夾'{$_COOKIE["fileOrFloderName"]}'移動失敗",ActionMode::OTHER);
                                    }
                                    break;
                                }
                                case FileListButtonAction::COPY_FOLDER:{
                                    DirectoryOperating::copydirectory(__DIR__."/{$_COOKIE["fileOrFloderPath"]}/{$_COOKIE["fileOrFloderName"]}", __DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_COOKIE["fileOrFloderName"]}");
                                    break;
                                }
                                case FileListButtonAction::MOVE_FILE:{
                                    if(@copy(__DIR__."/{$_COOKIE["fileOrFloderPath"]}/{$_COOKIE["fileOrFloderName"]}", __DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_COOKIE["fileOrFloderName"]}")){
                                        if($_COOKIE["fileOrFloderPath"] != Path::$rootPath.Path::getFolderPath())
                                            unlink(__DIR__."/{$_COOKIE["fileOrFloderPath"]}/{$_COOKIE["fileOrFloderName"]}");
                                    }else
                                        MessageBox::setMessage("錯誤","檔案'{$_COOKIE["fileOrFloderName"]}'移動失敗",ActionMode::OTHER);
                                    break;
                                }
                                case FileListButtonAction::COPY_FILE:
                                {
                                    if(!@copy(__DIR__."/{$_COOKIE["fileOrFloderPath"]}/{$_COOKIE["fileOrFloderName"]}", __DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$_COOKIE["fileOrFloderName"]}")){
                                        MessageBox::setMessage("錯誤","檔案'{$_COOKIE["fileOrFloderName"]}'複製失敗",ActionMode::OTHER);
                                    }
                                    break;
                                }
                            }
                            setcookie("moveOrCopy",null,-1);
                            setcookie("moveOrCopyActionType",null,-1);
                            setcookie("fileOrFloderPath",null,-1);
                            setcookie("fileOrFloderName",null,-1);
                            break;
                        }
                        case FileListButtonAction::CANCEL:{
                            setcookie("moveOrCopy",null,-1);
                            setcookie("moveOrCopyActionType",null,-1);
                            setcookie("fileOrFloderPath",null,-1);
                            setcookie("fileOrFloderName",null,-1);
                            $moveOrCopy=false;
                            break;
                        }
                        case FileListButtonAction::EDIT_FILE:{
                            ob_clean();
                            $_POST["path"]=Path::getFolderPath();
                            require("./edit_file.php");
                            exit(0);
                            break;
                        }
                        case FileListButtonAction::MOVE_FOLDER:
                        case FileListButtonAction::COPY_FOLDER:
                        case FileListButtonAction::MOVE_FILE:
                        case FileListButtonAction::COPY_FILE:
                        {
                            setcookie("moveOrCopy",$moveOrCopy=true);
                            setcookie("moveOrCopyActionType",intval($_POST["buttonAction"]));
                            setcookie("fileOrFloderPath",Path::$rootPath.Path::getFolderPath());
                            setcookie("fileOrFloderName",$_POST["fileName"]);
                            break;
                        }
                    }
                    break;
                }
            }
        else
            setcookie("moveOrCopy",$moveOrCopy=$moveOrCopy||isset($_COOKIE["moveOrCopy"])&&$_COOKIE["moveOrCopy"]);
    ?>
</head>
<body>
    <h2>檔案清單</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="type" value="upFile">
        <input type="file" multiple="multiple" name="FileUpload" id="FileUpload" Text="上傳檔案" />
        <input type='submit' value='上傳檔案'/>
    </form>
    &nbsp;&nbsp;
    <form method="post" action="">
        <input type="hidden" name="type" value="createFolder">
        資料及名稱:
        <input name="folderName" type="text" id="folderName" />
        <input type="submit" name="createFolder" value="建立資料夾" id="createFolder" />
    </form>
    <form style="display:<?=MessageBox::$show?'block':'none'?>" method="post" id="messageBox" action="" onsubmit="console.log(this.buttonAction);">
        <fieldset>
            <legend>
                <?=MessageBox::$title?>
            </legend>
            <center>
                <span><?=MessageBox::$message?></span>
                <br/>
                <input type="hidden" name="type" value="messageBox">
                <input type="hidden" name="actionMode" value="<?=MessageBox::$actionMode?>"/>
                <input type="hidden" name="buttonAction"/>
                <input type="hidden" name="temp" value='<?=MessageBox::$temp?>'/>
                <input type="submit" onclick="this.form.buttonAction.value='ok';" value="確定"/>
                <input type="submit" onclick="this.form.buttonAction.value='cancel';" value="取消"/>
            </center>
        </fieldset>
    </form>
    <script>
    var a;
    </script>
    <fieldset>
		<legend>
			<h3>路徑:<?=Path::$folderPath?></h3>
		</legend>
        <form method="post" action="">
            <input type="hidden" name="type" value="fileList"/>
            <input type="hidden" name="buttonAction" value="-1"/>
            <input type="hidden" name="fileName"/>
            <input type="hidden" name="oldName"/>
            <input type="submit" style="display:<?=Path::$folderPath=="/"?"none":"block"?>;" value="上一層" onclick="this.form.buttonAction.value=<?=FileListButtonAction::UP_FOLDER?>;" />
            <?php
                if($moveOrCopy){
                    echo '<input type="submit" value="放置到此" onclick="this.form.buttonAction.value='.FileListButtonAction::MOVE_TO_HERE.';" />';
                    echo '<input type="submit" value="取消" onclick="this.form.buttonAction.value='.FileListButtonAction::CANCEL.';" />';
                }
            ?>
            <table>
                <tr>
                    <td><span style="font-weight:800;">檔案名稱</span></td>
                </tr>
                <?php
                    $files=scandir(__DIR__."/".Path::$rootPath.Path::getFolderPath());
                    if(count($files)==2)
                        echo "<td><span>目錄是空的</span></td>";
                    else if($moveOrCopy){
                        for($i=2;$i<count($files);$i++){
                            if(is_dir(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$files[$i]}")){
                ?>
                    <tr>
                        <td>
                            <a href="./file_Server.php?path=<?=preg_replace("/&/","%26",Path::getFolderPath()."/".$files[$i])?>"><?=$files[$i]?></a>
                        </td>
                    </tr>
                <?php
                            }
                        }
                    }else{
                        for($i=2;$i<count($files);$i++){
                            if(is_dir(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$files[$i]}")){
                                if(!isset($_POST["oldName"])||$_POST["oldName"]!=$files[$i]){
                ?>
                    <tr>
                        <td>
                            <a href="./file_Server.php?path=<?=preg_replace("/&/","%26",Path::getFolderPath()."/".$files[$i])?>"><?=$files[$i]?></a>
                            <input type="submit" onclick="this.form.oldName.value='<?=$files[$i]?>';" value="重新命名"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::COPY_FOLDER?>;" value="複製"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::MOVE_FOLDER?>;" value="移動"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::DELETE_FOLDER?>;" value="刪除"/>
                        </td>
                    </tr>
                <?php
                                }else{
                ?>
                    <tr>
                        <td>
                            <input type="text" name="newName" value="<?=$files[$i]?>"/>
                            <input type="submit" onclick="this.form.oldName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::RENAME?>;" value="確定"/>
                        </td>
                    </tr>
                <?php
                                }
                            }
                        }
                        for($i=2;$i<count($files);$i++){
                            if(!is_dir(__DIR__."/".Path::$rootPath.Path::getFolderPath()."/{$files[$i]}")){
                                if(!isset($_POST["oldName"])||$_POST["oldName"]!=$files[$i]){
                ?>
                    <tr>
                        <td>
                            <a href="<?="./".Path::$rootPath.Path::getFolderPath()."/".$files[$i]?>" target="_block"><?=$files[$i]?></a>
                            <!--<input type="submit" onclick="this.form.buttonAction.value=<?=FileListButtonAction::DOWNLOAD?>;" value="下載"/>-->
                            <input type="submit" onclick="this.form.oldName.value='<?=$files[$i]?>';" value="重新命名"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::COPY_FILE?>;" value="複製"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::MOVE_FILE?>;" value="移動"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::EDIT_FILE?>;" value="編輯"/>
                            <input type="submit" onclick="this.form.fileName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::DELETE_FILE?>;" value="刪除"/>
                        </td>
                    </tr>
                <?php
                                }else{
                ?>
                    <tr>
                        <td>
                            <input type="text" name="newName" value="<?=$files[$i]?>"/>
                            <input type="submit" onclick="this.form.oldName.value='<?=$files[$i]?>';this.form.buttonAction.value=<?=FileListButtonAction::RENAME?>;" value="確定"/>
                        </td>
                    </tr>
                <?php
                                }
                            }
                        }
                    }
                ?>
            </table>
        </form>
	</fieldset>
</body>
</html>