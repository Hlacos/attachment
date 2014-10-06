<?php

namespace Hlacos\Attachment;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Config;

class Attachment extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    public $path;

    public function attachable() {
        return $this->morphTo();
    }

    public function save(array $options = array()) {
        parent::save($options);

        $this->moveFile($this->path);
    }

    public function addFile($path) {
        $this->path = $path;
        $this->filename = pathinfo($path, PATHINFO_FILENAME);
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        $this->size = filesize($path);
        $this->file_type = mime_content_type($path);
    }

    public function moveFile($path) {
        if (!file_exists(public_path().$this->basePath())) {
            mkdir(public_path().$this->basePath());
        }

        rename($path, $this->publicPath());
    }

    public function publicPath() {
        return public_path().$this->publicFilename();
    }

    public function publicUrl() {
        return asset($this->publicFilename());
    }

    private function basePath() {
        //TODO: könyvtárszerkezetet módosítani, esetleg uuid-s megoldással.
        return Config::get('attachment::attachment.folder').$this->id.'/';
    }

    private function publicFilename() {
        return $this->basePath().$this->baseFilename();
    }

    public function baseFilename() {
        return $this->filename.'.'.$this->extension;
    }
}
