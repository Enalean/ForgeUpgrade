<?php
class LoggerAppenderConsoleColor extends LoggerAppenderConsole {

    const BLACK   = "\033[30m";
    const RED     = "\033[31m";
    const GREEN   = "\033[32m";
    const BLUE    = "\033[34m";
    const YELLOW  = "\033[35m";
    const BG_RED  = "\033[41m";
    const NOCOLOR = "\033[0m";

    /**
     * Format message aaccording to given level 
     *
     * @param String $level
     * @param String $message
     * 
     * @return string
     */
    public function chooseColor($level, $message) {
        $color = null;
        switch ($level) {
            case 'INFO':
                $color = self::GREEN;
                break;
            case 'WARN':
                $color = self::YELLOW;
                break;
            case 'ERROR':
                $color = self::RED;
                break;
            case 'FATAL':
                $color = self::BLACK.self::BG_RED;
                break;
        }
        if ($color) {
            $message = $color.$message.self::NOCOLOR;
        }
        return $message;
    }

    /**
     * Display coloried messages on console
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    public function colorize(LoggerLoggingEvent $event) {
        return $this->chooseColor($event->getLevel()->toString(), $this->layout->format($event));
     }


    public function append(LoggerLoggingEvent $event) {
        if (is_resource($this->fp) && $this->layout !== null) {
            return fwrite ($this->fp, $this->colorize($event));
        }

    }
}
?>