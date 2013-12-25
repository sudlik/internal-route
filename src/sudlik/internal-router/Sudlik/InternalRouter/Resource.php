<?php

namespace Sudlik\InternalRouter;

use finfo;
use SplFileInfo;

class Resource extends SplFileInfo
{
    private $MimeType;
    private $MimeTypeDetector;
    private $RealPath;

    public function __construct($file_name)
    {
        parent::__construct($file_name);

        $this->setOriginalPath($file_name);
        $this->setMimeTypeDetector(new finfo(FILEINFO_MIME_TYPE));
        if ($this->isReadable()) {
            $this->setMimeType($this->setMimeType($this->MimeTypeDetector->file($this->getPathname())));
        }
    }
    
    public function setMimeTypeDetector($MimeTypeDetector)
    {
        $this->MimeTypeDetector = $MimeTypeDetector;
    }
    
    public function getMimeType()
    {
        if (!$this->MimeType) {
            if ($this->isReadable()) {
                $this->setMimeType($this->setMimeType($this->MimeTypeDetector->file($this->getPathname())));
            }
        }
        return $this->MimeType;
    }
    
    private function setMimeType($mime_type)
    {
        $this->MimeType = $mime_type ?: null;
    }

    public function getOriginalPath()
    {
        return $this->OriginalPath;
    }

    private function setOriginalPath($original_path)
    {
        $this->OriginalPath = $original_path;
    }
}