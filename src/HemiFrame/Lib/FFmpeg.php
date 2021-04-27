<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class FFmpeg
{

    private $inputFile = null;
    private $outputFile = null;
    private $command = null;
    private $options = array();
    private $as = array(
        'b' => 'bitrate',
        'r' => 'frameRate',
        'fs' => 'fileSizeLimit',
        'f' => 'forceFormat',
        'force' => 'forceFormat',
        'i' => 'input',
        's' => 'size',
        'ar' => 'audioSamplingFrequency',
        'ab' => 'audioBitrate',
        'acodec' => 'audioCodec',
        'vcodec' => 'videoCodec',
        'std' => 'redirectOutput',
        'unset' => '_unset',
        'number' => 'videoFrames',
        'vframes' => 'videoFrames',
        'y' => 'overwrite',
        'log' => 'loglevel',
    );

    public function __construct()
    {
        $this->setOutputOverwrite(true);
    }

    public function execute(string $append = "")
    {
        if (empty($this->getCommand())) {
            throw new \Exception("Command is empty!");
        }
        return shell_exec($this->getCommand() . $append);
    }

    /**
     *
     * @param bool $bool
     * @return self
     */
    public function setOutputOverwrite(bool $bool): self
    {
        if ($bool === true) {
            $this->setOption("-y");
        } else {
            $this->removeOption("-y");
        }
        return $this;
    }

    /**
     *     @return self
     */
    public function setDisableVideoRecording(bool $bool): self
    {
        if ($bool === true) {
            $this->setOption("-vn");
        } else {
            $this->removeOption("-vn");
        }
        return $this;
    }

    /**
     *     @return self
     */
    public function setOutputScale(string $value = "640:-1"): self
    {
        return $this->setOption("-vf", "\"scale=" . $value . "\"");
    }

    /**
     *     @return self
     */
    public function setOutputFramePerSeconds(int $value = 25): self
    {
        return $this->setOption("-r", $value);
    }

    /**
     *     @return self
     */
    public function setOutputSize(string $value = "320x240"): self
    {
        return $this->setOption("-s", $value);
    }

    /**
     *     @return self
     */
    public function setOutputFormat(string $value = "mp4"): self
    {
        $value = \strtolower($value);
        return $this->setOption("-f", $value);
    }

    /**
     *     @return self
     */
    public function setOutputVideoCodec(string $value = "libx264"): self
    {
        return $this->setOption("-codec:v", $value);
    }

    /**
     *     @return self
     */
    public function setOutputVideoBitrate(string $value = "1000k"): self
    {
        return $this->setOption("-b:v", $value);
    }

    /**
     *     @return self
     */
    public function setOutputAudioBitrate(string $value = "192k"): self
    {
        return $this->setOption("-b:a", $value);
    }

    /**
     *     @return self
     */
    public function setOutputAudioCodec(string $value = "aac"): self
    {
        return $this->setOption("-codec:a", $value);
    }

    /**
     *     @return self
     */
    public function setOutputThumb(int $second = 60): self
    {
        $this->setOption("-frames:v", 1);
        $this->setOption("-an");
        return $this->setOption("-ss", $second);
    }

    /**
     *     @return self
     */
    public function setOutputMetadata(string $title): self
    {
        return $this->setOption("-metadata", "title='$title'");
    }

    /**
     *     @return self
     */
    public function setInputFile(string $inputFile): self
    {
        $this->inputFile = $inputFile;

        return $this;
    }

    /**
     *     @return self
     */
    public function setOutputFile(string $outputFile): self
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    /**
     *
     * @param string $command
     * @return self
     */
    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     *
     * @param int $number
     * @return self
     */
    public function setThreads(int $number): self
    {
        return $this->setOption("-threads", $number);
    }

    /**
     *
     * @param string $path
     * @return self
     */
    public function setProgress(string $path): self
    {
        return $this->setOption("-progress", $path);
    }

    /**
     *
     * @param string $type
     * @return self
     */
    public function setStrict(string $type = "experimental"): self
    {
        return $this->setOption("-strict", $type);
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return self
     * @throws \Exception
     */
    public function setOption(string $key, $value = null): self
    {
        if (empty($key)) {
            throw new \Exception("Key is empty!");
        }
        $this->options[$key] = $value;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getInputFileDuration(): int
    {
        $durationString = exec("ffmpeg -i '" . $this->getInputFile() . "'" . " 2<&1 | grep Duration: | cut -f2- -d: | cut -f1 -d, | tr -d ' '");
        $timeArray = explode(":", substr($durationString, 0, -3));
        $seconds = intval($timeArray[0]) * 60 * 60 + intval($timeArray[1]) * 60 + intval($timeArray[2]);
        return $seconds;
    }

    /**
     *
     * @return array
     */
    public function getInputFileDimension(): array
    {
        $infoString = shell_exec("ffmpeg -i '" . $this->getInputFile() . "'" . " 2<&1");
        $regexSizes = "/Video: (.*?), (.*?), (?<width>[0-9]{1,4})x(?<height>[0-9]{1,4})/";
        $regs = [];

        if (preg_match($regexSizes, $infoString, $regs)) {
            $width = isset($regs["width"]) ? $regs["width"] : null;
            $height = isset($regs["height"]) ? $regs["height"] : null;
        } else {
            $width = null;
            $height = null;
        }
        return [
            "width" => $width,
            "height" => $height,
        ];
    }

    /**
     *
     * @return mixed
     */
    public function getInputFileFramePerSeconds()
    {
        $shellString = shell_exec("ffmpeg -i '" . $this->getInputFile() . "'" . " 2<&1");
        $regs = [];

        if (preg_match('/, (?<fps>\d+) fps(,|$)/m', $shellString, $regs)) {
            return $regs['fps'];
        }
        return null;
    }

    /**
     *
     * @return mixed
     */
    public function getInputFileAudioBitrate()
    {
        $shellString = shell_exec("ffmpeg -i '" . $this->getInputFile() . "'" . " 2<&1");
        $regs = [];

        if (preg_match('/Audio: (.*?)(?<bitrate>\d+) kb\/s(.*?)/', $shellString, $regs)) {
            return isset($regs['bitrate']) ? (int) $regs['bitrate'] : null;
        }
        return null;
    }

    /**
     *
     * @return mixed
     */
    public function getInputFileVideoBitrate()
    {
        $shellString = shell_exec("ffmpeg -i '" . $this->getInputFile() . "'" . " 2<&1");
        $regs = [];

        if (preg_match('/Video: (.*?)(?<bitrate>\d+) kb\/s(.*?)/', $shellString, $regs)) {
            return isset($regs['bitrate']) ? (int) $regs['bitrate'] : null;
        }
        return null;
    }

    public function getInputFile(): string
    {
        return $this->inputFile;
    }

    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /**
     *
     * @return string
     */
    public function getCommand(): string
    {
        $command = "ffmpeg -i '" . $this->getInputFile() . "' ";
        foreach ($this->getOptions() as $key => $value) {
            $command .= $key . " ";
            if ($value !== null) {
                $command .= $value . " ";
            }
        }
        if ($this->getOutputFile() != null) {
            $outputFile = "'" . $this->getOutputFile() . "'";
        } else {
            $outputFile = null;
        }
        $this->command = $command . " $outputFile 2<&1";
        return $this->command;
    }

    /**
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     *
     * @param string $key
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function removeOption(string $key): self
    {
        if (empty($key)) {
            throw new \InvalidArgumentException("Key is empty!");
        }
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }

        return $this;
    }

}
