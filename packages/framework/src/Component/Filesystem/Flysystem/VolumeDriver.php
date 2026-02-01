<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem\Flysystem;

use Barryvdh\elFinderFlysystemDriver\Driver;
use elFinder;
use Override;

class VolumeDriver extends Driver
{
    #[Override]
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
    #[Override]
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
    #[Override]
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
    #[Override]
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
    #[Override]
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
    #[Override]
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

      /**
     * Return content URL (for netmout volume driver)
     * If file.url == 1 requests from JavaScript client with XHR
     *
     * @param string $hash    file hash
     * @param array  $options options array
     *
     * @return boolean|string
     * @author Naoki Sawada
     */
    #[Override]
    public function getContentUrl($hash, $options = array())
    {
        if (($file = $this->file($hash)) === false) {
            return false;
        }
        if (!empty($options['onetime']) && $this->options['onetimeUrl']) {
            if (is_callable($this->options['onetimeUrl'])) {
                return call_user_func_array($this->options['onetimeUrl'], array($file, $options, $this));
            } else {
                $ret = false;
                if ($tmpdir = elFinder::getStaticVar('commonTempPath')) {
                    if ($source = $this->open($hash)) {
                        if ($_dat = tempnam($tmpdir, 'ELF')) {
                            $token = md5($_dat . session_id());
                            $dat = $tmpdir . DIRECTORY_SEPARATOR . 'ELF' . $token;
                            if (rename($_dat, $dat)) {
                                $info = stream_get_meta_data($source);
                                if (!empty($info['uri'])) {
                                    $tmp = $info['uri'];
                                } else {
                                    $tmp = tempnam($tmpdir, 'ELF');
                                    if ($dest = fopen($tmp, 'wb')) {
                                        if (!stream_copy_to_stream($source, $dest)) {
                                            $tmp = false;
                                        }
                                        fclose($dest);
                                    }
                                }
                                $this->close($source, $hash);
                                if ($tmp) {
                                    $info = array(
                                        'file' => base64_encode($tmp),
                                        'name' => $file['name'],
                                        'mime' => $file['mime'],
                                        'ts' => $file['ts']
                                    );
                                    if (file_put_contents($dat, json_encode($info))) {
                                        $conUrl = elFinder::getConnectorUrl();
                                        $ret = $conUrl . (strpos($conUrl, '?') !== false? '&' : '?') . 'cmd=file&onetime=1&target=' . $token;

                                    }
                                }
                                if (!$ret) {
                                    unlink($dat);
                                }
                            } else {
                                unlink($_dat);
                            }
                        }
                    }
                }
                return $ret;
            }
        }
        if (empty($file['url']) && $this->URL) {
            /* start fix for the double slash issue on the newly uploaded file - https://github.com/Studio-42/elFinder/issues/3725 */
            $decoded = $this->decode($hash);

            // safely strip root prefix if present
            if (str_starts_with($decoded, $this->root)) {
                $path = substr($decoded, strlen($this->root));
            } else {
                $path = $decoded;
            }

            // remove leading separator to avoid double slashes
            $path = ltrim($path, '/');
            $path = str_replace($this->separator, '/', $path);

             if ($this->encoding) {
                 $path = $this->convEncIn($path, true);
             }

             $path = str_replace('%2F', '/', rawurlencode($path));

            return rtrim($this->URL, '/') . '/' . $path;
            /* end fix */
        } else {
            $ret = false;
            if (!empty($file['url']) && $file['url'] != 1) {
                return $file['url'];
            } else if (!empty($options['temporary']) && ($tempInfo = $this->getTempLinkInfo('temp_' . md5($hash . session_id())))) {
                if (is_readable($tempInfo['path'])) {
                    touch($tempInfo['path']);
                    $ret = $tempInfo['url'] . '?' . rawurlencode($file['name']);
                } else if ($source = $this->open($hash)) {
                    if ($dest = fopen($tempInfo['path'], 'wb')) {
                        if (stream_copy_to_stream($source, $dest)) {
                            $ret = $tempInfo['url'] . '?' . rawurlencode($file['name']);
                        }
                        fclose($dest);
                    }
                    $this->close($source, $hash);
                }
            }
            return $ret;
        }
    }
}

class_alias(VolumeDriver::class, 'elFinderVolumeFlysystem');
