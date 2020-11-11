<?php

declare(strict_types = 1);

namespace Chapterphp\FileSystem\Model;

use Chapterphp\FileSystem\FileInterface;
use Exception;
use SplFileInfo;
use SplFileObject;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class File implements FileInterface
{
    private $fileInfo;
    private $fileName;
    private $mimeType;

    public function __construct(SplFileInfo $fileInfo, FileName $fileName, ?string $mimeType = null)
    {
        $this->fileInfo = $fileInfo;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
    }

    public static function createFromFileInfo(SplFileInfo $fileInfo): self
    {
        return new self($fileInfo, FileName::create($fileInfo->getBasename()), null);
    }

    public static function createFromUpload(UploadedFile $uploadedFile): self
    {
        $name = $uploadedFile->getClientOriginalName() ?? $uploadedFile->getFilename();

        return new self($uploadedFile, FileName::createUnique($name), $uploadedFile->getMimeType());
    }

    public static function createTempFile(FileName $fileName, string $body, ?string $mimeType = null): self
    {
        $tempFileObject = new SplTempFileObject(0);
        $tempFileObject->fwrite($body);

        return new self($tempFileObject, $fileName, $mimeType);
    }

    public function getFileName(): FileName
    {
        return $this->fileName;
    }

    public function getFileMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getFileContent(): ?string
    {
        if ($this->fileInfo instanceof SplFileObject) {
            return $this->getContentFromStream();
        }

        return $this->getContentFromLocation();
    }

    public function getFileLocation(): ?string
    {
        $realPath = $this->fileInfo->getRealPath();
        if (false !== $realPath && file_exists($realPath)) {
            return $realPath;
        }

        $fileName = $this->fileInfo->getFilename();
        if (file_exists($fileName)) {
            return $fileName;
        }

        return null;
    }

    public function delete(): bool
    {
        if (!$this->fileInfo->isWritable()) {
            return false;
        }

        return unlink($this->fileInfo->getRealPath());
    }

    private function getContentFromStream(): string
    {
        $content = '';
        $this->fileInfo->rewind();
        while (!$this->fileInfo->eof()) {
            $content .= $this->fileInfo->fgets();
        }

        return $content;
    }

    private function getContentFromLocation(): ?string
    {
        $location = $this->getFileLocation();
        if (null === $location) {
            return null;
        }

        $content = file_get_contents($location);
        if (false === $content) {
            throw new Exception(sprintf('Read the content from %s failed!', $location));
        }

        return $content;
    }
}
