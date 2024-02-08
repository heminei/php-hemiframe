<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class FFmpeg
{
    private $binaryPath = 'ffmpeg';
    private $inputFile;
    private $outputFile;
    private $command;
    private $options = [];

    public function __construct()
    {
        $this->setOutputOverwrite(true);
    }

    public function setBinaryPath(string $path): self
    {
        $this->binaryPath = $path;

        return $this;
    }

    public function getBinaryPath(): string
    {
        return $this->binaryPath;
    }

    public function execute(string $append = '')
    {
        if (empty($this->getCommand())) {
            throw new \Exception('Command is empty!');
        }

        return shell_exec($this->getCommand().$append);
    }

    public function setOutputOverwrite(bool $bool): self
    {
        if (true === $bool) {
            $this->setOption('-y');
        } else {
            $this->removeOption('-y');
        }

        return $this;
    }

    public function setDisableVideoRecording(bool $bool): self
    {
        if (true === $bool) {
            $this->setOption('-vn');
        } else {
            $this->removeOption('-vn');
        }

        return $this;
    }

    public function setOutputScale(string $value = '640:-1'): self
    {
        return $this->setOption('-vf', '"scale='.$value.'"');
    }

    public function setOutputFramePerSeconds(int $value = 25): self
    {
        return $this->setOption('-r', $value);
    }

    public function setOutputSize(string $value = '320x240'): self
    {
        return $this->setOption('-s', $value);
    }

    public function setOutputFormat(string $value = 'mp4'): self
    {
        $value = \strtolower($value);

        return $this->setOption('-f', $value);
    }

    public function setOutputVideoCodec(string $value = 'libx264'): self
    {
        return $this->setOption('-codec:v', $value);
    }

    public function setOutputVideoBitrate(string $value = '1000k'): self
    {
        return $this->setOption('-b:v', $value);
    }

    public function setOutputAudioBitrate(string $value = '192k'): self
    {
        return $this->setOption('-b:a', $value);
    }

    public function setOutputAudioCodec(string $value = 'aac'): self
    {
        return $this->setOption('-codec:a', $value);
    }

    public function setOutputThumb(int $second = 60): self
    {
        $this->setOption('-frames:v', 1);
        $this->setOption('-an');

        return $this->setOption('-ss', $second);
    }

    public function setOutputMetadata(string $title): self
    {
        return $this->setOption('-metadata', "title='$title'");
    }

    public function setInputFile(string $inputFile): self
    {
        $this->inputFile = $inputFile;

        return $this;
    }

    public function setOutputFile(string $outputFile): self
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function setThreads(int $number): self
    {
        return $this->setOption('-threads', $number);
    }

    public function setProgress(string $path): self
    {
        return $this->setOption('-progress', $path);
    }

    public function setStrict(string $type = 'experimental'): self
    {
        return $this->setOption('-strict', $type);
    }

    /**
     * @throws \Exception
     */
    public function setOption(string $key, $value = null): self
    {
        if (empty($key)) {
            throw new \Exception('Key is empty!');
        }
        $this->options[$key] = $value;

        return $this;
    }

    public function getInputFileDuration(): int
    {
        $durationString = exec($this->getBinaryPath()." -i '".$this->getInputFile()."' 2<&1 | grep Duration: | cut -f2- -d: | cut -f1 -d, | tr -d ' '");
        $timeArray = explode(':', substr($durationString, 0, -3));
        $seconds = intval($timeArray[0]) * 60 * 60 + intval($timeArray[1]) * 60 + intval($timeArray[2]);

        return $seconds;
    }

    public function getInputFileDimension(): array
    {
        $infoString = shell_exec($this->getBinaryPath()." -i '".$this->getInputFile()."'".' 2<&1');
        $regexSizes = '/Video: (.*?), (.*?), (?<width>[0-9]{1,4})x(?<height>[0-9]{1,4})/';
        $regs = [];

        if (preg_match($regexSizes, $infoString, $regs)) {
            $width = isset($regs['width']) ? $regs['width'] : null;
            $height = isset($regs['height']) ? $regs['height'] : null;
        } else {
            $width = null;
            $height = null;
        }

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    public function getInputFileFramePerSeconds()
    {
        $shellString = shell_exec($this->getBinaryPath()." -i '".$this->getInputFile()."'".' 2<&1');
        $regs = [];

        if (preg_match('/, (?<fps>\d+) fps(,|$)/m', $shellString, $regs)) {
            return $regs['fps'];
        }

        return null;
    }

    public function getInputFileAudioBitrate()
    {
        $shellString = shell_exec($this->getBinaryPath()." -i '".$this->getInputFile()."'".' 2<&1');
        $regs = [];

        if (preg_match('/Audio: (.*?)(?<bitrate>\d+) kb\/s(.*?)/', $shellString, $regs)) {
            return isset($regs['bitrate']) ? (int) $regs['bitrate'] : null;
        }

        return null;
    }

    public function getInputFileVideoBitrate()
    {
        $shellString = shell_exec($this->getBinaryPath()." -i '".$this->getInputFile()."'".' 2<&1');
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

    public function getCommand(): string
    {
        $command = $this->getBinaryPath()." -i '".$this->getInputFile()."' ";
        foreach ($this->getOptions() as $key => $value) {
            $command .= $key.' ';
            if (null !== $value) {
                $command .= $value.' ';
            }
        }
        if (null != $this->getOutputFile()) {
            $outputFile = "'".$this->getOutputFile()."'";
        } else {
            $outputFile = null;
        }
        $this->command = $command." $outputFile 2<&1";

        return $this->command;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function removeOption(string $key): self
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key is empty!');
        }
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }

        return $this;
    }
}
