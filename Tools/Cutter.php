<?php

namespace Msi\CmfBundle\Tools;

class Cutter
{
    private $file;

    private $original;
    private $originalW;
    private $originalH;

    private $resized;

    private $qualityPng = 1;
    private $qualityJpg = 100;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;

        if (!$this->file->isFile()) {
            throw new \InvalidArgumentException($this->file.' is not a file');
        }

        switch ($this->file->getExtension()) {
            case 'png';
                $this->original = imagecreatefrompng($this->file);
                break;
            case 'gif';
                $this->original = imagecreatefromgif($this->file);
                break;
            default:
                $this->original = imagecreatefromjpeg($this->file);
        }

        $this->originalW = imagesx($this->original);
        $this->originalH = imagesy($this->original);
    }

    public function resizeProp($size)
    {
        if ($this->originalW > $this->originalH) {
            $width = $size;
            $height = $size * $this->originalH / $this->originalW;
        } else {
            $height = $size;
            $width = $size * $this->originalW / $this->originalH;
        }

        $this->resized = imagecreatetruecolor($width, $height);

        imagecopyresampled($this->resized, $this->original, 0, 0, 0, 0, $width, $height, $this->originalW, $this->originalH);

        return $this;
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->originalW;
        $height = $this->originalH * $ratio;

        $this->resized = imagecreatetruecolor($width, $height);

        imagecopyresampled($this->resized, $this->original, 0, 0, 0, 0, $width, $height, $this->originalW, $this->originalH);

        return $this;
    }

    public function resize($width, $height)
    {
        $srcRatio = $this->originalW / $this->originalH;
        $dstRatio = $width / $height;

        // Resizing
        if ($srcRatio > $dstRatio) {
            $ratio = $height / $this->originalH;
            $h = $this->originalH * $ratio;
            $w = $this->originalW * $ratio;
        } else {
            $ratio = $width / $this->originalW;
            $h = $this->originalH * $ratio;
            $w = $this->originalW * $ratio;
        }

        // Cropping
        $x = ($w - $width) / -2;
        $y = ($h - $height) / -2;

        $this->resized = imagecreatetruecolor($width, $height);

        imagecopyresampled($this->resized, $this->original, $x, $y, 0, 0, $w, $h, $this->originalW, $this->originalH);

        return $this;
    }

    public function watermark(\SplFileInfo $watermarkFile)
    {
        switch ($watermarkFile->getExtension()) {
            case 'png';
                $watermark = imagecreatefrompng($watermarkFile);
                break;
            case 'gif';
                $watermark = imagecreatefromgif($watermarkFile);
                break;
            default:
                $watermark = imagecreatefromjpeg($watermarkFile);
        }

        $x1 = imagesx($this->original);
        $y1 = imagesy($this->original);
        $x2 = imagesx($watermark);
        $y2 = imagesy($watermark);

        imagecopyresampled($this->original, $watermark, 0, 0, 0, 0, $x1, $y1, $x2, $y2);

        return $this;
    }

    // 0-100
    public function setQuality($a)
    {
        $this->qualityJpg = $a;
        $this->qualityPng = round(abs((9 * $a) / 100 - 9));
    }

    public function save($prefix = null)
    {
        $filename = $prefix ? $this->file->getPath().'/'.$prefix.$this->file->getFilename() : $this->file->getPathname();

        switch ($this->file->getExtension()) {
            case 'png':
                return imagepng($this->resized, $filename, $this->qualityPng);
                break;
            case 'gif':
                return imagegif($this->resized, $filename);
                break;
            default:
                return imagejpeg($this->resized, $filename, $this->qualityJpg);
        }

        imagedestroy($this->resized);
        imagedestroy($this->original);
    }
}
