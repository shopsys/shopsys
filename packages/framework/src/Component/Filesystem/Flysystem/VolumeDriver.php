<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem\Flysystem;

use Barryvdh\elFinderFlysystemDriver\Driver;
use elFinder;

class VolumeDriver extends Driver
{
    protected function configure()
    {
        @parent::configure();

        $thumbnailPath = $this->options['tmbPath'];

        if (!$thumbnailPath) {
            return;
        }

        if (!$this->fs->has($thumbnailPath)) {
            if ($this->_mkdir($thumbnailPath, '')) {
                $this->_chmod($thumbnailPath, $this->options['tmbPathMode']);
            } else {
                $thumbnailPath = '';
            }
        }

        $stat = $this->_stat($thumbnailPath);

        if (!$this->_dirExists($thumbnailPath) || !$stat['read']) {
            return;
        }

        $this->tmbPath = $thumbnailPath;
        $this->tmbPathWritable = $stat['write'];
    }

    /**
     * @param string $hash
     * @return false|string
     */
    public function tmb($hash)
    {
        $thumbnailPath = $this->decode($hash);
        $stat = $this->stat($thumbnailPath);

        if (isset($stat['tmb'])) {
            $res = (string)$stat['tmb'] === '1' ? $this->createTmb($thumbnailPath, $stat) : $stat['tmb'];

            if (!$res) {
                [$type] = explode('/', $stat['mime']);
                $fallback = $this->options['resourcePath'] . DIRECTORY_SEPARATOR . strtolower($type) . '.png';

                if (is_file($fallback)) {
                    $res = $this->tmbname($stat);
                    $this->fs->delete($fallback);
                    $this->fs->write($fallback, $this->createThumbnailPath($res));
                }
            }
            // tmb garbage collection
            if ($res && $this->options['tmbGcMaxlifeHour'] && $this->options['tmbGcPercentage'] > 0) {
                $rand = mt_rand(1, 10000);

                if ($rand <= $this->options['tmbGcPercentage'] * 100) {
                    register_shutdown_function(['elFinder', 'GlobGC'], $this->tmbPath . DIRECTORY_SEPARATOR . '*.png', $this->options['tmbGcMaxlifeHour'] * 3600);
                }
            }

            return $res;
        }

        return false;
    }

    /**
     * @param string $thumbnailPath
     * @param mixed[] $stat
     * @return false|string
     */
    protected function gettmb($thumbnailPath, $stat)
    {
        if ($this->tmbURL && $this->tmbPath) {
            // file itself thumnbnail
            if (strpos($thumbnailPath, $this->tmbPath) === 0) {
                return basename($thumbnailPath);
            }

            $stat['hash'] = $stat['hash'] ?? '';
            $name = $this->tmbname($stat);

            if ($this->fs->has($this->createThumbnailPath($name))) {
                return $name;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return string
     */
    public function createThumbnailPath($name)
    {
        return $this->tmbPath . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @param string $thumbnailPath
     * @param mixed[] $stat
     * @return false|string
     */
    protected function createTmb($thumbnailPath, $stat)
    {
        @mkdir($this->tmbPath, 0777, true);

        $name = parent::createTmb($thumbnailPath, $stat);

        if ($name !== false) {
            $fp = fopen($this->createThumbnailPath($name), 'rb');

            if ($fp === false) {
                return false;
            }

            $this->_save($fp, $this->tmbPath, $name, $stat);
            unlink($this->createThumbnailPath($name));
        }

        return $name;
    }

    /**
     * @param mixed[] $stat
     */
    protected function rmTmb($stat)
    {
        $path = $this->tmbPath . DIRECTORY_SEPARATOR . $this->tmbname($stat);

        if ($this->tmbURL) {
            $thumbnailName = $this->gettmb($path, $stat);
            $stat['tmb'] = $thumbnailName ?: 1;
        }

        if (!$this->tmbPathWritable) {
            return;
        }

        if ($stat['mime'] === 'directory') {
            foreach ($this->scandirCE($this->decode($stat['hash'])) as $p) {
                elFinder::extendTimeLimit(30);
                $name = $this->basenameCE($p);

                if ($name !== '.' && $name !== '..') {
                    $this->rmTmb($this->stat($p));
                }
            }
        } elseif (!empty($stat['tmb']) && (string)$stat['tmb'] !== '1') {
            $thumbnailPath = $this->createThumbnailPath(rawurldecode($stat['tmb']));

            if ($this->fs->has($thumbnailPath)) {
                $this->_unlink($thumbnailPath);
            }

            clearstatcache();
        }
    }

    /**
     * @param string $path
     * @param string $hash
     * @return false|mixed[]
     */
    protected function _stat($path, $hash = '')
    {
        $stat = parent::_stat($path);

        if ($hash !== '') {
            $stat['hash'] = $hash;
        }

        if (count($stat) > 0 && $this->tmbURL && !isset($stat['tmb']) && $this->canCreateTmb($path, $stat)) {
            $thumbnailName = $this->gettmb($path, $stat);
            $stat['tmb'] = $thumbnailName ?: 1;
        }

        return $stat;
    }
}

class_alias(VolumeDriver::class, 'elFinderVolumeFlysystem');
