<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use Symfony\Component\Finder\SplFileInfo;

/**
 * @method string getPath() https://php.net/manual/en/splfileinfo.getpath.php
 * @method string getFilename() https://php.net/manual/en/splfileinfo.getfilename.php
 * @method string getBasename(?string $suffix = null) https://php.net/manual/en/splfileinfo.getbasename.php
 * @method string getPathname() https://php.net/manual/en/splfileinfo.getpathname.php
 * @method int getPerms() https://php.net/manual/en/splfileinfo.getperms.php
 * @method int getInode() https://php.net/manual/en/splfileinfo.getinode.php
 * @method int getSize() https://php.net/manual/en/splfileinfo.getsize.php
 * @method int getOwner() https://php.net/manual/en/splfileinfo.getowner.php
 * @method int getGroup() https://php.net/manual/en/splfileinfo.getgroup.php
 * @method int getATime() https://php.net/manual/en/splfileinfo.getatime.php
 * @method int getMTime() https://php.net/manual/en/splfileinfo.getmtime.php
 * @method int getCTime() https://php.net/manual/en/splfileinfo.getctime.php
 * @method string getType() https://php.net/manual/en/splfileinfo.gettype.php
 * @method bool isWritable() https://php.net/manual/en/splfileinfo.iswritable.php
 * @method bool isReadable() https://php.net/manual/en/splfileinfo.isreadable.php
 * @method bool isExecutable() https://php.net/manual/en/splfileinfo.isexecutable.php
 * @method bool isFile() https://php.net/manual/en/splfileinfo.isfile.php
 * @method bool isDir() https://php.net/manual/en/splfileinfo.isdir.php
 * @method bool isLink() https://php.net/manual/en/splfileinfo.islink.php
 * @method string getLinkTarget() https://php.net/manual/en/splfileinfo.getlinktarget.php
 * @method string getRealPath() https://php.net/manual/en/splfileinfo.getrealpath.php
 * @method SplFileInfo getFileInfo(?string $class_name) https://php.net/manual/en/splfileinfo.getfileinfo.php
 * @method SplFileInfo getPathInfo(?string $class_name) https://php.net/manual/en/splfileinfo.getpathinfo.php
 * @method SplFileInfo openFile(string $open_mode, bool $use_include_path = false, ?resource $context = null) https://php.net/manual/en/splfileinfo.openfile.php
 * @method void setFileClass(?string $class_name) https://php.net/manual/en/splfileinfo.setfileclass.php
 * @method void setInfoClass(?string $class_name) https://php.net/manual/en/splfileinfo.setinfoclass.php
 *
 * @method string getRelativePath() \Symfony\Component\Finder\SplFileInfo::getRelativePath
 * @method string getRelativePathname() \Symfony\Component\Finder\SplFileInfo::getRelativePathname
 * @method string getContents() \Symfony\Component\Finder\SplFileInfo::getContents
 */
class InputFile
{
    /** @var SplFileInfo */
    protected $file;

    /** @var string[] */
    protected $extraBladeExtensions = [
        'js', 'json', 'xml', 'rss', 'atom', 'txt', 'text', 'html',
    ];

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function topLevelDirectory(): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->file->getRelativePathName());

        return count($parts) == 1 ? '' : $parts[0];
    }

    public function getFilenameWithoutExtension(): string
    {
        return $this->getBasename('.' . $this->getFullExtension());
    }

    public function getExtension(): ?string
    {
        if (! starts_with($this->getFilename(), '.')) {
            return $this->file->getExtension();
        }

        return null;
    }

    public function getFullExtension(): ?string
    {
        return $this->isBladeFile() ? 'blade.' . $this->getExtension() : $this->getExtension();
    }

    public function getExtraBladeExtension(): string
    {
        return $this->isBladeFile() && in_array($this->getExtension(), $this->extraBladeExtensions) ? $this->getExtension() : '';
    }

    public function getLastModifiedTime(): int
    {
        return $this->file->getMTime();
    }

    public function isBladeFile(): bool
    {
        return strpos($this->getBasename(), '.blade.' . $this->getExtension()) > 0;
    }

    /**
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->file->{$method}(...$args);
    }
}
