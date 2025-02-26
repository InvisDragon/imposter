<?php

declare(strict_types=1);

namespace TypistTech\Imposter;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RuntimeException;

class Filesystem implements FilesystemInterface
{
    /**
     * @param string $path
     *
     * @return \SplFileInfo[]
     * @throws \UnexpectedValueException
     */
    public function allFiles(string $path): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                function ($current, $key, $iterator) {
                    if ($iterator->hasChildren()) {
                        return preg_match(
                            '/[\._](svn|CVS|arch-params|monotone|bzr|git|hg)|CVS|_darcs/',
                            $current->getFilename()
                        ) === 0;
                    }
                    return preg_match('/\.php$/i', $current->getFileName()) === 1;
                }
            )
        );

        return iterator_to_array($iterator);
    }

    public function filter($current, $key, $iterator)
    {

        return $current->isFile() && preg_match('/.php$/i', $current->getFilename());
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     *
     * @return string
     * @throws \RuntimeException
     */
    public function get(string $path): string
    {
        if (!$this->isFile($path)) {
            throw new RuntimeException('File does not exist at path ' . $path);
        }

        return file_get_contents($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     *
     * @return int|false
     */
    public function put(string $path, string $contents)
    {
        return file_put_contents($path, $contents);
    }
}
