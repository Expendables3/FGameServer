<?php
class DebugLog
{

  private static $m_pInstance;

  public static function getInstance()
  {
    if (!self::$m_pInstance)
    {
        self::$m_pInstance = new DebugLog();
    }

    return self::$m_pInstance;
  }

    private $LogPath;
    private $FileToZip;

    function SetLogPath($LogPath)
    {
        return $this->LogPath = $LogPath;
    }

    function Debug($logme)
    {
        //$this->ZipArcv();
        $date = "";
        $CurrentDate = $_SERVER['REMOTE_ADDR'].'___'.date("m.d.y") . ".log";
        $date .= date("m.d.y") ." :: ". date("H:i:s");
        $logme = $date . " - " . $logme . "\r\n";

        try
        {
            file_put_contents($this->LogPath . $CurrentDate, $logme, FILE_APPEND);
        }
        catch(Exception $fault)
        {
        }

    }
    function ZipArcv()
    {
        $FilesToZip  = $this->GetFileListToZip();

        $zip = new ZipArchive;
        foreach($FilesToZip as $files)
        {
         $ZipOpen  = $this->LogPath . basename($files, ".log") . ".zip";
        if ($zip->open($ZipOpen ,  ZipArchive::CREATE) === TRUE) {
        $zip->addFile($files, basename($files, ".log") . ".log");
        $zip->close();

        }
        else {
        }
        }
            $this->dest(); // delete ziped files

    }
    //if it's a past year or if it's this year and a past month, add the path to $FileToZip

    function GetFileListToZip()
    {
    $FileToZip = array();
    $dir = $this->LogPath;
    foreach (glob("$dir/*.log") as $path)
    {
        list($m, $d, $y) = explode('.', basename($path, '.log'));
        $y = (int) $y < 70 ? (int) "20$y" : (int) "19$y";

        if ($y < (int) date('Y') || ($y == (int) date('Y') && (int) $m < (int) date('m')))
            {

                $FileToZip[] = $path;
            }

    }
        $this->FileToZip = $FileToZip;
        return $FileToZip;
    }
    function dest()
    {

        // deleted file was Ziped
            foreach($this->FileToZip as $File)
            {
            unlink($File);
            }


    }
}

?>
