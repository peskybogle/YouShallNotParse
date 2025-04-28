<?php

namespace YouShallNotParse;

class ProgressSpinner {
    private $spinnerChars = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
    private $currentStep = 0;
    private $lastUpdate = 0;
    private $message = '';

    public function start(string $message): void {
        $this->message = $message;
        $this->currentStep = 0;
        $this->lastUpdate = 0;
        $this->update();
    }

    public function stop(string $completionMessage = ''): void {
        echo "\r" . str_pad($completionMessage ?: ($this->message . ' Complete.'), 50) . PHP_EOL;
    }

    public function update(): void {
        $now = microtime(true);
        if ($now - $this->lastUpdate < 0.1) {
            return;
        }

        $this->lastUpdate = $now;
        $this->currentStep = ($this->currentStep + 1) % count($this->spinnerChars);
        echo "\r" . $this->spinnerChars[$this->currentStep] . ' ' . $this->message;
    }
} 