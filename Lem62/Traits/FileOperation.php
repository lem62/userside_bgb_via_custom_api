<?php

namespace Lem62\Traits;

trait FileOperation
{
    public function filePutContent($file, $content, $append = true)
    {
        if (!$this->validateFileName($file)) {
            return;
        }
        if ($append) {
            file_put_contents($file, $content . "\n", FILE_APPEND);
        } else {
            file_put_contents($file, $content . "\n");
        }
    }

    public function fileGetContent($file)
    {
        if (!$this->validateFileName($file)) {
            return false;
        }
        if (!file_exists($file)) {
            return false;
        }
        return file_get_contents($file);
    }

    public function fileRemove($file)
    {
        if (!$this->validateFileName($file)) {
            return false;
        }
        return unlink($file);
    }

    private function validateFileName($fileName)
    {
        if (empty($fileName)) {
            return false;
        }
        if (preg_match("/(php|gitignore|gitkeep|env)$/", $fileName)) {
            return false;
        }
        if (preg_match("/(\.git)/", $fileName)) {
            return false;
        }
        return true;
    }
}