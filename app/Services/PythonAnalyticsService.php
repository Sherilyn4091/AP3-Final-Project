<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/*
|--------------------------------------------------------------------------
| PythonAnalyticsService
|--------------------------------------------------------------------------
|
| Runs Python analytics scripts from Laravel and returns decoded JSON.
| Responsibilities are intentionally small:
| - locate a Python executable
| - run a selected Python file
| - pass JSON input through STDIN
| - decode the JSON response safely
|
| This avoids mixing Python process logic directly into controllers.
|
*/

class PythonAnalyticsService
{
    private const DEFAULT_TIMEOUT_SECONDS = 45;

    /**
     * Run the Student Risk Analytics engine.
     */
    public function runStudentRiskAnalysis(array $students, string $mode = 'full'): array
    {
        $script = match ($mode) {
            'dashboard' => 'dashboard_analytics.py',
            'reports' => 'reports_analytics.py',
            default => 'student_risk_analytics.py',
        };

        return $this->runScript($script, [
            'students' => array_values($students),
        ]);
    }

    /**
     * Run a Python analytics script and decode the JSON output.
     */
    private function runScript(string $scriptName, array $payload): array
    {
        $scriptPath = base_path('python_analytics' . DIRECTORY_SEPARATOR . $scriptName);

        if (!is_file($scriptPath)) {
            throw new RuntimeException("Python analytics script not found: {$scriptPath}");
        }

        $input = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $lastError = null;

        foreach ($this->pythonCommands() as $command) {
            try {
                $process = new Process(
                    array_merge($command, [$scriptPath]),
                    base_path(),
                    null,
                    $input,
                    self::DEFAULT_TIMEOUT_SECONDS
                );

                $process->run();

                if (!$process->isSuccessful()) {
                    $lastError = trim($process->getErrorOutput() ?: $process->getOutput());
                    continue;
                }

                return $this->decodePythonOutput($process->getOutput());
            } catch (\Throwable $exception) {
                $lastError = $exception->getMessage();
            }
        }

        Log::error('Python analytics failed.', [
            'script' => $scriptName,
            'error' => $lastError,
        ]);

        throw new RuntimeException('Python analytics failed. ' . ($lastError ?: 'No Python executable was found.'));
    }

    /**
     * Decode Python JSON output and validate the result shape.
     */
    private function decodePythonOutput(string $output): array
    {
        $output = trim($output);

        if ($output === '') {
            throw new RuntimeException('Python analytics returned empty output.');
        }

        $decoded = json_decode($output, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Python analytics returned invalid JSON: ' . $output);
        }

        if (($decoded['ok'] ?? false) !== true) {
            throw new RuntimeException($decoded['error'] ?? 'Python analytics returned an error.');
        }

        return $decoded;
    }

    /**
     * Candidate Python commands for Windows and local development.
     */
    private function pythonCommands(): array
    {
        $commands = [];

        if ($custom = env('PYTHON_BINARY')) {
            $commands[] = [$custom];
        }

        $commands[] = ['python'];
        $commands[] = ['py', '-3'];
        $commands[] = ['python3'];

        return $commands;
    }
}
