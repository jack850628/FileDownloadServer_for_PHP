<?php
class Path{
    public static $rootPath="file";
    public static $folderPath;
    public static function getFolderPath(){
        return Path::$folderPath != "/" ? Path::$folderPath : "";
    }
    public static function setFolderPath($path){
        Path::$folderPath = $path != "/" ? $path : "/";
    }
}
class ActionMode{
    const OTHER = -1,DELETE_FILE = 0, DELETE_FOLDER = 1,PATH_NOT_EXIST=2;
}
class FileListButtonAction{
    const UP_FOLDER=0,RENAME=1,DOWNLOAD=2,DELETE_FOLDER=3,DELETE_FILE=4,
        MOVE_FOLDER=5,COPY_FOLDER=6,MOVE_FILE=7,COPY_FILE=8,MOVE_TO_HERE=9,CANCEL=10,EDIT_FILE=11;
}
class MessageBox{
    public static $title,$message,$temp;
    public static $actionMode;
    public static $show=false;
    public static function setMessage($title,$message,$actionMode,$temp=""){
        MessageBox::$title=$title;
        MessageBox::$message=$message;
        MessageBox::$actionMode=$actionMode;
        MessageBox::$temp=$temp;
        MessageBox::$show=true;
    }
}
class DirectoryOperating{
    static function copydirectory($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    static function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!DirectoryOperating::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}