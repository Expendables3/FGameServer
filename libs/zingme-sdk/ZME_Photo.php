<?php

/**
 * Copyright 2013 ZingMe
 * 
 */
class ZME_Photo extends BaseZingMe {

    private $upload_path = "photo/upload/@%s";

    public function __construct($config) {
        parent::__construct($config);
    }

    /**
     * send photo into app game Zing me with access_token, file and description
     *
     * @param type $access_token, $file, $description
     * @return info about photo uploaded: photo_url, photo_src, id, album ...
     */
    public function upload($access_token, $file, $description) {
        $path = sprintf($this->upload_path, $this->appname);

        $url = $this->getUrl("photo", $path, '');

        $data = $this->sendRequestPhoto($url, $access_token, $file, $description);
        return $data;
    }
}

?>
